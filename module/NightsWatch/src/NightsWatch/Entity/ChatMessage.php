<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChatMessage
 * @package NightsWatch\Entity
 * @ORM\Entity
 * @ORM\Table(name="chatMessage")
 * @property int $id
 * @property User $user
 * @property string $chatroom
 * @property \DateTime $timestamp
 * @property string $message
 */
class ChatMessage
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \NightsWatch\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $chatroom;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $message;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $timestamp;

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
