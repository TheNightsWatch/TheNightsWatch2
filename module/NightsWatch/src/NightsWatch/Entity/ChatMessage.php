<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Navarr
 * Date: 7/12/13
 * Time: 5:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class ChatMessage
 * @package NightsWatch\Entity
 * @ORM\Entity
 * @ORM\Table(name="chatMessage")
 * @property int $id
 * @property User $user
 * @property string $chatroom
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

    public function __get($property)
    {
        return $this->{$property};
    }

    public function __set($property, $value)
    {
        $this->{$property} = $value;
    }
}
