<?php

namespace NightsWatch\Form;

use NightsWatch\Entity\User;
use Zend\Form\Form;

class AssignRankForm extends Form
{
    public function __construct(User $user, array $options = array())
    {
        parent::__construct('assign_rank');

        $this->add(
            [
                'name' => 'id',
                'type' => 'hidden',
                'attributes' => [
                    'id' => 'id',
                    'required' => true,
                    'value' => $user->id,
                ],
                'options' => [
                    'label' => 'ID',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'rank',
                'type' => 'select',
                'attributes' => [
                    'id' => 'rank',
                    'required' => true,
                    'value' => $user->rank,
                ],
                'options' => [
                    'label' => 'Rank',
                    'value_options' => \NightsWatch\Entity\User::getRankNames(),
                ]
            ]
        );

        $this->add(
            [
                'name' => 'accord',
                'type' => 'checkbox',
                'attributes' => [
                    'checked_value' => 1,
                    'unchecked_value' => 0,
                    'value' => $user->accordMember,
                ],
                'options' => [
                    'label' => 'Accord member',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'deserter',
                'type' => 'checkbox',
                'attributes' => [
                    'checked_value' => 1,
                    'unchecked_value' => 0,
                    'value' => $user->deserter,
                ],
                'options' => [
                    'label' => 'Deserter'
                ]
            ]
        );

        $this->add(
            [
                'name' => 'denyjoin',
                'type' => 'checkbox',
                'attributes' => [
                    'checked_value' => 1,
                    'unchecked_value' => 0,
                    'value' => $user->deniedJoin,
                ],
                'options' => [
                    'label' => 'Can\'t rejoin as Recruit',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'email',
                'type' => 'multi_checkbox',
                'attributes' => [
                    'value' => $user->emailNotifications
                ],
                'options' => [
                    'label' => 'Emails',
                    'value_options' => [
                        User::EMAIL_ANNOUNCEMENT => 'Announcements',
                        User::EMAIL_ELECTION => 'Elections'
                    ]
                ]
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'type' => 'submit',
                'attributes' => [
                    'value' => 'Set/re-set Rank',
                    'class' => 'btn btn-primary',
                ]
            ]
        );
    }
}
