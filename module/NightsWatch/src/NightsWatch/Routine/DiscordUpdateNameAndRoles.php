<?php
namespace NightsWatch\Routine;

use NightsWatch\DiscordProvider;

class DiscordUpdateNameAndRoles extends Discord
{
    public function perform()
    {
        $client = $this->userProvider->getHttpClient();

        $url = DiscordProvider::URI_PREFIX.'guilds/'.self::GUILD_ID.'/members/'.$this->discordId;
        $roles = $this->getRolesForRank($this->user->rank);
        $client->patch(
            $url,
            [
                'json'    => [
                    'nick'  => $this->user->username,
                    'roles' => $roles,
                ],
                'headers' => [
                    'Authorization' => 'Bot '.$this->botAccessToken,
                ]
            ]
        );
    }
}
