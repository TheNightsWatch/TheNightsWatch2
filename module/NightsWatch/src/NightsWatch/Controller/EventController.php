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
use NightsWatch\Entity\EventRsvp;
use NightsWatch\Entity\EventView;
use NightsWatch\Entity\User;
use NightsWatch\Form\EventForm;
use NightsWatch\Form\RsvpForm;
use NightsWatch\Mvc\Controller\ActionController;
use NightsWatch\Routine\DiscordMessage;
use Zend\Json\Server\Exception\HttpException;
use Zend\Mail\Address;
use Zend\Mail\Transport\Sendmail;
use Zend\Mime\Message as MimeBody;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Session\Container as SessionContainer;
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
        $last = $days[count($days) - 1]['stamp'];

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

        foreach ($events as $event) {
            EventView::triggerView($this->getEntityManager(), $event, $this->getIdentityEntity());
        }

        return new ViewModel(['events' => $events, 'user' => $this->getIdentityEntity()]);
    }

    public function quickRsvpAction()
    {
        $rsvp = $this->params()->fromQuery('rsvp');
        $this->updateLayoutWithIdentity();

        $rank = is_null($this->getIdentityEntity()) ? 0 : $this->getIdentityEntity()->rank;
        $id = $this->params()->fromRoute('id');

        /** @var Event $event */
        $event = $this->getEntityManager()
            ->getRepository('NightsWatch\Entity\Event')
            ->find($id);

        if (is_null($rsvp) || is_null($event) || $rank == 0 || $this->getIdentityEntity()->id == $event->leader->id) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $redirect = $this->disallowRankLessThan($event->lowestViewableRank);
        if ($redirect) {
            return false;
        }

        $view = EventView::triggerView($this->getEntityManager(), $event, $this->getIdentityEntity());
        $this->getEntityManager()->persist($view);
        $this->getEntityManager()->flush();

        return new ViewModel(['event' => $event, 'user' => $this->getIdentityEntity(), 'attendance' => $rsvp]);
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

        $redirect = $this->disallowRankLessThan($event->lowestViewableRank);
        if ($redirect) {
            return false;
        }

        $view = EventView::triggerView($this->getEntityManager(), $event, $this->getIdentityEntity());
        $this->getEntityManager()->persist($view);
        $this->getEntityManager()->flush();

        return new ViewModel(['event' => $event, 'user' => $this->getIdentityEntity()]);
    }

    public function rsvpAction()
    {
        if ($this->disallowGuest()) {
            return;
        }

        /** @var \NightsWatch\Entity\Event $event */
        $event = $this->getEntityManager()
            ->getRepository('NightsWatch\Entity\Event')
            ->find($this->params()->fromPost('event'));

        if ($this->disallowRankLessThan($event->lowestViewableRank)) {
            return;
        }

        if (!$event) {
            $this->getResponse()->setStatusCode(404);

            return;
        }

        $form = new RsvpForm($event);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                // First try to find an RSVP for the user
                $rsvp = $this->getEntityManager()
                    ->getRepository('NightsWatch\Entity\EventRsvp')
                    ->findOneBy(['event' => $event, 'user' => $this->getIdentityEntity()]);

                if ($rsvp == null) {
                    $rsvp = new EventRsvp();
                    $rsvp->user = $this->getIdentityEntity();
                    $rsvp->event = $event;
                }

                $rsvp->attendance = $form->get('attendance')->getValue();
                $rsvp->timestamp = new \DateTime();

                $this->getEntityManager()->persist($rsvp);
                $this->getEntityManager()->flush();
            }
        }

        $this->redirect()->toRoute('id', ['controller' => 'event', 'action' => 'view', 'id' => $event->id]);
    }

    public function createAction()
    {
        $this->updateLayoutWithIdentity();

        if ($this->disallowRankLessThan(User::RANK_CORPORAL)) {
            return false;
        }

        $form = new EventForm($this->getIdentityEntity());
        $session = new SessionContainer('NightsWatch\Event\Create');
        if (!empty($session->name)) {
            static::fillEventFormFromSession($form, $session);
        }
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $session = new SessionContainer('NightsWatch\Event\Create');
                $session->id = false;
                $session->name = $form->get('name')->getValue();
                $session->description = $form->get('description')->getValue();
                $session->rank = $form->get('lowrank')->getValue();
                $session->date = $form->get('date')->getValue();
                $session->time = $form->get('time')->getValue();
                $session->offset = $form->get('offset')->getValue();
                $session->region = $form->get('region')->getValue();
                $session->leader = $form->get('leader')->getValue();
                $session->type = $form->get('eventtype')->getValue();
                $session->accord = $form->get('accord')->getValue();
                $this->redirect()->toRoute('home', ['controller' => 'event', 'action' => 'preview']);

                return false;
            }
        }

        return new ViewModel(['form' => $form, 'identity' => $this->getIdentityEntity()]);
    }

    public function editAction()
    {
        $this->updateLayoutWithIdentity();

        if ($this->disallowRankLessThan(User::RANK_CIVILIAN)) {
            return false;
        }

        $id = $this->params()->fromRoute('id');

        /** @var \NightsWatch\Entity\Event $event */
        $event = $this->getEntityManager()
            ->getRepository('NightsWatch\Entity\Event')
            ->find($id);

        if (is_null($event)) {
            $this->getResponse()->setStatusCode(404);

            return;
        }

        if (!$event->canEdit($this->getIdentityEntity())) {
            $this->getResponse()->setStatusCode(403);

            return;
        }

        $form = new EventForm($this->getIdentityEntity(), null, true);
        $session = new SessionContainer('NightsWatch\Event\Create');
        if (!empty($session->name) && !empty($session->id)) {
            static::fillEventFormFromSession($form, $session);
        } else {
            static::fillEventFormFromEvent($form, $event);
        }

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $session = new SessionContainer('NightsWatch\Event\Create');
                $session->id = $form->get('id')->getValue();
                $session->name = $form->get('name')->getValue();
                $session->description = $form->get('description')->getValue();
                $session->rank = $form->get('lowrank')->getValue();
                $session->date = $form->get('date')->getValue();
                $session->time = $form->get('time')->getValue();
                $session->offset = $form->get('offset')->getValue();
                $session->region = $form->get('region')->getValue();
                $session->leader = $form->get('leader')->getValue();
                $session->type = $form->get('eventtype')->getValue();
                $session->sendemail = $form->get('sendemail')->getValue();
                $session->accord = $form->get('accord')->getValue();
                $this->redirect()->toRoute('home', ['controller' => 'event', 'action' => 'preview']);

                return false;
            }
        }

        return new ViewModel(['form' => $form, 'identity' => $this->getIdentityEntity()]);
    }

    public function previewAction()
    {
        $this->updateLayoutWithIdentity();

        if ($this->disallowRankLessThan(User::RANK_CORPORAL)) {
            return false;
        }

        $session = new SessionContainer('NightsWatch\Event\Create');
        if (empty($session->name)) {
            $this->redirect()->toRoute('home', ['contoller' => 'event', 'action' => 'create']);

            return false;
        }

        $userRepo = $this->getEntityManager()->getRepository('NightsWatch\Entity\User');
        $eventRepo = $this->getEntityManager()->getRepository('NightsWatch\Entity\Event');

        $leader = $session->leader;
        if (!empty($leader)) {
            $leader = $userRepo->findOneBy(['username' => $leader]);
        }
        if (empty($leader)) {
            $leader = $this->getIdentityEntity();
        }

        $newEvent = false;
        if (isset($session->id) && $session->id != false) {
            $event = $eventRepo->find($session->id);
            if (is_null($event)) {
                throw new \Exception('No such event found');
            }
        } else {
            $newEvent = true;
            $event = new Event();
        }
        $event->name = $session->name;
        $event->description = $session->description;
        if ($newEvent) {
            $event->user = $this->getIdentityEntity();
        }
        $event->start = new \DateTime($session->date . ' ' . $session->time);
        $event->region = $session->region;
        $event->leader = $leader;
        $event->accord = $session->accord;
        $event->type = $session->type;
        $offset = $session->offset + date('Z');
        $add = $offset > 0 ? true : false;
        $offset = abs($offset);
        $interval = new \DateInterval('PT' . $offset . 'S');
        if ($add) {
            $event->start->add($interval);
        } else {
            $event->start->sub($interval);
        }
        $event->lowestViewableRank = $session->rank;

        if ($this->getRequest()->isPost()) {
            // Verify that Event type is allowed
            if (!in_array($event->type, array_keys(EventForm::getEventTypesForRank($event->user->rank)))) {
                throw new HttpException('Disallowed', 403);
            }
            $this->getEntityManager()->persist($event);
            $this->getEntityManager()->flush();
            $event->user = $this->getIdentityEntity();

            $session->name = '';

            // Send out the Emails
            $sendSpecified = (isset($session->sendemail) && (bool)$session->sendemail);
            $sendEmails = $newEvent || $sendSpecified;
            if ($sendEmails) {
                $criteria = Criteria::create()
                    ->where(Criteria::expr()->gte('rank', $event->lowestViewableRank));

                /** @var User[] $users */
                $users = $userRepo->matching($criteria);

                $mail = new \Zend\Mail\Message();
                $mail->setSubject('[NightsWatch] Event: ' . $event->name);
                $mail->setFrom(new Address('noreply@minez-nightswatch.com', $event->user->username));
                $mail->setTo(new Address('members@minez-nightswatch.com', 'Members'));
                $headers = $mail->getHeaders();
                if ($newEvent) {
                    $headers->addHeaderLine('Message-Id', '<event-' . $event->id . '@threads.minez-nightswatch.com>');
                } else {
                    $headers->addHeaderLine('References', '<event-' . $event->id . '@threads.minez-nightswatch.com>');
                    $headers->addHeaderLine('In-Reply-To', '<event-' . $event->id . '@threads.minez-nightswatch.com>');
                }
                $headers->addHeaderLine('Threading-Id', '<event-' . $event->id . '@threads.minez-nightswatch.com>');
                $mail->setEncoding('UTF-8');

                $url = $this->url()->fromRoute(
                    'id',
                    ['controller' => 'event', 'action' => 'view', 'id' => $event->id],
                    ['force_canonical' => true]
                );

                $niceDate = $event->start->format('M j, Y');
                $region = $event->getRegionName();
                // Create a signature
                $title = trim($event->user->getTitleOrRank());
                if ($newEvent) {
                    $lead = 'A new event has been posted to the calendar.';
                } else {
                    $lead = 'An event on the calendar has been updated with new, important information.';
                }
                $event->description
                    = "{$lead}  All information concerning this event "
                    . 'is classified and only available to members of rank ' . User::getRankName(
                        $event->lowestViewableRank
                    )
                    . " and up.\n\n"
                    . 'You can read the details of this event at https://minez-nightswatch.com/event/' . $event->id
                    . "\n\nEvent Details:  \nDate: {$niceDate}  \nRSVP: [{$url}]({$url})  "
                    . ($event->region > 0 ? "\nRegion: {$region}" : '')
                    . "\n\n"
                    . "{$event->user->username}  \n*{$title}*";

                $body = new MimeBody();
                $bodyHtml = new MimePart($event->getParsedDescription());
                $bodyHtml->type = Mime::TYPE_HTML;
                $body->setParts([$bodyHtml]);
                $mail->setBody($body);

                foreach ($users as $user) {
                    if ($user->emailNotifications & User::EMAIL_ANNOUNCEMENT > 0) {
                        $mail->addBcc(new Address($user->email, $user->username));
                    }
                }
                $transport = new Sendmail();
                $transport->send($mail);

                $discordConfig = $this->getServiceLocator()->get('config')['NightsWatch']['discord'];
                $webhookConfig = $discordConfig['webhooks'];
                $webhook = isset($webhookConfig[$event->lowestViewableRank]) ? $webhookConfig[$event->lowestViewableRank] : false;
                if ($event->accord) {
                    $webhook = isset($webhookConfig['accord']) ? $webhookConfig['accord'] : false;
                }
                if ($webhook) {
                    $colorMap = [
                        User::RANK_CIVILIAN => '9936031',
                        User::RANK_RECRUIT => '1146986',
                        User::RANK_PRIVATE => '2123412',
                        User::RANK_CORPORAL => '10181046',
                        User::RANK_CAPTAIN => '15844367',
                        User::RANK_LIEUTENANT => '15105570',
                        'accord' => '14408667',
                    ];
                    $yes = $this->url()->fromRoute(
                            'id',
                            [
                                'controller' => 'event',
                                'action' => 'quickRsvp',
                                'id' => $event->id,
                                'rsvp' => EventRsvp::RSVP_ATTENDING
                            ],
                            ['force_canonical' => true]
                        ) . '?rsvp=' . EventRsvp::RSVP_ATTENDING;
                    $maybe = $this->url()->fromRoute(
                            'id',
                            [
                                'controller' => 'event',
                                'action' => 'quickRsvp',
                                'id' => $event->id,
                                'rsvp' => EventRsvp::RSVP_MAYBE
                            ],
                            ['force_canonical' => true]
                        ) . '?rsvp=' . EventRsvp::RSVP_MAYBE;
                    $no = $this->url()->fromRoute(
                            'id',
                            [
                                'controller' => 'event',
                                'action' => 'quickRsvp',
                                'id' => $event->id,
                                'rsvp' => EventRsvp::RSVP_ABSENT
                            ],
                            ['force_canonical' => true]
                        ) . '?rsvp=' . EventRsvp::RSVP_ABSENT;
                    $discordMessenger = new DiscordMessage($webhook);
                    $color = intval($event->accord ? $colorMap['accord'] : $colorMap[$event->lowestViewableRank]);
                    $classified = $event->accord ? 'Accord' : User::getRankName($event->lowestViewableRank);
                    $discordMessenger->perform(
                        [
                            'username' => 'The Night\'s Watch',
                            'content' => $lead,
                            'embeds' => [
                                [
                                    'title' => $event->name,
                                    'url' => $url,
                                    'color' => $color,
                                    'author' => [
                                        'name' => $event->leader->getTitleWithName(),
                                        'icon_url' => $event->leader->getAvatar(100),
                                    ],
                                    'fields' => [
                                        [
                                            'inline' => true,
                                            'value' => $classified . '+',
                                            'name' => 'Classified',
                                        ],
                                        [
                                            'inline' => true,
                                            'value' => "[Attending]({$yes}) · [Maybe]({$maybe}) · [No]({$no})",
                                            'name' => 'RSVP',
                                        ],
                                        [
                                            'inline' => true,
                                            'name' => 'Event Type',
                                            'value' => $event->getTypeName(),
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    );
                }
            }

            $this->redirect()->toRoute('id', ['controller' => 'event', 'id' => $event->id]);

            return false;
        }

        return new ViewModel(['event' => $event, 'user' => $this->getIdentityEntity()]);
    }

    // We're going to create $days = [ [month: '', day: '', events: 0] ]
    // outer is month, first child is weeks, second child is days

    private static function createDaysArrayForCalendar($month, $year)
    {
        $days = [];

        $date = \DateTime::createFromFormat('Y n j', "{$year} {$month} 1");
        $oneDay = new \DateInterval('P1D');

        // Calculate the Next Month
        $nextMonthDate = clone $date;
        $oneMonth = new \DateInterval('P1M');
        $nextMonthDate->add($oneMonth);
        $nextMonthYear = $nextMonthDate->format('Y');
        $nextMonthMonth = $nextMonthDate->format('n');
        $nextMonthDate = \DateTime::createFromFormat('Y n j', "{$nextMonthYear} {$nextMonthMonth} 1");

        // Add a week to the end of the Calendar
        $nextMonthDate->add(new \DateInterval('P7D'));

        // Back up to a Sunday
        while ($date->format('w') > 0) {
            $date->sub($oneDay);
        }

        while ($date < $nextMonthDate || $date->format('w') != 0) {
            $days[] = [
                'stamp' => $date->format('Y-m-d'),
                'year' => $date->format('Y'),
                'month' => $date->format('m'),
                'day' => $date->format('d'),
                'events' => 0,
            ];

            $date->add($oneDay);
        }

        return $days;
    }

    private static function fillEventFormFromSession(EventForm &$form, $session)
    {
        $data = [
            'name' => $session->name,
            'description' => $session->description,
            'lowrank' => $session->rank,
            'date' => $session->date,
            'time' => $session->time,
            'offset' => $session->offset,
            'region' => $session->region,
            'eventtype' => $session->type,
            'leader' => $session->leader,
            'accord' => $session->accord,
        ];
        if (isset($session->id)) {
            $data['id'] = $session->id;
        }
        $form->setData($data);
    }

    private static function fillEventFormFromEvent(EventForm &$form, Event $event)
    {
        $data = [
            'id' => $event->id,
            'name' => $event->name,
            'description' => $event->description,
            'lowrank' => $event->lowestViewableRank,
            'absolutetime' => $event->start->getTimestamp(),
            'date' => $event->start->format('Y-m-d'),
            'time' => $event->start->format('H:i'),
            'offset' => 0,
            'region' => $event->region,
            'eventtype' => $event->type,
            'leader' => !is_null($event->leader) ? $event->leader->username : $event->user->username,
            'accord' => $event->accord,
        ];
        $form->setData($data);
    }
}
