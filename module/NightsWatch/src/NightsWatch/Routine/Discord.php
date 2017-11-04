<?php

namespace NightsWatch\Routine;

use NightsWatch\DiscordProvider;
use NightsWatch\Entity\User;

abstract class Discord
{
    use RoutineEvents;

    const GUILD_ID = '222528908988383233';

    const ROLE_COUNCIL = '222800578877718529';
    const ROLE_GENERALPLUS = '291742086305284097';
    const ROLE_GENERAL = '291742117951176704';
    const ROLE_LIEUTENANTPLUS = '291742020668489728';
    const ROLE_LIEUTENANT = '291742060204261386';
    const ROLE_CAPTAINPLUS = '291741978545356801';
    const ROLE_CAPTAIN = '222800614592217088';
    const ROLE_CORPORALPLUS = '376380535435558913';
    const ROLE_CORPORAL = '376380428464029696';
    const ROLE_PRIVATEPLUS = '291741921716731904';
    const ROLE_PRIVATE = '222800638155685888';
    const ROLE_RECRUITPLUS = '291741842817417228';
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
            case ($rank >= User::RANK_GENERAL):
                $roles = [
                    self::ROLE_COUNCIL,
                    self::ROLE_GENERAL,
                    self::ROLE_GENERALPLUS,
                    self::ROLE_LIEUTENANTPLUS,
                    self::ROLE_CAPTAINPLUS,
                    self::ROLE_CORPORALPLUS,
                    self::ROLE_PRIVATEPLUS,
                    self::ROLE_RECRUITPLUS,
                    self::ROLE_VERIFIED,
                ];
                break;

            case ($rank >= User::RANK_LIEUTENANT):
                $roles = [
                    self::ROLE_COUNCIL,
                    self::ROLE_LIEUTENANT,
                    self::ROLE_LIEUTENANTPLUS,
                    self::ROLE_CAPTAINPLUS,
                    self::ROLE_CORPORALPLUS,
                    self::ROLE_PRIVATEPLUS,
                    self::ROLE_RECRUITPLUS,
                    self::ROLE_VERIFIED,
                ];
                break;

            case ($rank >= User::RANK_CAPTAIN):
                $roles = [
                    self::ROLE_CAPTAIN,
                    self::ROLE_CAPTAINPLUS,
                    self::ROLE_CORPORALPLUS,
                    self::ROLE_PRIVATEPLUS,
                    self::ROLE_RECRUITPLUS,
                    self::ROLE_VERIFIED,
                ];
                break;

            case ($rank >= User::RANK_CORPORAL):
                $roles = [
                    self::ROLE_CORPORAL,
                    self::ROLE_CORPORALPLUS,
                    self::ROLE_PRIVATEPLUS,
                    self::ROLE_RECRUITPLUS,
                    self::ROLE_VERIFIED,
                ];
                break;

            case ($rank >= User::RANK_PRIVATE):
                $roles = [
                    self::ROLE_PRIVATE,
                    self::ROLE_PRIVATEPLUS,
                    self::ROLE_RECRUITPLUS,
                    self::ROLE_VERIFIED,
                ];
                break;

            case ($rank >= User::RANK_RECRUIT):
                $roles = [
                    self::ROLE_RECRUIT,
                    self::ROLE_RECRUITPLUS,
                    self::ROLE_VERIFIED,
                ];
                break;

            case ($rank >= User::RANK_CIVILIAN):
                $roles = [
                    self::ROLE_VERIFIED,
                ];
                break;
        }

        return $roles;
    }
}
