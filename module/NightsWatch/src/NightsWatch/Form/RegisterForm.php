<?php

namespace NightsWatch\Form;

use Zend\Form\Element\Collection;
use Zend\Form\Form;

class RegisterForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('register');

        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => 'email',
                'attributes' => [
                    'type' => 'email',
                    'id' => 'register-email',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Email',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'username',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'register-username',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Username',
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'This must be exactly the same as your Minecraft character\'s username.',
                        ]
                    ]
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
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'Please create a secure password for your new account.',
                        ]
                    ],
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
                    'label',
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'Please verify your new password by typing it again.',
                        ],
                    ],
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
                    'class' => 'btn btn-primary',
                ],
                'options' => [
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'On the next page, you will be asked to verify your Minecraft account to finish registration.',
                        ],
                    ],
                ],
            ]
        );
    }
}
