<?php

namespace NightsWatch\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class EventView
 *
 * @package NightsWatch\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="event_view")
 * @property Event     $event
 * @property User      $user
 * @property \DateTime $firstViewed
 * @property \DateTime $lastViewed
 */
class EventView
{
    /**
     * @var Event
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Event")
     */
    protected $event;

    /**
     * @var User
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $firstViewed;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $lastViewed;

    public function __construct()
    {
        $this->lastViewed = $this->firstViewed = new \DateTime();
    }

    public function updateLastViewed()
    {
        $this->lastViewed = new \DateTime();
    }

    /**
     * Returns a view object which MUST be persist'ed and flushed.
     *
     * @param EntityManager $entityManager
     * @param Event         $event
     * @param User          $user
     *
     * @return \NightsWatch\Entity\EventView
     */
    public static function triggerView(EntityManager $entityManager, Event $event, User $user = null)
    {
        if (is_null($user)) {
            return;
        }
        /** @var EventView $view */
        $view = $entityManager->getRepository('NightsWatch\Entity\EventView')
            ->findOneBy(['event' => $event, 'user' => $user]);

        if (is_null($view)) {
            $view = new static();
            $view->user = $user;
            $view->event = $event;
        } else {
            $view->updateLastViewed();
        }

        return $view;
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
