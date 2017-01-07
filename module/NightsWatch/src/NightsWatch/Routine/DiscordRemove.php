<?php
namespace NightsWatch\Routine;

use NightsWatch\DiscordProvider;

class DiscordRemove extends Discord
{
    public function perform()
    {
        $client = $this->userProvider->getHttpClient();

        $url = DiscordProvider::URI_PREFIX.'guilds/'.self::GUILD_ID.'/members/'.$this->discordId;

        $client->delete($url, ['headers' => ['Authorization' => 'Bot '.$this->botAccessToken]]);
    }
}
