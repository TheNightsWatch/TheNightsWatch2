<?php
namespace NightsWatch\Routine;

use NightsWatch\DiscordProvider;
use NightsWatch\Entity\User;

abstract class Discord
{
    use RoutineEvents;

    const GUILD_ID = '222528908988383233';

    const ROLE_COUNCIL = '222800578877718529';
    const ROLE_CORPORAL = '222800614592217088';
    const ROLE_PRIVATE = '222800638155685888';
    const ROLE_RECRUIT = '222800668224651276';
    const ROLE_VERIFIED = '237769649251549184'; // Typical Civilian

    protected $user;
    protected $discordId;
    protected $userProvider;
    protected $botAccessToken;

    public function __construct(
        User $user,
        $discordId,
        DiscordProvider $userProvider,
        $botAccessToken
    ) {
        $this->user = $user;
        $this->discordId = $discordId;
        $this->userProvider = $userProvider;
        $this->botAccessToken = $botAccessToken;
    }

    protected function getRolesForRank($rank)
    {
        $roles = [];
        switch (true) {
            case ($rank >= User::RANK_LIEUTENANT):
                $roles = [self::ROLE_COUNCIL, self::ROLE_VERIFIED];
                break;

            case ($rank >= User::RANK_CORPORAL):
                $roles = [self::ROLE_CORPORAL, self::ROLE_VERIFIED];
                break;

            case ($rank >= User::RANK_PRIVATE):
                $roles = [self::ROLE_PRIVATE, self::ROLE_VERIFIED];
                break;

            case ($rank >= User::RANK_RECRUIT):
                $roles = [self::ROLE_RECRUIT, self::ROLE_VERIFIED];
                break;

            case ($rank >= User::RANK_CIVILIAN):
                $roles = [self::ROLE_VERIFIED];
                break;
        }

        return $roles;
    }
}
