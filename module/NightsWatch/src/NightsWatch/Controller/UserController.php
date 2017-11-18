<?php

namespace NightsWatch\Controller;

use NightsWatch\DiscordProvider;
use NightsWatch\Entity\User;
use NightsWatch\Form\AssignRankForm;
use NightsWatch\Mvc\Controller\ActionController;
use NightsWatch\Routine\DiscordMessage;
use NightsWatch\Routine\DiscordUpdateNameAndRoles;
use Zend\View\Model\ViewModel;

class UserController extends ActionController
{
    public function indexAction()
    {
        $this->updateLayoutWithIdentity();

        /** @var User $users */
        $users = $this->getEntityManager()
            ->getRepository(User::class)
            ->findAll();

        $ranks = [];

        foreach ($users as $user) {
            if (!isset($ranks[$user->rank])) {
                $ranks[$user->rank] = [];
            }
            $ranks[$user->rank][] = $user;
        }

        return new ViewModel(['usersByRank' => $ranks]);
    }

    public function viewAction($username = '')
    {
        $this->updateLayoutWithIdentity();

        $username = $this->params()->fromRoute('username');
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        if (!$user) {
            $this->getResponse()->setStatusCode(404);

            return;
        }

        return new ViewModel(['user' => $user, 'identity' => $this->getIdentityEntity()]);
    }

    public function assignRankAction($username = '')
    {
        $this->disallowRankLessThan(User::RANK_COMMANDER);
        $this->updateLayoutWithIdentity();

        $performer = $this->getIdentityEntity();

        $username = $this->params()->fromRoute('username');
        /** @var User $user */
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        $origRank = $user->rank;

        if (!$user) {
            $this->getResponse()->setStatusCode(404);
            return false;
        }

        $form = new AssignRankForm($user);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $user->rank = $form->get('rank')->getValue();

                // do discord message first
                $discordConfig = $this->getServiceLocator()->get('config')['NightsWatch']['discord'];
                $webhookConfig = $discordConfig['webhooks'];
                if ($user->rank != $origRank && isset($webhookConfig[User::RANK_LIEUTENANT])) {
                    $webhook = $webhookConfig[User::RANK_LIEUTENANT];
                    $discordMessenger = new DiscordMessage($webhook);

                    $performerText = $performer->discordId ? "<@{$performer->discordId}>" : $performer->username;
                    $userText = $user->discordId ? "<@{$user->discordId}>" : $user->username;
                    $prevRank = User::getRankName($origRank);
                    $newRank = User::getRankName($user->rank);

                    $discordMessenger->perform(
                        [
                            'username' => 'The Night\'s Watch',
                            'content' => "{$performerText} has changed {$userText}'s rank from {$prevRank} to {$newRank}"
                        ],
                        true
                    );
                }

                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();

                if ($user->discordId) {
                    $this->updateDiscordRank($user);
                }

                $this->redirect()->toRoute('user', ['controller' => 'user', 'action' => 'view', 'username' => $user->id]);
            }
        }

        return new ViewModel(['form' => $form]);
    }

    private function updateDiscordRank(User $user)
    {
        $discord = $this->getServiceLocator()->get('config')['NightsWatch']['discord'];
        $domain = $this->getRequest()->getUri()->getHost();
        $scheme = $this->getRequest()->getUri()->getScheme();

        $provider = new DiscordProvider(
            [
                'clientId' => $discord['clientId'],
                'clientSecret' => $discord['clientSecret'],
                'redirectUri' => "{$scheme}://{$domain}/site/connectdiscord",
                'scopes' => ['identify', 'guilds.join'],
            ]
        );

        $routine = new DiscordUpdateNameAndRoles($user, $user->discordId, $provider, $discord['accessToken']);
        $routine->perform();
    }
}
