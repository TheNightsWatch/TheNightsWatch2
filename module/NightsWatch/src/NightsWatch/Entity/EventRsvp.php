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
 *
 * @package NightsWatch\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="rsvp")
 * @property Event     $event
 * @property User      $user
 * @property int       $attendance
 * @property boolean   $attended
 * @property \DateTime $timestamp
 * @property string    $notes
 */
class EventRsvp
{
    const RSVP_ABSENT    = 0;
    const RSVP_ATTENDING = 1;
    const RSVP_MAYBE     = 2;
    const RSVP_NONE      = 3;

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
    protected $attendance = 0;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $attended = null;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $timestamp;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $notes;

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

    /**
     * @param int $type
     *
     * @return string
     */
    public static function getRsvpNameFromType($type)
    {
        switch ($type) {
            case static::RSVP_ATTENDING:
                return 'Attending';
            case static::RSVP_MAYBE:
                return 'Possibly Attending';
            case static::RSVP_ABSENT:
            default:
                return 'Not Attending';
            case static::RSVP_NONE:
                return 'No RSVP';
        }
    }
}
