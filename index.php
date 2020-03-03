<?php

use src\flex\FlexBubble;
use src\flex\FlexCarousel;
use src\CSV;
use src\LINEBot;
use src\RAW;

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

        var_dump($diffs);
    }
}
