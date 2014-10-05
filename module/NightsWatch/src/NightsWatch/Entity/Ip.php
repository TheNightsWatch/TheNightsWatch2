<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * An IP Address
 *
 * @package NightsWatch\Entity
 * @ORM\Entity
 * @ORM\Table(name="ip", uniqueConstraints={@ORM\UniqueConstraint(name="user_ip_unique", columns={"userId", "ip"})})
 * @property int $id
 * @property string $ip
 * @property User $user
 * @property \DateTime $firstSeen
 * @property \DateTime $lastSeen
 */
class Ip
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
    protected $ip;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="ips")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $firstSeen;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $lastSeen;

    public function __construct()
    {
        $this->firstSeen = $this->lastSeen = new \DateTime();
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
     * Convert the object into an array
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $vars = get_object_vars($this);
        // unset private vars
        unset($vars['password']);

        return $vars;
    }

    public function populate(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
