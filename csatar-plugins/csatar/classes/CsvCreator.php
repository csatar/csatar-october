<?php

namespace Csatar\Csatar\Classes;

class CsvCreator
{

    public static function writeCsvFile($fileName, $data, $append=false): ?string
    {
        if (!is_array($data)) {
            return 'Input data must be a 2 dimensional array';
        }

        if (($file = fopen($fileName, $append ? 'a' : 'w')) === false) {
            return print_r(error_get_last(), true);
        }

        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach ($data as $fields) {
            if (!is_array($fields)) {
                continue;
            }

            fputcsv($file, $fields);
        }

        fclose($file);
        return null;
    }

}
