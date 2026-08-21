<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpreadsheetExportService
{
    public function download(string $title, array $headers, iterable $rows, string $filename): StreamedResponse
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle(substr(str_replace(['\\', '/', '?', '*', '[', ']', ':'], '', $title), 0, 31));
        $sheet->setCellValue('A1', $title);
        $sheet->setCellValue('A2', 'Generated: '.now()->format('Y-m-d H:i:s'));

        foreach (array_values($headers) as $column => $header) {
            $sheet->setCellValue([$column + 1, 4], $header);
        }

        $rowNumber = 5;
        foreach ($rows as $row) {
            foreach (array_values($row) as $column => $value) {
                if (is_int($value) || is_float($value)) {
                    $sheet->setCellValue([$column + 1, $rowNumber], $value);
                } else {
                    $sheet->setCellValueExplicit([$column + 1, $rowNumber], (string) ($value ?? ''), DataType::TYPE_STRING);
                }
            }
            $rowNumber++;
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A4:'.$sheet->getHighestColumn().'4')->getFont()->setBold(true);
        foreach (range('A', $sheet->getHighestColumn()) as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
        $sheet->freezePane('A5');

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
            $book->disconnectWorksheets();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
