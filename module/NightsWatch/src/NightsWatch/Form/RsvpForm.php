<?php

namespace NightsWatch\Form;

use NightsWatch\Entity\Event;
use Zend\Form\Form;
use NightsWatch\Entity\EventRsvp as RSVP;

class RsvpForm extends Form
{
    public function __construct(Event $event)
    {
        parent::__construct('rsvp');

        $this->add(
            [
                'name' => 'status',
                'type' => 'select',
                'attributes' => [
                    'id' => 'status',
                    'required' => true,
                    'value' => RSVP::RSVP_ATTENDING,
                ],
                'options' => [
                    'value_options' => [
                        RSVP::RSVP_ATTENDING => 'Attending',
                        RSVP::RSVP_MAYBE => 'Possibly Attending',
                        RSVP::RSVP_ABSENT => 'Not Attending',
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'event',
                'type' => 'hidden',
                'attributes' => [
                    'value' => $event->id
                ],
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'type' => 'submit',
                'attributes' => [
                    'value' => 'RSVP',
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}
