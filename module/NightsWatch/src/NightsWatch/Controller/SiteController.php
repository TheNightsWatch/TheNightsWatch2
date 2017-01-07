<?php

namespace NightsWatch\Controller;

use Navarr\Minecraft\Profile;
use NightsWatch\Authentication\Adapter as AuthAdapter;
use NightsWatch\Authentication\ForceAdapter;
use NightsWatch\Authentication\MinecraftIdAdapter;
use NightsWatch\DiscordProvider;
use NightsWatch\Entity\User;
use NightsWatch\Form\LoginForm;
use NightsWatch\Mvc\Controller\ActionController;
use NightsWatch\Routine\DiscordRemove;
use NightsWatch\Routine\DiscordUpdateNameAndRoles;
use NightsWatch\Routine\DiscordWire;
use NightsWatch\ShotbowProvider;
use Zend\Authentication\Result as AuthResult;
use Zend\Authentication\Storage\Session;
use Zend\Session\Container;
use Zend\Session\Container as SessionContainer;
use Zend\View\Model\ViewModel;

class SiteController extends ActionController
{
    public function indexAction()
    {
        $this->updateLayoutWithIdentity();

        return;
    }

    public function logoutAction()
    {
        if ($this->disallowGuest()) {
            return false;
        }

        $this->getAuthenticationService()->clearIdentity();
        $this->redirect()->toRoute('home');

        return false;
    }

    public function mumbleAction()
    {
        if ($this->disallowGuest()) {
            return false;
        }
        $this->updateLayoutWithIdentity();

        $mumble = $this->getServiceLocator()->get('config')['NightsWatch']['mumble'];

        return new ViewModel(['settings' => $mumble, 'user' => $this->getIdentityEntity()]);
    }

    public function connectdiscordAction()
    {
        if ($this->disallowGuest()) {
            return false;
        }

        $discord = $this->getServiceLocator()->get('config')['NightsWatch']['discord'];

        $domain = $this->getRequest()->getUri()->getHost();
        $scheme = $this->getRequest()->getUri()->getScheme();
        $provider = new DiscordProvider(
            [
                'clientId'     => $discord['clientId'],
                'clientSecret' => $discord['clientSecret'],
                'redirectUri'  => "{$scheme}://{$domain}/site/connectdiscord",
                'scopes'       => ['identify', 'guilds.join'],
            ]
        );

        $session = new SessionContainer('NightsWatch\Connect\Discord');

        if (!isset($_GET['code']) && empty($_GET['error'])) {
            $authUrl = $provider->getAuthorizationUrl();
            $session->state = $provider->state;

            return $this->redirect()->toUrl($authUrl);
        } elseif (!empty($_GET['error'])) {
            throw new \Exception($_GET['message']);
        } elseif (empty($_GET['state']) || $_GET['state'] !== $session->state) {
            $session->state = null;
            throw new \Exception('Invalid State - Possible MITM Attack');
        } else {
            $token = $provider->getAccessToken(
                'authorization_code',
                ['code' => $_GET['code'], 'grant_type' => 'authorization_code']
            );
            try {
                $provider->headers['Authorization'] = 'Bearer '.$token;
                $userDetails = $provider->getUserDetails($token);
            } catch (\Exception $e) {
                throw new \Exception('Failed to get User Details - '.$e->getMessage(), $e->getCode(), $e);
            }
        }

        $originalId = $this->identityEntity->discordId;
        $routine = new DiscordWire(
            $this->identityEntity,
            $userDetails->id,
            $provider,
            $token,
            $discord['accessToken']
        );
        $routine->after(function () use($originalId) {
            $this->getEntityManager()->persist($this->identityEntity);
            $this->getEntityManager()->flush($this->identityEntity);
            if ($originalId && $this->identityEntity->discordId != $originalId) {
                $this->removeFromDiscord($originalId);
            }
        });
        $routine->perform();

        $this->redirect()->toRoute('home', ['controller' => 'chat']);
        return false;
    }

    public function modAction()
    {
        if ($this->disallowRankLessThan(User::RANK_RECRUIT)) {
            return false;
        }
        $this->updateLayoutWithIdentity();

        $mod = $this->getServiceLocator()->get('config')['NightsWatch']['mod'];

        return new ViewModel(['mod' => $mod, 'user' => $this->getIdentityEntity()]);
    }

    public function mcstatusAction()
    {
        header('Access-Control-Allow-Origin: http://shotbow.net');
        header('Content-Type: application/json');
        $context = stream_context_create(
            [
                'http' => [
                    'method' => 'GET',
                    'header' => "Accept: */*\r\nUser-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36",
                ],
            ]
        );
        echo file_get_contents('http://xpaw.ru/mcstatus/status.json', false, $context);

        return $this->response;
    }

