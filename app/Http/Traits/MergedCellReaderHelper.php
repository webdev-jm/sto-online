<?php

namespace App\Http\Traits;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait MergedCellReaderHelper {

    public function readMergedCells(Worksheet $worksheet): array {
        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $cellValue = $this->getMergedCellValue($worksheet, $cell->getCoordinate());
                $rowData[] = $cellValue;
            }
            $data[] = $rowData;
        }
        return $data;
    }
    
    private function getMergedCellValue(Worksheet $worksheet, string $cellCoordinate) {
        $mergedRanges = $worksheet->getMergeCells();
    
        foreach ($mergedRanges as $mergedRange) {
            [$startCell, $endCell] = explode(':', $mergedRange);
            if ($this->isCellInRange($cellCoordinate, $startCell, $endCell)) {
                return $worksheet->getCell($startCell)->getValue();
            }
        }
    
        return $worksheet->getCell($cellCoordinate)->getValue();
    }
    
    private function isCellInRange(string $cellCoordinate, string $startCell, string $endCell): bool {
        $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($startCell);
        $end = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($endCell);
        $current = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($cellCoordinate);
    
        [$startCol, $startRow] = $start;
        [$endCol, $endRow] = $end;
        [$currentCol, $currentRow] = $current;
    
        return ($currentCol >= $startCol && $currentCol <= $endCol && $currentRow >= $startRow && $currentRow <= $endRow);
    }
}