<?php

namespace NightsWatch\Form;

use NightsWatch\Entity\Event;
use NightsWatch\Entity\EventRsvp as RSVP;
use Zend\Form\Form;

class RsvpForm extends Form
{
    public function __construct(Event $event)
    {
        parent::__construct('rsvp');

        $this->add(
            [
                'name'       => 'attendance',
                'type'       => 'select',
                'attributes' => [
                    'id'       => 'attendance',
                    'required' => true,
                    'value'    => RSVP::RSVP_ATTENDING,
                ],
                'options' => [
                    'value_options' => [
                        RSVP::RSVP_ATTENDING => RSVP::getRsvpNameFromType(RSVP::RSVP_ATTENDING),
                        RSVP::RSVP_MAYBE     => RSVP::getRsvpNameFromType(RSVP::RSVP_MAYBE),
                        RSVP::RSVP_ABSENT    => RSVP::getRsvpNameFromType(RSVP::RSVP_ABSENT),
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'event',
                'type'       => 'hidden',
                'attributes' => [
                    'value' => $event->id,
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => 'RSVP',
                    'class' => 'btn btn-primary',
                ],
            ]
        );
    }
}
