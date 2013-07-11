<?php

namespace NightsWatch\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class VerifyForm extends Form
{
    private $inputFilter = null;

    public function __construct($name = null)
    {
        parent::__construct('verify');
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => 'username',
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
                'name' => 'password',
                'attributes' => [
                    'type' => 'password',
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

    public function getInputFilter()
    {
        if (is_null($this->inputFilter)) {
            $inputFilter = new InputFilter();

            $inputFilter->add(
                [
                    'name' => 'username',
                    'required' => true,
                ]
            );

            $inputFilter->add(
                [
                    'name' => 'password',
                    'required' => true,
                ]
            );
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}
