<?php

namespace NightsWatch\Controller;

use Doctrine\Common\Collections\Criteria;
use Navarr\Minecraft\Profile;
use Navarr\MinecraftAPI\MinecraftAPI;
use NightsWatch\Authentication\Adapter as AuthAdapter;
use NightsWatch\Authentication\ForceAdapter;
use NightsWatch\Authentication\MinecraftIdAdapter;
use NightsWatch\Entity\User;
use NightsWatch\ShotbowProvider;
use Zend\Authentication\Result as AuthResult;
use NightsWatch\Mvc\Controller\ActionController;
use NightsWatch\Form\LoginForm;
use Zend\Authentication\Storage\Session;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

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
                    'header' => "Accept: */*\r\nUser-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36"
                ]
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

        $provider = new ShotbowProvider(
            [
                'clientId'     => $shotbow['clientId'],
                'clientSecret' => $shotbow['clientSecret'],
                'redirectUri'  => 'https://minez-nightswatch.com/site/shotbowlogin',
                'scopes'       => ['basic', 'email', 'ban', 'rank'],
            ]
        );

        $session = new SessionContainer('NightsWatch\Login\Shotbow');

        if (!isset($_GET['code'])) {
            $authUrl        = $provider->getAuthorizationUrl();
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

        $name        = $userDetails->username;
        $minecraftId = $userDetails->minecraftId;
        $email       = $userDetails->email;

        if (empty($name) || empty($minecraftId) || empty($email)) {
            throw new \Exception('Bad Data from Shotbow');
        }

        // Attempt Login
        $authNamespace = new Container(Session::NAMESPACE_DEFAULT);
        $authNamespace->getManager()->rememberMe(60 * 60 * 24 * 30);
        
        $authAdapter = new MinecraftIdAdapter($minecraftId, $name, $this->getEntityManager());
        $result      = $this->getAuthenticationService()->authenticate($authAdapter);

        $errors = [];

        switch ($result->getCode()) {
            case AuthResult::SUCCESS:
                $this->redirect()->toRoute('home', ['controller' => 'chat']);
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
                $errors[] = "Your account has been banned";
                break;
            default:
                $errors[] = "Invalid Password...?";
                break;
        }
    }

    public function loginAction()
    {
        if ($this->disallowMember()) {
            return false;
        }
        $this->updateLayoutWithIdentity();
        $errors = [];
        $form   = new LoginForm();
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                if ($form->get('rememberme')->getValue()) {
                    $authNamespace = new Container(Session::NAMESPACE_DEFAULT);
                    $authNamespace->getManager()->rememberMe(60 * 60 * 24 * 30);
                }
                $authAdapter = new AuthAdapter(
                    $form->get('username')->getValue(),
                    $form->get('password')->getValue(),
                    $this->getEntityManager()
                );

                $result = $this->getAuthenticationService()->authenticate($authAdapter);

                switch ($result->getCode()) {
                    case AuthResult::SUCCESS:
                        $this->updateUsername();
                        $this->redirect()->toRoute('home', ['controller' => 'chat']);
                        return false;
                    case AuthResult::FAILURE_IDENTITY_NOT_FOUND:
                        $errors[] = "Identity not Registered";
                        break;
                    case -5:
                        $errors[] = "Your account has been banned";
                        break;
                    default:
                        $errors[] = "Invalid Password";
                        break;
                }
            }
        }
        return new ViewModel(
            [
                'form'   => $form,
                'errors' => $errors,
            ]
        );
    }
}
