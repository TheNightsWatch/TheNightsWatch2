<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;
use Michelf\MarkdownExtra;

/**
 * Class Event
 *
 * @package NightsWatch\Entity
 * @ORM\Entity
 * @ORM\Table(name="event")
 * @property int                            $id
 * @property string                         $name
 * @property string                         $description
 * @property \DateTime                      $start
 * @property int                            $lowestViewableRank
 * @property int                            $region
 * @property int                            $type
 * @property string                         $report
 * @property \NightsWatch\Entity\User       $leader
 * @property \NightsWatch\Entity\User       $user
 * @property \NightsWatch\Entity\EventRsvp  $rsvps
 */
class Event
{
    const REGION_NONE = 0;
    const REGION_US   = 1;
    const REGION_EU   = 2;

    const EVENT_INFORMAL = 0;
    const EVENT_RANGING  = 1;
    const EVENT_FORMAL   = 2;

    private static $regionMap;

    private static $typeMap;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $start;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $lowestViewableRank;

    /**
     * The user who created the event
     *
     * @var \NightsWatch\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @var \NightsWatch\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $leader;

    /**
     * @var \NightsWatch\Entity\EventRsvp[]
     * @ORM\OneToMany(targetEntity="EventRsvp", mappedBy="event")
     */
    protected $rsvps = [];

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $region;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type="text", nullable = true)
     */
    protected $report = null;

    public function getParsedDescription()
    {
        return MarkdownExtra::defaultTransform($this->description);
    }

    /**
     * @return string
     */
    public function getRegionName()
    {
        return static::getRegionNameByregion($this->region);
    }

    public static function getRegionNames()
    {
        if (!isset(static::$regionMap)) {
            static::$regionMap = [
                static::REGION_NONE => 'Not Applicable',
                static::REGION_US   => 'United States',
                static::REGION_EU   => 'Europe'
            ];
        }
        return static::$regionMap;
    }

    public static function getRegionNameByRegion($region)
    {
        $regions = static::getRegionNames();
        if (!isset($regions[$region])) {
            throw new \InvalidArgumentException('Invalid region');
        }
        return $regions[$region];
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return static::getTypeNameByType($this->type);
    }

    public function canEdit(User $user)
    {
        return static::canEditEvent($this, $user);
    }

    public static function canEditEvent(self $event, User $user)
    {
        $now = new \DateTimeImmutable();
        if ($event->start->getTimestamp() < $now->getTimestamp()) {
            // Too Old
            return false;
        }

        $isCouncil = $user->rank >= User::RANK_LIEUTENANT;
        $isLeader  = is_null($event->leader) ? false : $event->leader->id == $user->id;
        return $isCouncil || $isLeader;
    }

    public static function getTypeNames()
    {
        if (!isset(static::$typeMap)) {
            static::$typeMap = [
                static::EVENT_INFORMAL => 'Informal Event',
                static::EVENT_FORMAL   => 'Official Event',
                static::EVENT_RANGING  => 'Official Ranging'
            ];
        }
        return static::$typeMap;
    }

    public static function getTypeNameByType($type)
    {
        $types = static::getTypeNames();
        if (!isset($types[$type])) {
            throw new \InvalidArgumentException('Invalid type');
        }
        return $types[$type];
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
