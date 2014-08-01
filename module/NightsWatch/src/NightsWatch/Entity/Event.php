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
 * @property \NightsWatch\Entity\User $user
 * @property \NightsWatch\Entity\EventRsvp $rsvps
 */
class Event
{
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

    public function getParsedDescription()
    {
        return MarkdownExtra::defaultTransform($this->description);
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
