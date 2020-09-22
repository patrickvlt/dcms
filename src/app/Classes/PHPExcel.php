<?php

namespace App\Classes\DCMS;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PHPExcel
{
    /**
     * Generate and download an Excel sheet
     * @param array $headers
     * @param array $data
     * @param string $fileName
     * @return void
     * @throws Exception
     */

    public static function download(array $headers = [], array $data = [], $fileName = 'data.xlsx')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Loop through headers array
        for ($h = 0; $h < sizeof($headers); $h++) {
            $sheetColumn = $h + 1;
            $sheetRow = 1;
            $sheet->setCellValueByColumnAndRow($sheetColumn, $sheetRow, $headers[$h]);
        }

        // Loop through data array
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
        // Clean file to prevent encoding error
        ob_end_clean();
        // Set the content type and attachment for php://output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
    }

}
