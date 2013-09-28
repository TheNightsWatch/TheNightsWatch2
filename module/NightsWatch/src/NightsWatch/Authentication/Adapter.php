<?php

namespace NightsWatch\Authentication;

use Doctrine\ORM\EntityManager;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;

class Adapter implements AdapterInterface
{
    /** @var EntityManager */
    private $entityManager;
    private $username;
    private $password;

    public function __construct($username, $password, EntityManager $entityManager)
    {
        $this->username = $username;
        $this->password = $password;
        $this->entityManager = $entityManager;
    }


    public function authenticate()
    {
        /** @var \NightsWatch\Entity\User $user */
        $user = $this->entityManager
            ->getRepository('NightsWatch\Entity\User')
            ->findOneBy(['username' => $this->username]);
        $bcrypt = new Bcrypt();

        if (is_null($user)) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                [],
                ['No Such User']
            );
        } elseif (!$bcrypt->verify($this->password, $user->password)) {
            return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                [],
                ['Invalid Password']
            );
        } elseif ($user->banned) {
            return new Result(
                -5,
                [],
                ['Account Banned']
            );
        } else {
            return new Result(
                Result::SUCCESS,
                $user->id,
                []
            );
        }
    }
}
