<?php

namespace src;

class CSV
{
    private const MAX_CSV = 5;
    private const MIN_CSV = 2;

    private const BODY_CSV = 2;

    private const LAST_ROW = 7;
    private const LAST_DIFF = 2;

    public const DIFF_TYPE_NEW = 0;
    public const DIFF_TYPE_UPDATE = 1;

    private static $csv_folder = "";
    private static $csv_url = "";

    public function __construct(String $csv_url)
    {
        self::$csv_folder = dirname(__DIR__) . "/csv/";
        self::$csv_url = $csv_url;
    }


    public function get()
    {
        if (!file_exists(self::$csv_folder)) {
            mkdir(self::$csv_folder);
        }

        $files = glob(self::$csv_folder . "*");

        rsort($files);

        if (count($files) >= self::MAX_CSV) {
            unlink($files[self::MAX_CSV - 1]);
        }

        $csv = file_get_contents(self::$csv_url);
        $path = self::$csv_folder . time() . ".csv";

        file_put_contents($path, $csv);
    }


    public function pickup(): array
    {
        $files = glob(self::$csv_folder . "*");

        rsort($files);

        if (count($files) <= self::MIN_CSV) {
            return [
                "result" => false,
                "csv" => null
            ];
        }

        $csv = new \stdClass;

        $csv->new = $this->parseCsv($files[0]);
        $csv->old = $this->parseCsv($files[1]);

        return [
            "result" => true,
            "csv" => $csv
        ];
    }


    public function diff(array $newCsv, array $oldCsv): array
    {
        if (count($newCsv) !== count($oldCsv)) {
            $diff = count($newCsv) - count($oldCsv);

            for ($i = 0; $i < $diff; $i++) {
                $oldCsv[] = [];
            }
        }

        $newCsvCount = count($newCsv);

        $results = [];

        for ($i = 0; $i < $newCsvCount; $i++) {
            $result = array_diff($newCsv[$i], $oldCsv[$i]);

            $data = new \stdClass();

            if ($i === 211) {
                var_dump($newCsv[211], $oldCsv[211]);
            }

            if (!empty($result)) {
                if (isset($result[self::LAST_ROW]) && isset($oldCsv[$i][self::LAST_ROW])) {
                    $results[$i] = $result;
                    if (strlen($result[self::LAST_ROW]) - strlen($oldCsv[$i][self::LAST_ROW]) === self::LAST_DIFF) {
                        unset($results[$i][self::LAST_ROW]);
                    }

                    if (!empty($results[$i])) {
                        $data->type = self::DIFF_TYPE_UPDATE;
                        $data->diff = $result;

                        $results[$i] = $data;
                    } else {
                        unset($results[$i]);
                    }
                } else {
                    $data->type = self::DIFF_TYPE_NEW;
                    $data->diff = $result;

                    $results[$i] = $data;
                }
            }
        }

        if ($empty = empty($results)) {
            foreach ($results as $key => $result) {
                if (isset($result[self::LAST_ROW])) continue;

                $encode = explode("、", $result[self::LAST_ROW]);
                $encode[0] = trim($encode[0]);

                if (!isset($encode[1])) {
                    $encode[1] = "なし";
                }

                $results[$key][self::LAST_ROW - 1] = $encode[0];
                $results[$key][self::LAST_ROW] = $encode[1];
            }
        }

        return [
            "result" => !$empty,
            "diff" => $empty ? null : $results
        ];
    }


    private function parseCsv(String $path): array
    {
        $csv = file($path);
        $body = array_splice($csv, self::BODY_CSV);

        $datas = [];

        foreach ($body as $row) {
            $datas[] = explode(",", $row);
        }

        return $datas;
    }
}
