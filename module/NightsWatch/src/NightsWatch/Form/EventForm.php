<?php

namespace NightsWatch\Form;

use NightsWatch\Entity\Event;
use Zend\Form\Element\Time;
use Zend\Form\Form;

class EventForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('event');

        $this->add(
            [
                'name' => 'name',
                'type' => 'text',
                'attributes' => [
                    'id' => 'name',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Name',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'date',
                'type' => 'text',
                'attributes' => [
                    'id' => 'date',
                    'required' => true,
                    'class' => 'datepicker',
                ],
                'options' => [
                    'label' => 'Date',
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'Local Time',
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'time',
                'type' => Time::class,
                'attributes' => [
                    'id' => 'time',
                    'required' => true,
                    'min' => '00:00:00',
                    'max' => '23:59:59',
                    'step' => 60,
                ],
                'options' => [
                    'label' => 'Time (local)',
                    'format' => 'H:i:s',
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'Local Time',
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'offset',
                'type' => 'text',
                'attributes' => [
                    'id' => 'offset',
                    'required' => true,
                    'readonly' => true,
                    'class' => 'jsoffset disabled',
                ],
                'options' => [
                    'label' => 'GMT Offset',
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'This is your offset from GMT in seconds',
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'region',
                'type' => 'select',
                'attributes' => [
                    'id' => 'region',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Region Instance',
                    'value_options' => Event::getRegionNames(),
                    'bootstrap' => [
                        'help' => [
                            'style' => 'block',
                            'content' => 'The region the event will take place on.  (For differing database instances)'
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'description',
                'type' => 'textarea',
                'attributes' => [
                    'id' => 'description',
                    'required' => true,
                ],
                'options' => [
                    'label' => 'Description',
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
                            'content' => 'The lowest rank that\'s allowed to attend this event',
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
                    'id' => 'submit-event',
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}
