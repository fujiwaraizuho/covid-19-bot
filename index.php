<?php

use src\flex\FlexBubble;
use src\flex\FlexCarousel;
use src\CSV;
use src\LINEBot;

require_once(__DIR__ . "/vendor/autoload.php");

define("LINE_CHANNEL_SECRET", "");
define("LINE_CHANNEL_TOKEN", "");

define("CSV_URL", "https://toyokeizai.net/sp/visual/tko/covid19/csv/data.csv");

boot();

function boot()
{
    $csv = new CSV(CSV_URL);

    $csvs = $csv->pickup();

    $diffs = $csv->diff($csvs["csv"]->new, $csvs["csv"]->old);

    var_dump($diffs);
}