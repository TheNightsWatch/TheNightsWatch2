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
use NightsWatch\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;

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

        return new ViewModel(['days' => $days, 'month' => $month, 'year' => $year]);
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
        // TODO Create Events
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
