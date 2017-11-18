<?php

namespace NightsWatch\Routine;

use GuzzleHttp\Client;
use NightsWatch\DiscordProvider;

class DiscordMessage
{
    use RoutineEvents;

    private $webhook;
    private $client;

    /**
     * DiscordMessage constructor.
     * @param string $webhook
     */
    public function __construct($webhook)
    {
        $this->webhook = $webhook;
        $this->client = new Client();
    }

    /**
     * @param array $json
     */
    public function perform($json, $wait = false)
    {
        $webhook = $wait ? $this->webhook . '?wait=true' : $this->webhook;
        $this->client->post(
            $webhook,
            [
                'json' => $json,
            ]
        );
    }
}