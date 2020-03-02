<?php

class csv
{
    private const CSV_FOLDER = __DIR__ . "/csv";

    private const MAX_CSV = 5;
    private const MIN_CSV = 2;

    private const BODY_CSV = 2;

    private const LAST_ROW = 7;
    private const LAST_DIFF = 2;

    public const DIFF_TYPE_NEW = 0;
    public const DIFF_TYPE_UPDATE = 1;

    private $csv_url = "";

    public function __construct(String $csv_url)
    {
        $this->csv_url = $csv_url;
    }


    public function boot()
    {
        $this->get();
    }


    private function get()
    {
        if (!file_exists(self::CSV_FOLDER)) {
            mkdir(self::CSV_FOLDER);
        }

        $files = glob(self::CSV_FOLDER . "/*");
        $sorted_files = usort($files, "sort");

        if (count($sorted_files) >= self::MAX_CSV) {
            unlink($sorted_files[self::MAX_CSV - 1]);
        }

        $csv = file_get_contents($this->csv_url);
        $path = self::CSV_FOLDER . "/" . time() . ".csv";

        file_put_contents($path, $csv);
    }


    public function pickup(): array
    {
        $files = glob(self::CSV_FOLDER . "/*");
        $sorted_files = usort($files, "sort");

        if (count($sorted_files) <= self::MIN_CSV) {
            return [
                "result" => false,
                "csv" => null
            ];
        }

        $csv = new stdClass;

        $csv->new = $this->parseCsv($sorted_files[0]);
        $csv->old = $this->parseCsv($sorted_files[1]);

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

            $data = new stdClass();

            if (!empty($result)) {
                if (isset($result[self::LAST_ROW]) && isset($oldCsv[$i][self::LAST_ROW])) {
                    $results[$i] = $result;
                    if (strlen($result[self::LAST_ROW]) - strlen($result[$i][self::LAST_ROW]) === self::LAST_DIFF) {
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

        $empty = empty($results);

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


    private static function sort($a, $b)
    {
        return filemtime($b) - filemtime($a);
    }
}
