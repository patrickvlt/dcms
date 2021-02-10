<?php

namespace Pveltrop\DCMS\Classes;

use Illuminate\Support\Facades\Storage;
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

    public static function store(array $headers = [], array $data = [], $fileName = 'data.xlsx')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Loop through headers array
        $headerCount = 0;
        foreach ($headers as $header => $visibleText) {
            $sheet->setCellValueByColumnAndRow($headerCount+1, 1, $visibleText);
            $headerCount++;
        }

        // Loop through data array
        for ($row = 0, $rowMax = count($data); $row < $rowMax; $row++) {
            $headerPos = 0;

            foreach ($headers as $headerKey => $headerVal) {
                // Start from row one, since first row is for headers
                $sheetRow = ($row + 1 + 1);
                $sheetColumn = $headerPos + 1;

                // If current header is a nested array column
                if (count(explode('.', $headerKey)) > 1) {
                    $dataEntry = '';
                    foreach (explode('.', $headerKey) as $header) {
                        $dataEntry .= "['".$header."']";
                    }
                    $dataEntry = "\$data[\$row]".$dataEntry;
                    $dataEntry = eval("return ".$dataEntry." ?? null;");
                } else {
                    // If header is a normal column
                    $dataEntry = $data[$row][$headerKey];
                }
                $sheet->setCellValueByColumnAndRow($sheetColumn, $sheetRow, $dataEntry);
                $headerPos++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        // Clean file to prevent encoding error
        ob_end_clean();
        // Set the content type and attachment for php://output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        Storage::disk('tmp')->put($fileName, $content);
    }
}
