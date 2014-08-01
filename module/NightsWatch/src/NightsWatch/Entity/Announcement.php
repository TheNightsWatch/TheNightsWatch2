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
     * @ORM\Column(type="datetime")
     */
    protected $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function getParsedContent()
    {
        // Replace old announcements
        $oldAnnouncementDiv = [
            '<div style="color:#222;width:400px;font-family:Georgia, serif;text-align:justify;line-height:14pt;' .
            'font-size:12pt;">',
            '<div style="color:#222;width:400px;font-family:Georgia, serif;text-align:left;line-height:14pt;' .
            'font-size:12pt;">',
        ];
        $newOldAnnouncementDiv = '<div style="width:400px;font-family:Georgia, serif;text-align:justify;' .
            'line-height:14pt;font-size:12pt;">';
        $content = str_replace($oldAnnouncementDiv, $newOldAnnouncementDiv, $this->content);
        return MarkdownExtra::defaultTransform($content);
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
