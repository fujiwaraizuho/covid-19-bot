<?php

use src\CSV;
use src\LINEBot;

require_once(__DIR__ . "/vendor/autoload.php");

define("LINE_CHANNEL_SECRET", "");
define("LINE_CHANNEL_TOKEN", "");

boot();

function boot()
{
    $csv = new CSV();
    $csv->get();

    $csvs = $csv->pickup();

    if ($csvs["result"]) {
        $diffs = $csv->diff($csvs["csv"]->new, $csvs["csv"]->old);
        if ($diffs["result"]) {
            $bot = new LINEBot(LINE_CHANNEL_TOKEN, LINE_CHANNEL_SECRET);
            $bot->broadcast($diffs["diff"]);
        }
    }
}
