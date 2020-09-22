<?php

namespace App\Support\DCMS;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PHPExcel
{
    public static function createExcel(array $data, array $headers = [], $fileName = 'data.xlsx')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        for ($h = 0; $h < sizeof($headers); $h++) {
            $sheetColumn = $h + 1;
            $sheetRow = 1;
            $sheet->setCellValueByColumnAndRow($sheetColumn, $sheetRow, $headers[$h]);
        }

        for ($r = 0; $r < sizeof($data); $r++) {
            $j = 0;
            foreach ($data[$r] as $key => $value) {
                // Get row and column from for loop
                $sheetColumn = $j + 1;
                $sheetRow = ($r + 1 + 1);
                // Set cell value
                $sheet->setCellValueByColumnAndRow($sheetColumn, $sheetRow, $value);
                $j++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
    }

}
