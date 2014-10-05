<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\Mapping as ORM;
use NightsWatch\Entity\User;

/**
 * A User's Accolade
 *
 * @package NightsWatch\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="accolade")
 * @property int       $id
 * @property User      $user
 * @property User      $givenBy
 * @property \DateTime $timestamp
 * @property string    $reason
 * @property \DateTime $voidedOn
 */
class Accolade
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="accolades")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="giver_id", referencedColumnName="id", nullable=false)
     */
    protected $givenBy;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $timestamp;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $reason;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $voidedOn;
}
