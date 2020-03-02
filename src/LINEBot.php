<?php

namespace src\LINEBot;

use LINE\LINEBot\HTTPClient\CurlHTTPClient;

require_once(__DIR__ . "/vendor/autoload.php");

class LINEBot
{
    private static $access_token = "";
    private static $secret_token = "";

    public $bot;

    public function __construct(String $access_token, String $secret_token)
    {
        self::$access_token = $access_token;
        self::$secret_token = $secret_token;

        $httpClient = new CurlHTTPClient(self::$access_token);

        $this->bot = new \LINE\LINEBot($httpClient, [
            "channelSecret" => self::$secret_token
        ]);
    }
}
