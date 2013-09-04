<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Navarr
 * Date: 8/15/13
 * Time: 1:23 AM
 * To change this template use File | Settings | File Templates.
 */

namespace NightsWatch\Controller;

use Doctrine\Common\Collections\Criteria;
use NightsWatch\Entity\Event;
use NightsWatch\Entity\User;
use NightsWatch\Form\EventForm;
use NightsWatch\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class EventController extends ActionController
{
    public function indexAction()
    {
        $this->updateLayoutWithIdentity();

        $month = $this->params()->fromQuery('month', date('n'));
        $year = $this->params()->fromQuery('year', date('Y'));

        $days = static::createDaysArrayForCalendar($month, $year);

        $first = $days[0]['stamp'];
        $last = $days[count($days)-1]['stamp'];

        // Query for Events
        $rank = is_null($this->getIdentityEntity()) ? 0 : $this->getIdentityEntity()->rank;

        $criteria = Criteria::create()
            ->where(Criteria::expr()->lte('lowestViewableRank', $rank))
            ->andWhere(Criteria::expr()->gte('start', new \DateTime($first)))
            ->andWhere(Criteria::expr()->lt('start', new \DateTime($last)))
            ->orderBy(['start' => 'ASC']);

        /** @var \NightsWatch\Entity\Event[] $events */
        $events = $this->getEntityManager()
            ->getRepository('NightsWatch\Entity\Event')
            ->matching($criteria);

        $dayIncrement = 0;
        foreach ($events as $event) {
            while ($event->start->format('Y-m-d') != $days[$dayIncrement]['stamp']) {
                ++$dayIncrement;
            }
            $days[$dayIncrement]['events']++;
        }

        $identity = $this->getIdentityEntity();
        return new ViewModel(['days' => $days, 'month' => $month, 'year' => $year, 'identity' => $identity]);
    }

    public function dateAction()
    {
        $this->updateLayoutWithIdentity();

        $user = $this->getIdentityEntity();

        $year = intval($this->params()->fromRoute('year'), 10);
        $month = intval($this->params()->fromRoute('month'), 10);
        $day = intval($this->params()->fromRoute('day'), 10);

        $start = new \DateTime("{$year}-{$month}-{$day}");
        $end = clone $start;
        $end->add(new \DateInterval('P1D'));

        // Get any events from th Database.
        $criteria = Criteria::create()
            ->where(Criteria::expr()->lte('lowestViewableRank', $user->rank))
            ->andWhere(Criteria::expr()->gte('start', $start))
            ->andWhere(Criteria::expr()->lt('start', $end))
            ->orderBy(['start' => 'ASC']);

        /** @var \NightsWatch\Entity\Event[] $events */
        $events = $this->getEntityManager()
            ->getRepository('NightsWatch\Entity\Event')
            ->matching($criteria);

        return new ViewModel(['events' => $events]);
    }

    public function viewAction()
    {
        $this->updateLayoutWithIdentity();

        $rank = is_null($this->getIdentityEntity()) ? 0 : $this->getIdentityEntity()->rank;
        $id = $this->params()->fromRoute('id');

        /** @var \NightsWatch\Entity\Event $event */
        $event = $this->getEntityManager()
            ->getRepository('NightsWatch\Entity\Event')
            ->find($id);

        if (is_null($event)) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        if ($rank < $event->lowestViewableRank) {
            $this->getResponse()->setStatusCode(403);
            return;
        }

        return new ViewModel(['event' => $event]);
    }

    public function rsvpAction()
    {
        // TODO RSVP For Events
    }

    public function createAction()
    {
        $this->updateLayoutWithIdentity();

        if ($this->disallowRankLessThan(User::RANK_LIEUTENANT)) {
            return false;
        }

        $form = new EventForm();
        $session = new SessionContainer('NightsWatch\Event\Create');
        if (!empty($session->name)) {
            $form->setData(
                [
                    'name' => $session->name,
                    'description' => $session->description,
                    'lowrank' => $session->rank,
                    'date' => $session->date,
                    'time' => $session->time,
                    'offset' => $session->offset,
                ]
            );
        }
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $session = new SessionContainer('NightsWatch\Event\Create');
                $session->name = $form->get('name')->getValue();
                $session->description = $form->get('description')->getValue();
                $session->rank = $form->get('lowrank')->getValue();
                $session->date = $form->get('date')->getValue();
                $session->time = $form->get('time')->getValue();
                $session->offset = $form->get('offset')->getValue();
                $this->redirect()->toRoute('home', ['controller' => 'event', 'action' => 'preview']);
                return false;
            }
        }
        return new ViewModel(['form' => $form, 'identity' => $this->getIdentityEntity()]);
    }

    public function previewAction()
    {
        $this->updateLayoutWithIdentity();

        if ($this->disallowRankLessThan(User::RANK_LIEUTENANT)) {
            return false;
        }

        $session = new SessionContainer('NightsWatch\Event\Create');
        if (empty($session->name)) {
            $this->redirect()->toRoute('home', ['contoller' => 'event', 'action' => 'create']);
            return false;
        }

        $event = new Event();
        $event->name = $session->name;
        $event->description = $session->description;
        $event->user = $this->getIdentityEntity();
        $event->start = new \DateTime($session->date . ' ' . $session->time);
        $offset = $session->offset + date('Z');
        $event->start->sub(new \DateInterval('PT' . $offset . 'S'));
        $event->lowestViewableRank = $session->rank;

        if ($this->getRequest()->isPost()) {
            $this->getEntityManager()->persist($event);
            $this->getEntityManager()->flush();

            $session->name = '';

            $this->redirect()->toRoute('id', ['controller' => 'event', 'id' => $event->id]);
            return false;
        }

        return new ViewModel(['event' => $event]);
    }

    // We're going to create $days = [ [month: '', day: '', events: 0] ]
    // outer is month, first child is weeks, second child is days
    private static function createDaysArrayForCalendar($month, $year)
    {
        $days = [];

        $date = \DateTime::createFromFormat('Y n j', "{$year} {$month} 1");
        $oneDay = new \DateInterval('P1D');

        // Back up to a Sunday
        while ($date->format('w') > 0) {
            $date->sub($oneDay);
        }

        while ($date->format('n') < $month + 1 || $date->format('w') != 0) {
            $days[] = [
                'stamp' => $date->format('Y-m-d'),
                'year' => $date->format('Y'),
                'month' => $date->format('m'),
                'day' => $date->format('d'),
                'events' => 0
            ];

            $date->add($oneDay);
        }

        return $days;
    }
}
