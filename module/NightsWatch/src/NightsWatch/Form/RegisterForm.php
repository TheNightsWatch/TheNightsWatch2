<?php

namespace NightsWatch\Form;

use Zend\Form\Form;

class RegisterForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('register');
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => 'username',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'register-username',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Minecraft Username',
                    'required' => true,
                ],
            ]
        );

        $this->add(
            [
                'name' => 'email',
                'attributes' => [
                    'type' => 'email',
                    'id' => 'register-email',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Email Address',
                    'required' => true,
                ],
            ]
        );

        $this->add(
            [
                'name' => 'password',
                'attributes' => [
                    'type' => 'password',
                    'id' => 'register-password',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Password',
                    'required' => true,
                ],
            ]
        );

        $this->add(
            [
                'name' => 'password2',
                'attributes' => [
                    'type' => 'password',
                    'id' => 'register-password2',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Password',
                    'required' => true,
                ],
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Register',
                    'id' => 'register-submit',
                ],
            ]
        );
    }
}