    private function updateUsername()
    {
        $user = $this->getIdentityEntity(true);
        try {
            $minecraftProfile = Profile::fromUuid($user->minecraftId);

            $user->username = $minecraftProfile->name;

            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush($user);
        } catch (\Exception $e) {
            // Just do nothing.
        }
    }

    public function shotbowloginAction()
    {
        if ($this->disallowMember()) {
            return false;
        }

        $shotbow = $this->getServiceLocator()->get('config')['NightsWatch']['shotbow'];

        $domain = $this->getRequest()->getUri()->getHost();
        $scheme = $this->getRequest()->getUri()->getScheme();
        $provider = new ShotbowProvider(
            [
                'clientId'     => $shotbow['clientId'],
                'clientSecret' => $shotbow['clientSecret'],
                'redirectUri'  => "{$scheme}://{$domain}/site/shotbowlogin",
                'scopes'       => ['basic', 'email', 'ban', 'rank'],
            ]
        );

        $session = new SessionContainer('NightsWatch\Login\Shotbow');

        if (!isset($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl();
            $session->state = $provider->state;

            return $this->redirect()->toUrl($authUrl);
        } elseif (!empty($_GET['error'])) {
            throw new \Exception($_GET['message']);
        } elseif (empty($_GET['state']) || $_GET['state'] !== $session->state) {
            $session->state = null;
            throw new \Exception('Invalid State');
        } else {
            $token = $provider->getAccessToken(
                'authorization_code',
                ['code' => $_GET['code'], 'grant_type' => 'authorization_code']
            );
            try {
                $userDetails = $provider->getUserDetails($token);
            } catch (\Exception $e) {
                throw new \Exception('Failed to get User Details');
            }
        }

        // At this point we have to have $userDetails - we've thrown Exceptions everywhere else.

        $name = $userDetails->username;
        $minecraftId = $userDetails->minecraftId;
        $email = $userDetails->email;

        if (empty($name) || empty($minecraftId) || empty($email)) {
            throw new \Exception('Bad Data from Shotbow');
        }

        // Attempt Login
        $authNamespace = new Container(Session::NAMESPACE_DEFAULT);
        $authNamespace->getManager()->rememberMe(60 * 60 * 24 * 30);

        $authAdapter = new MinecraftIdAdapter($minecraftId, $name, $this->getEntityManager());
        $result = $this->getAuthenticationService()->authenticate($authAdapter);

        $errors = [];

        $discord = $this->getServiceLocator()->get('config')['NightsWatch']['discord'];

        $domain = $this->getRequest()->getUri()->getHost();
        $scheme = $this->getRequest()->getUri()->getScheme();
        $provider = $this->createDiscordProvider();

        switch ($result->getCode()) {
            case AuthResult::SUCCESS:
                $this->redirect()->toRoute('home', ['controller' => 'chat']);
                $this->updateDiscord($result->getIdentity());

                return false;
            case AuthResult::FAILURE_IDENTITY_NOT_FOUND:
                $user = new User();
                $user->username = $name;
                $user->password = 'not-a-real-password';
                $user->email = $email;
                $user->minecraftId = $minecraftId;
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush($user);
                $this->getAuthenticationService()->authenticate(new ForceAdapter($user->id));
                $this->redirect()->toRoute('home', ['controller' => 'chat']);

                return false;
            case -5:
                $errors[] = 'Your account has been banned';
                break;
            default:
                $errors[] = 'Invalid Password...?';
                break;
        }
    }

    private function createDiscordProvider()
    {
        $domain = $this->getRequest()->getUri()->getHost();
        $scheme = $this->getRequest()->getUri()->getScheme();
        $discord = $this->getServiceLocator()->get('config')['NightsWatch']['discord'];
        return new DiscordProvider(
            [
                'clientId'     => $discord['clientId'],
                'clientSecret' => $discord['clientSecret'],
                'redirectUri'  => "{$scheme}://{$domain}/site/connectdiscord",
                'scopes'       => ['identify', 'guilds.join'],
            ]
        );
    }

    private function removeFromDiscord($discordId)
    {
        $discord = $this->getServiceLocator()->get('config')['NightsWatch']['discord'];
        $provider = $this->createDiscordProvider();

        $routine = new DiscordRemove(null, $discordId, $provider, $discord['accessToken']);
        $routine->perform();
    }

    private function updateDiscord($id)
    {
        $discord = $this->getServiceLocator()->get('config')['NightsWatch']['discord'];
        $provider = $this->createDiscordProvider();
        $user = $this->entityManager->getRepository('NightsWatch\Entity\User')->findOneBy(['id' => $id]);
        if (!$user) return;

        $routine = new DiscordUpdateNameAndRoles($user, $user->discordId, $provider, $discord['accessToken']);
        $routine->perform();
    }

    public function loginAction()
    {
        $this->redirect()->toRoute('shotbowlogin');
        return false;
    }
}
