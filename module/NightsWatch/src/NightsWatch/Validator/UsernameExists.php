<?php
namespace NightsWatch\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class UsernameExists extends AbstractValidator
{
    const INVALID = 'noUser';

    protected $messageTemplates
        = [
            self::INVALID => 'The user does not have a TNW account',
        ];

    private $userRepo;

    public function __construct($options = null)
    {
        parent::__construct($options);

        if ($options && is_array($options) && array_key_exists('entityManager', $options)) {
            if (!($options['entityManager'] instanceof \Doctrine\ORM\EntityManager)) {
                throw new \Exception('Bad Entity Manager Provided');
            }
            $this->userRepo = $options['entityManager']->getRepository('NightsWatch\Entity\User');
        }
    }

    public function isValid($value)
    {
        $user = $this->userRepo->findOneBy(['username' => $value]);
        if (is_null($user)) {
            $this->error(self::INVALID);
            return false;
        }

        return true;
    }
}
