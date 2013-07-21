<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;
use Michelf\MarkdownExtra;

/**
 * Class Announcement
 * @package NightsWatch\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="announcement")
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $lowestReadableRank
 * @property \NightsWatch\Entity\User $user
 * @property \DateTime $timestamp
 */
class Announcement
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
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $lowestReadableRank;

    /**
     * @var \NightsWatch\Entity\User
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", columnDefinition="TIMESTAMP")
     */
    protected $timestamp;

    public function getParsedContent()
    {
        return MarkdownExtra::defaultTransform($this->content);
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
