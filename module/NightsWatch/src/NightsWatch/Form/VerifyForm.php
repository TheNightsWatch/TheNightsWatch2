<?php

namespace NightsWatch\Form;

use Zend\Form\Form;

class VerifyForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('verify');
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => 'username',
                'attributes' => [
                    'type' => 'hidden',
                ]
            ]
        );

        $this->add(
            [
                'name' => 'email',
                'attributes' => [
                    'type' => 'hidden',
                ]
            ]
        );

        $this->add(
            [
                'name' => 'password',
                'attributes' => [
                    'type' => 'hidden',
                ]
            ]
        );

        $this->add(
            [
                'name' => 'mojang-login',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'mojang-login',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Mojang Login',
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'This is the username you log into Minecraft with.  If you have migrated your account, it will be an email address.',
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'mojang-password',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'mojang-password',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Mojang Password',
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'This password will be used once to verify your account.  It will not be saved.',
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
                    'value' => 'Verify Account',
                    'id' => 'verify-submit',
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}
