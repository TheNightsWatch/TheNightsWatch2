<?php
namespace NightsWatch\Routine;

use NightsWatch\DiscordProvider;
use NightsWatch\Entity\User;

class DiscordWire extends Discord
{
    protected $userAccessToken;

    public function __construct(User $user, $discordId, DiscordProvider $userProvider, $userAccessToken, $botAccessToken)
    {
        $this->userAccessToken = $userAccessToken;
        parent::__construct($user, $discordId, $userProvider, $botAccessToken);
    }

    public function perform()
    {
        $this->doBefore();
        // Step 1: Attach to User
        $this->user->discordId = $this->discordId;

        // Step 2: Invite to Guild
        $client = $this->userProvider->getHttpClient();

        $url = DiscordProvider::URI_PREFIX.'guilds/'.self::GUILD_ID.'/members/'.$this->discordId;

        $roles = $this->getRolesForRank($this->user->rank);
        $res = $client->put(
            $url,
            [
                'json'    => [
                    'access_token' => $this->userAccessToken->accessToken,
                    'nick'         => $this->user->username,
                    'roles'        => $roles
                ],
                'headers' => [
                    'Authorization' => 'Bot '.$this->botAccessToken,
                ]
            ]
        );
        if ($res->getStatusCode() == 204) {
            $routine = new DiscordUpdateNameAndRoles($this->user, $this->discordId, $this->userProvider, $this->botAccessToken);
            $routine->perform();
        }

        $this->doAfter();
    }
}
