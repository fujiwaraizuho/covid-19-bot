<?php

namespace src;

use phpQuery;

class Scraping
{
    private const SOURCE_URL = "https://www.mhlw.go.jp/stf/newpage_09889.html";

    private static $path = "";

    public function __construct(String $path)
    {
        self::$path = $path;
    }


    public function get()
    {
        $html = file_get_contents(self::SOURCE_URL);

        $dom = phpQuery::newDocumentHTML($html);

        $datas = [];

        foreach ($dom["table:eq(2)"]->find("tbody")->find("tr") as $row) {
            $new_number = pq($row)->find("td:eq(0)")->text();
            $date = pq($row)->find("td:eq(2)")->text();
            $old = pq($row)->find("td:eq(3)")->text();
            $danger = pq($row)->find("td:eq(4)")->text();

            $from = str_replace("\r\n", "", pq($row)->find("td:eq(5)")->text());
            $contact = explode("\n", pq($row)->find("td:eq(7)")->text());

            $contact[0] = trim($contact[0]);

            if (isset($contact[1])) {
                $contact[1] = trim($contact[1]);
            } else {
                $contact[1] = "なし";
            }

            $datas[] = [
                "new_number" => $new_number,
                "date" => $date,
                "old" => $old,
                "danger" => $danger,
                "from" => preg_replace("/(				)/", "", $from),
                "close_contact" => $contact[0],
                "close_contact_status" => $contact[1]
            ];
        }

        $csv = fopen(self::$path, "w");

        foreach ($datas as $values) {
            fputcsv($csv, $values);
        }
    }
}
