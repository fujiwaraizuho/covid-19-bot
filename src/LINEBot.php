<?php

namespace src;

use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use src\flex\FlexBubble;
use src\flex\FlexCarousel;

require_once(dirname(__DIR__) . "/vendor/autoload.php");

class LINEBot
{

    private static $access_token = "";
    private static $secret_token = "";

    public $bot;

    public const BASELINE_DATE = 1;
    public const BASELINE_OLD = 2;
    public const BASELINE_GENDER = 3;
    public const BASELINE_RECIDENCE = 4;
    public const BASELINE_CLOSE_CONTACT = 5;
    public const BASELINE_CLOSE_CONTACT_STATUS = 6;

    private static $altMessage = [
        CSV::DIFF_TYPE_NEW => "新しい感染者が発生しました！",
        CSV::DIFF_TYPE_UPDATE => "感染者の情報が更新されました！",
        "感染者情報が追加、もしくは更新されました！"
    ];

    private static $type = [
        CSV::DIFF_TYPE_NEW => "NEW!!",
        CSV::DIFF_TYPE_UPDATE => "UPDATE!!"
    ];

    private static $title = "感染者情報";

    private static $message = [
        CSV::DIFF_TYPE_NEW => "新しい感染者が発生しました。",
        CSV::DIFF_TYPE_UPDATE => "感染者の情報が更新されました。"
    ];

    public function __construct(String $access_token, String $secret_token)
    {
        self::$access_token = $access_token;
        self::$secret_token = $secret_token;

        $httpClient = new CurlHTTPClient(self::$access_token);

        $this->bot = new \LINE\LINEBot($httpClient, [
            "channelSecret" => self::$secret_token
        ]);
    }


    public function broadcast(array $diffs)
    {
        if (count($diffs) === 1) {
            $data = new \stdClass;

            $diffs = $diffs[0];
            $diff = $diffs->diff;

            $data->type = self::$type[$diffs->type];
            $data->title = self::$title;
            $data->message = self::$message[$diffs->type];

            $data->baseline = [];

            for ($i = 1; $i <= self::BASELINE_CLOSE_CONTACT_STATUS; $i++) {
                $data->baseline[$i] = new \stdClass;
                $data->baseline[$i]->bold = false;
            }

            $data->baseline[self::BASELINE_DATE]->text = $diff[1];
            $data->baseline[self::BASELINE_OLD]->text = $diff[2];
            $data->baseline[self::BASELINE_GENDER]->text = $diff[3];
            $data->baseline[self::BASELINE_RECIDENCE]->text = $diff[4];
            $data->baseline[self::BASELINE_CLOSE_CONTACT]->text = $diff[5];
            $data->baseline[self::BASELINE_CLOSE_CONTACT_STATUS]->text = $diff[6];

            if (isset($diffs->bold)) {
                foreach ($diffs->bold as $bold) {
                    $data->baseline[$bold]->bold = true;
                }
            }

            $message = new FlexBubble(self::$altMessage[$diffs->type], $data);

            $message->get();
        } else {
            $datas = [];

            foreach ($diffs as $diff) {
                $data = new \stdClass;

                $data->type = self::$type[$diff->type];
                $data->title = self::$title;
                $data->message = self::$message[$diff->type];

                $data->baseline = [];

                for ($i = 1; $i <= self::BASELINE_CLOSE_CONTACT_STATUS; $i++) {
                    $data->baseline[$i] = new \stdClass;
                    $data->baseline[$i]->bold = false;
                }

                if (isset($diff->bold)) {
                    foreach ($diff->bold as $bold) {
                        $data->baseline[$bold]->bold = true;
                    }
                }


                $diff = $diff->diff;

                $data->baseline[self::BASELINE_DATE]->text = $diff[1];
                $data->baseline[self::BASELINE_OLD]->text = $diff[2];
                $data->baseline[self::BASELINE_GENDER]->text = $diff[3];
                $data->baseline[self::BASELINE_RECIDENCE]->text = $diff[4];
                $data->baseline[self::BASELINE_CLOSE_CONTACT]->text = $diff[5];
                $data->baseline[self::BASELINE_CLOSE_CONTACT_STATUS]->text = $diff[6];

                $datas[] = $data;
            }

            $message = new FlexCarousel(self::$altMessage[2], $datas);
        }

        $this->bot->broadcast($message->get());
    }
}
