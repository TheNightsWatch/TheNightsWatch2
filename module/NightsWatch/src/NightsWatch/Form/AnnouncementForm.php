<?php

namespace NightsWatch\Form;

use Zend\Form\Form;

class AnnouncementForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('announcement');

        $this->add(
            [
                'name' => 'title',
                'type' => 'text',
                'attributes' => [
                    'id' => 'title',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Title',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'content',
                'type' => 'textarea',
                'attributes' => [
                    'id' => 'content',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Announcement',
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'You can use <a href="http://daringfireball.net/projects/markdown/basics" target="_blank">markdown</a> to format your text.</a>',
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'lowrank',
                'type' => 'select',
                'attributes' => [
                    'id' => 'lowrank',
                    'required' => true,
                    'value' => \NightsWatch\Entity\User::RANK_PRIVATE,
                ],
                'options' => [
                    'label' => 'Rank',
                    'value_options' => \NightsWatch\Entity\User::getRankNames(),
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'The lowest rank that\'s allowed to view this announcement',
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'preview',
                'type' => 'hidden',
                'attributes' => [
                    'value' => 0,
                ],
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'type' => 'submit',
                'attributes' => [
                    'value' => 'Preview',
                    'id' => 'submit-announcement',
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}
