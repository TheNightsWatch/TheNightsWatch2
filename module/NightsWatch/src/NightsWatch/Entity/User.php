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
 * @property Accolade[] $accolades
 * @property int $rank
 * @property int $order
 * @property bool $admin
 * @property bool $deniedJoin If true, the user is not allowed to become a recruit
 * @property bool $deserter If true, the user has deserted the Watch
 * @property \DateTime $joined
 * @property int $emailNotifications
 * @property bool $banned
 * @property \DateTime $recruitmentDate
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

    // Order Constants
    const ORDER_STEWARD = 0;
    const ORDER_RANGER = 1;
    const ORDER_BUILDER = 2;

    // Email Notification Constants, used as bitwise
    const EMAIL_ANNOUNCEMENT = 0b1;

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
     * @var Accolade[]
     * @ORM\OneToMany(targetEntity="Accolade", mappedBy="user")
     */
    protected $accolades;

    /**
     * @var int
     * @ORM\Column(type="boolean")
     */
    protected $admin = false;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $joined = null;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $recruitmentDate = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $deniedJoin = false;

    /**
     * @var int
     * @ORM\Column(name="`order`", type="integer", nullable=true)
     */
    protected $order = null;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $emailNotifications = self::EMAIL_ANNOUNCEMENT;

    /**
     * An honorary title, %1$s is username, %2$s is order
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $banned = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $deserter = false;

    /**
     * @var Ip[]
     * @ORM\OneToMany(targetEntity="Ip", mappedBy="user")
     */
    protected $ips;

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

    public static function getOrderNames()
    {
        return [
            static::ORDER_STEWARD => 'Steward',
            static::ORDER_RANGER => 'Ranger',
            static::ORDER_BUILDER => 'Builder',
        ];
    }

    public static function getOrderName($order)
    {
        return static::getOrderNames()[$order];
    }

    public function getTitleOrRank()
    {
        if (!is_null($this->title)) {
            return sprintf($this->title, '', $this->order);
        }
        if ($this->rank == User::RANK_GENERAL) {
            switch ($this->order) {
                case User::ORDER_BUILDER:
                    return 'First Builder';
                case User::ORDER_STEWARD:
                    return 'First Steward';
                case User::ORDER_RANGER:
                    return 'First Ranger';
            }
        }
        if ($this->deserter) {
            return 'Deserter';
        }
        return static::getRankName($this->rank);
    }

    public function getTitleWithName()
    {
        if (!is_null($this->title)) {
            return sprintf($this->title, $this->username, static::getOrderName($this->order));
        }
        if ($this->deserter) {
            return $this->username . ', Deserter';
        }
        switch ($this->rank) {
            case static::RANK_RECRUIT:
                return $this->username . ', ' . static::getRankName($this->rank);
            case static::RANK_PRIVATE:
                return 'Private ' . $this->username . ', ' . static::getOrderName($this->order);
            case static::RANK_CORPORAL:
                return 'Corporal ' . $this->username . ', ' . static::getOrderName($this->order);
            case static::RANK_LIEUTENANT:
                return 'Lieutenant ' . $this->username . ', ' . static::getOrderName($this->order);
            case static::RANK_GENERAL:
                $rank = 'First ';
                switch ($this->order) {
                    case static::ORDER_BUILDER:
                        $rank .= 'Builder ';
                        break;
                    case static::ORDER_RANGER:
                        $rank .= 'Ranger ';
                        break;
                    case static::ORDER_STEWARD:
                        $rank .= 'Steward ';
                        break;
                }
                $rank .= $this->username;
                return $rank;
            case static::RANK_COMMANDER:
                return 'Lord Commander ' . $this->username;
            default:
                return $this->username;
        }
    }

    public function __construct()
    {
        $this->joined = new \DateTime();
        $this->honors = new ArrayCollection();
        $this->ips = new ArrayCollection();
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

    public static function getAvatarForUser($username, $size = 16)
    {
        return "//minotar.net/helm/{$username}/{$size}.png";
    }

    public function getAvatar($size = 16)
    {
        return static::getAvatarForUser($this->username, $size);
    }
}
