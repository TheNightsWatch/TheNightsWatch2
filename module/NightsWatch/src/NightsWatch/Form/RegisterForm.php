<?php

namespace NightsWatch\Form;

use Zend\Form\Element\Collection;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class RegisterForm extends Form
{
    private $inputFilter = null;

    public function __construct($name = null)
    {
        parent::__construct('register');

        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => 'email',
                'type' => 'email',
                'attributes' => [
                    'type' => 'email',
                    'id' => 'register-email',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Email',
                ],
                'validators' => [
                    [
                        'name' => 'EmailAddress',
                    ]
                ]
            ]
        );

        $this->add(
            [
                'name' => 'username',
                'type' => 'text',
                'attributes' => [
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
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 16,
                        ],
                    ],
                    [
                        'name' => 'Alpha',
                        'options' => [
                            'allowWhiteSpace' => false,
                        ],
                    ],
                    [
                        'name' => 'NightsWatch\Validator\MinecraftUsername',
                    ],
                ]
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
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 100,
                        ],
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
                'validators' => [
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => 'password',
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

    public function getInputFilter()
    {
        if (is_null($this->inputFilter)) {
            $inputFilter = new InputFilter();

            $inputFilter->add(
                [
                    'name' => 'email',
                    'required' => true,
                    'validators' => [
                        [
                            'name' => 'EmailAddress',
                        ]
                    ]
                ]
            );

            $inputFilter->add(
                [
                    'name' => 'username',
                    'required' => true,
                    'validators' => [
                        [
                            'name' => 'StringLength',
                            'options' => [
                                'encoding' => 'UTF-8',
                                'min' => 1,
                                'max' => 16,
                            ],
                        ],
                        [
                            'name' => 'Alpha',
                            'options' => [
                                'allowWhiteSpace' => false,
                            ],
                        ],
                        [
                            'name' => 'NightsWatch\Validator\MinecraftUsername',
                        ],
                    ]
                ]
            );

            $inputFilter->add(
                [
                    'name' => 'password',
                    'required' => true,
                    'validators' => [
                        [
                            'name' => 'StringLength',
                            'options' => [
                                'encoding' => 'UTF-8',
                                'min' => 5,
                                'max' => 100,
                            ],
                        ],
                    ],
                ]
            );

            $inputFilter->add(
                [
                    'name' => 'password2',
                    'required' => true,
                    'validators' => [
                        [
                            'name' => 'Identical',
                            'options' => [
                                'token' => 'password',
                            ],
                        ],
                    ],
                ]
            );

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}
