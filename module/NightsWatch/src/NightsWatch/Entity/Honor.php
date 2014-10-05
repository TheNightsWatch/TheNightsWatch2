<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM,
    NightsWatch\Entity\User;

/**
 * A User's Honor
 *
 * @ORM\Entity
 * @ORM\Table(name="honor")
 * @property int $id
 * @property User $user
 * @property \DateTime $timestamp
 * @property string $reason
 */
class Honor
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="honors")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $timestamp;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $reason;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function __get($property)
    {
        return $this->{$property};
    }

    public function __set($property, $value)
    {
        $this->{$property} = $value;
    }
}
