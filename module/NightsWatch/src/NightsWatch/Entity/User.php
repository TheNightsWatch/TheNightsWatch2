<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A User
 *
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $minecraftId
 * @property Honor[] $honors
 * @property int $rank
 * @property bool $admin
 * @property \DateTime $joined
 */
class User
{
    // Rank Constants, with space to grow.
    // The bigger the number, the higher the rank.
    const RANK_ADMIN = 50000;
    const RANK_COMMANDER = 10000;
    const RANK_GENERAL = 5000;
    const RANK_LIEUTENANT = 1000;
    const RANK_CORPORAL = 500;
    const RANK_PRIVATE = 2;
    const RANK_RECRUIT = 1;
    const RANK_CIVILIAN = 0;

    const ORDER_STEWARD = 0;
    const ORDER_RANGER = 1;
    const ORDER_BUILDER = 2;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    protected $minecraftId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $rank = self::RANK_CIVILIAN;

    /**
     * @var Honor[]
     * @ORM\OneToMany(targetEntity="Honor", mappedBy="user")
     */
    protected $honors;

    /**
     * @var int
     * @ORM\Column(type="boolean")
     */
    protected $admin = false;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", columnDefinition="TIMESTAMP")
     */
    protected $joined = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $deniedJoin = false;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $order = null;

    public static function getRankNames()
    {
        return [
            static::RANK_ADMIN => 'Admin',
            static::RANK_COMMANDER => 'Lord Commander',
            static::RANK_GENERAL => 'General',
            static::RANK_LIEUTENANT => 'Lieutenant',
            static::RANK_CORPORAL => 'Corporal',
            static::RANK_PRIVATE => 'Private',
            static::RANK_RECRUIT => 'Recruit',
            static::RANK_CIVILIAN => 'Civilian',
        ];
    }

    public static function getRankName($rank)
    {
        return static::getRankNames()[$rank];
    }

    public function __construct()
    {
        $this->joined = new \DateTime();
        $this->honors = new ArrayCollection();
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
