<?php

namespace NightsWatch\Authentication;

use Doctrine\ORM\EntityManager;
use NightsWatch\Routine\DiscordUpdateNameAndRoles;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class MinecraftIdAdapter implements AdapterInterface
{
    /** @var EntityManager */
    private $entityManager;
    private $minecraftId;
    private $username;

    public function __construct($minecraftId, $username, EntityManager $entityManager)
    {
        $this->minecraftId = $minecraftId;
        $this->username = $username;
        $this->entityManager = $entityManager;
    }

    public function authenticate()
    {
        /** @var \NightsWatch\Entity\User $user */
        $user = $this->entityManager
            ->getRepository('NightsWatch\Entity\User')
            ->findOneBy(['minecraftId' => $this->minecraftId]);

        if (is_null($user)) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                [],
                ['No Such User']
            );
        } elseif ($user->banned) {
            $this->updateName($user);

            return new Result(
                -5,
                [],
                ['Account Banned']
            );
        } else {
            $this->updateName($user);

            return new Result(
                Result::SUCCESS,
                $user->id,
                []
            );
        }
    }

    private function updateName($user)
    {
        $user->username = $this->username;
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }
}
