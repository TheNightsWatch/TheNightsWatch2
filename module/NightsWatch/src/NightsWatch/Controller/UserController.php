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
        $origDeserter = $user->deserter;
        $origEmailNotifications = $user->emailNotifications;
        $origAccord = $user->accordMember;

        if (!$user) {
            $this->getResponse()->setStatusCode(404);
            return false;
        }

        $form = new AssignRankForm($user);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $user->rank = $form->get('rank')->getValue();
                $user->deserter = $form->get('deserter')->getValue();
                $user->emailNotifications = $form->get('email')->getValue();
                $user->accordMember = $form->get('accord')->getValue();

                // do discord message first
                $discordConfig = $this->getServiceLocator()->get('config')['NightsWatch']['discord'];
                $webhookConfig = $discordConfig['webhooks'];

                $webhook = $webhookConfig[User::RANK_LIEUTENANT];
                $discordMessenger = new DiscordMessage($webhook);

                $escapedPerformer = str_replace('_', '\_', $performer->username);
                $performerText = $performer->discordId ? "<@{$performer->discordId}> ({$escapedPerformer})" : $performer->username;
                $escapedName = str_replace('_', '\_', $user->username);
                $userText = $user->discordId ? "<@{$user->discordId}> ({$escapedName})" : $user->username;

                if ($user->rank != $origRank && isset($webhookConfig[User::RANK_LIEUTENANT])) {
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
                if ($user->deserter != $origDeserter && isset($webhookConfig[User::RANK_LIEUTENANT])) {
                    $message = $user->deserter ? "{$performerText} has marked {$userText} as a deserter" : "{$performerText} has removed {$userText} from the list of deserters";

                    $discordMessenger->perform(
                        [
                            'username' => 'The Night\'s Watch',
                            'content' => $message,
                        ],
                        true
                    );
                }
                if ($user->emailNotifications != $origEmailNotifications) {
                    $message = "{$performerText} has set {$userText} email preferences to: {$user->emailNotifications}";

                    $discordMessenger->perform(
                        [
                            'username' => 'The Night\'s Watch',
                            'content' => $message,
                        ],
                        false
                    );
                }
                if ($user->accordMember != $origAccord) {
                    $message = $user->accordMember ? "{$performerText} has associated {$userText} with an accord clan." : "{$performerText} has disassociated {$userText} from accord clans.";

                    $discordMessenger->perform(
                        [
                            'username' => 'The Night\'s Watch',
                            'content' => $message,
                        ]
                    );
                }

                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();

                if ($user->discordId) {
                    $this->updateDiscordRank($user);
                }

                $this->redirect()->toRoute('user', ['controller' => 'user', 'action' => 'view', 'username' => $user->username]);
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
