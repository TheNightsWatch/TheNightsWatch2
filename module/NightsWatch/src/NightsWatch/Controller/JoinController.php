<?php

namespace NightsWatch\Controller;

use Doctrine\ORM\EntityManager;
use Navarr\MinecraftAPI\Exception\BadLoginException;
use Navarr\MinecraftAPI\Exception\BasicException;
use Navarr\MinecraftAPI\Exception\MigrationException;
use Navarr\MinecraftAPI\MinecraftAPI;
use NightsWatch\Entity\User;
use NightsWatch\Form\RegisterForm;
use NightsWatch\Form\VerifyForm;
use NightsWatch\Mvc\Controller\ActionController;
use Zend\Authentication\Storage\Session;
use Zend\Crypt\Password\Bcrypt;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class JoinController extends ActionController
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var Session */
    protected $session;

    public function __construct()
    {
        $this->session = new Container('NightsWatch\register');
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        if (is_null($this->entityManager)) {
            $this->entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->entityManager;
    }

    public function indexAction()
    {
        $form = new RegisterForm();

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $bcrypt = new Bcrypt();
                $this->session->email = $form->get('email')->getValue();
                $this->session->username = $form->get('username')->getValue();
                $this->session->password = $bcrypt->create($form->get('password')->getValue());
                $this->redirect()->toRoute('home', ['controller' => 'join', 'action' => 'verify']);
            }
        }
        return new ViewModel(
            [
                'form' => $form,
            ]
        );
    }

    public function verifyAction()
    {
        $form = new VerifyForm();
        $errors = [];
        if (!isset($this->session->username)) {
            $this->redirect()->toRoute('home', ['controller' => 'join', 'action' => 'index']);
        } elseif ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                try {
                    $minecraft = new MinecraftAPI(
                        $form->get('username')->getValue(),
                        $form->get('password')->getValue()
                    );

                    if (strtolower($minecraft->username) != strtolower($this->session->username)) {
                        $errors[] = "Account Valid, but does not match provided username.";
                    } else {
                        $user = new User();
                        $user->username = $minecraft->username;
                        $user->password = $this->session->password;
                        $user->email = $this->session->email;
                        $user->minecraftId = $minecraft->minecraftID;
                        $this->getEntityManager()->persist($user);
                        $this->getEntityManager()->flush();
                    }
                } catch (\RuntimeException $e) {
                    $errors[] = "Problem querying the API";
                } catch (BadLoginException $e) {
                    $errors[] = "Invalid username or Password";
                } catch (MigrationException $e) {
                    $errors[] = "Your Minecraft account has been migrated to a Mojang account.  Please enter your Mojang email and try again";
                } catch (BasicException $e) {
                    $errors[] = "This is not a premium Minecraft Account";
                }
            }
        }
        return new ViewModel(
            [
                'errors' => $errors,
                'form' => $form,
            ]
        );
    }
}
