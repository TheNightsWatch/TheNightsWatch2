<?php

namespace NightsWatch\Form;

use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('login');

        $this->add(
            [
                'name' => 'username',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'login-username',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Username',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'password',
                'attributes' => [
                    'type' => 'password',
                    'id' => 'login-password',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Password',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Log In',
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}
