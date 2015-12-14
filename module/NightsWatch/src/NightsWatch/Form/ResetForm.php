<?php

namespace NightsWatch\Form;

use Zend\Form\Form;

class ResetForm extends Form
{
    public function __construct()
    {
        parent::__construct('reset');

        $this->add(
            [
                'name'       => 'username',
                'attributes' => [
                    'type'     => 'text',
                    'id'       => 'mojang-login',
                    'required' => true,
                ],
                'options' => [
                    'label'     => 'Mojang Login',
                    'bootstrap' => [
                        'help' => [
                            'style'   => 'block',
                            'content' => 'This is the username you log into Minecraft with.  If you have migrated your '
                            .'account, it will be an email address.',
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'mojangPassword',
                'attributes' => [
                    'type'     => 'password',
                    'id'       => 'mojang-password',
                    'required' => true,
                ],
                'options' => [
                    'label'     => 'Mojang Password',
                    'bootstrap' => [
                        'help' => [
                            'style'   => 'block',
                            'content' => 'This password will be used once to verify your account.'
                            .' It will not be saved.',
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'password',
                'attributes' => [
                    'type'     => 'password',
                    'id'       => 'register-password',
                    'required' => true,
                ],
                'options' => [
                    'label'     => 'Password',
                    'bootstrap' => [
                        'help' => [
                            'style'   => 'block',
                            'content' => 'Please create a secure password for your account.',
                        ],
                    ],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 5,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'password2',
                'attributes' => [
                    'type'     => 'password',
                    'id'       => 'register-password2',
                    'required' => true,
                ],
                'options' => [
                    'label',
                    'bootstrap' => [
                        'help' => [
                            'style'   => 'block',
                            'content' => 'Please verify your new password by typing it again.',
                        ],
                    ],
                ],
                'validators' => [
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token' => 'password',
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'submit',
                'attributes' => [
                    'type'  => 'submit',
                    'value' => 'Reset Password',
                    'id'    => 'reset-submit',
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}
