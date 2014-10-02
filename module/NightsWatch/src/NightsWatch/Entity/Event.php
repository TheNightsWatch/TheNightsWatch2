<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;
use Michelf\MarkdownExtra;

/**
 * Class Event
 * @package NightsWatch\Entity
 * @ORM\Entity
 * @ORM\Table(name="event")
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \DateTime $start
 * @property int $lowestViewableRank
 * @property int $region
 * @property \NightsWatch\Entity\User $user
 * @property \NightsWatch\Entity\EventRsvp $rsvps
 */
class Event
{
    const REGION_NONE = 0;
    const REGION_US = 1;
    const REGION_EU = 2;

    private static $regionMap;

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
     * @var \NightsWatch\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

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
                static::REGION_US => 'United States',
                static::REGION_EU => 'Europe'
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

    public function __get($property)
    {
        return $this->{$property};
    }

    public function __set($property, $value)
    {
        $this->{$property} = $value;
    }
}
