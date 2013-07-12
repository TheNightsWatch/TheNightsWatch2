<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChatToken
 * @package NightsWatch\Entity
 * @ORM\Entity
 * @ORM\Table(name="chatToken")
 * @property int $id
 * @property User $user
 * @property string $token
 * @property \DateTime $expires
 */
class ChatToken
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
    protected $token = null;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", columnDefinition="TIMESTAMP")
     */
    protected $expires;

    public function __construct()
    {
        $this->expires = new \DateTime("+2 minutes");
    }

    public function generateToken()
    {
        $userId = $this->user->id;
        $now = time();
        $token = sha1($userId . 'chatToken' . $now);
        $this->token = $token;
        return $token;
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
