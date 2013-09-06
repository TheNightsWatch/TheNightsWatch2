<?php
/**
 * Created by PhpStorm.
 * User: Navarr
 * Date: 9/5/13
 * Time: 8:56 PM
 */

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class EventRsvp
 * @package NightsWatch\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="rsvp")
 * @property Event $event
 * @property User $user
 * @property int $attendance
 * @property boolean $attended
 * @property \DateTime $timestamp
 */
class EventRsvp
{
    const RSVP_ABSENT = 0;
    const RSVP_ATTENDING = 1;
    const RSVP_MAYBE = 2;

    /**
     * @var Event
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Event")
     */
    protected $event;

    /**
     * @var User
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $attendance;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", columnDefinition="TIMESTAMP")
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
