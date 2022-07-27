<?php

namespace NightsWatch\Entity;

class ElectionVote
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $voter;

    /**
     * @var ElectionOption
     * @ORM\ManyToOne(targetEntity="ElectionOption")
     */
    protected $option;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $rank = null;

    public function __get($property)
    {
        return $this->{$property};
    }

    public function __set($property, $value)
    {
        $this->{$property} = $value;
    }

    public function populate(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
