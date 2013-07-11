<?php

namespace NightsWatch\Validator;

use Zend\Validator\AbstractValidator;

class MinecraftUsername extends AbstractValidator
{
    const INVALID = 'basicMember';

    protected $messageTemplates = [
        self::INVALID => 'The Minecraft Username has not paid for their account',
    ];

    public function isValid($value)
    {
        $url = "https://minecraft.net/haspaid.jsp?user=" . urlencode($value);

        $result = strtolower(trim(file_get_contents($url)));

        if ($result == 'true') {
            return true;
        } else {
            $this->error(self::INVALID);
            return false;
        }
    }
}
