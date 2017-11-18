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