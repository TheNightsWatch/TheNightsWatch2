<?php

namespace NightsWatch\Controller;

use Doctrine\Common\Collections\Criteria;
use NightsWatch\Authentication\Adapter as AuthAdapter;
use Zend\Authentication\Result as AuthResult;
use NightsWatch\Mvc\Controller\ActionController;
use NightsWatch\Form\LoginForm;
use Zend\Authentication\Storage\Session;
use Zend\Session\Container;
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

    public function mcstatusAction()
    {
        header('Access-Control-Allow-Origin: http://shotbow.net');
        header('Content-Type: application/json');
        echo file_get_contents('http://xpaw.ru/mcstatus/status.json');
        return $this->response;
    }

    public function loginAction()
    {
        if ($this->disallowMember()) {
            return false;
        }
        $this->updateLayoutWithIdentity();
        $errors = [];
        $form = new LoginForm();
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
                'form' => $form,
                'errors' => $errors,
            ]
        );
    }
}
