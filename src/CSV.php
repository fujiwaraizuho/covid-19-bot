<?php

namespace src;

class CSV
{
    private const MAX_CSV = 5;
    private const MIN_CSV = 2;

    private const FIRST_RECORD = 0;

    public const DIFF_TYPE_NEW = 0;
    public const DIFF_TYPE_UPDATE = 1;

    private static $csv_folder = "";

    public function __construct()
    {
        self::$csv_folder = dirname(__DIR__) . "/csv/";
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

        $path = self::$csv_folder . time() . ".csv";

        $scraping = new Scraping($path);
        $scraping->get();
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

        $csv->new = $this->csv_to_array($files[0]);
        $csv->old = $this->csv_to_array($files[1]);

        return [
            "result" => true,
            "csv" => $csv
        ];
    }


    public function diff(array $newCsv, array $oldCsv): array
    {
        $diffs = array_diff(
            $this->array_to_csv($newCsv),
            $this->array_to_csv($oldCsv)
        );

        $results = [];

        foreach ($diffs as $diff) {
            $data = new \stdClass;

            $diff = explode(",", $diff);
            $is_update = in_array($diff[self::FIRST_RECORD], array_column($oldCsv, self::FIRST_RECORD),  true);

            if ($is_update) {
                $oldRow = array_search($diff[self::FIRST_RECORD], array_column($oldCsv, self::FIRST_RECORD), true);
                $updateDiff = array_diff($diff, $oldCsv[$oldRow]);

                $data->bold = array_keys($updateDiff);
            }

            $data->type = $is_update ? self::DIFF_TYPE_UPDATE : self::DIFF_TYPE_NEW;
            $data->diff = $diff;

            $results[] = $data;
        }

        $empty = empty($results);

        return [
            "result" => !$empty,
            "diff" => $empty ? null : $results
        ];
    }


    private function csv_to_array(String $path)
    {
        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_AHEAD);
        $records = [];
        foreach ($file as $i => $row) {
            if ($i === 0) {
                foreach ($row as $j => $col) $colbook[$j] = $col;
                continue;
            }
            $line = [];
            foreach ($colbook as $j => $col) $line[] = @$row[$j];
            $records[] = $line;
        }

        return $records;
    }


    private function array_to_csv(array $csv)
    {
        foreach ($csv as $value) {
            $data[] = implode(",", $value);
        }

        return $data;
    }
}
