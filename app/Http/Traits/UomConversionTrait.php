<?php

namespace App\Http\Traits;

trait UomConversionTrait {
    public $uomMapping = [
        // --- CASE MAPPINGS ---
        'CAS'    => 'CS',
        'CASE'   => 'CS',
        'CASES'  => 'CS',
        'BX'     => 'CS', // Often handled as a case
        'BOX'    => 'CS',
        'CTN'    => 'CS',
        'CARTON' => 'CS',

        // --- PIECE MAPPINGS ---
        'PC'     => 'PCS',
        'PIECE'  => 'PCS',
        'PIECES' => 'PCS',
        'EA'     => 'PCS',
        'EACH'   => 'PCS',
        'UNIT'   => 'PCS',

        // --- PACK MAPPINGS ---
        'PAC'     => 'PCK',
        'PACK'    => 'PCK',
        'PACKAGE' => 'PCK',
        'PKG'     => 'PCK',
        'PK'      => 'PCK',
        'PKGS'    => 'PCK',

        // --- INNER BOX MAPPINGS
        'IB' => 'IN',
        'INBOX' => 'IN',
        'INNER' => 'IN',
        'IN-BOX' => 'IN',
        'INNER-BOX' => 'IN',
    ];

    public function convertUom($product, $uom, $quantity, $targetUom = 'CS')
    {
        if (empty($product) || empty($quantity)) {
            return 0;
        }

        // 1. Normalize Codes
        $sourceCode = $this->normalizeUom($uom);
        $targetCode = $this->normalizeUom($targetUom);
        $baseCode   = $this->normalizeUom($product->stock_uom);

        // Optimization: If source matches target, return immediately
        if ($sourceCode === $targetCode) {
            return (float) $quantity;
        }

        // 2. Convert Source -> Base (Stock UOM)
        $qtyInBase = $this->toBase($product, $sourceCode, $quantity);

        // 3. Convert Base -> Target
        return $this->fromBase($product, $targetCode, $qtyInBase);
    }

    /**
     * Wrapper specifically for CS (Legacy support).
     */
    public function csConversion($product, $uom, $quantity)
    {
        return $this->convertUom($product, $uom, $quantity, 'CS');
    }

    /**
     * Wrapper specifically for PCS (Legacy support).
     */
    public function pcsConversion($product, $uom, $quantity)
    {
        return $this->convertUom($product, $uom, $quantity, 'PCS');
    }

    /**
     * Normalize UOM strings using the mapping array.
     */
    private function normalizeUom($uom)
    {
        $uom = trim(strtoupper($uom));
        return $this->uomMapping[$uom] ?? $uom;
    }

    /**
     * Convert a quantity FROM a specific UOM TO the Product's Base UOM.
     */
    private function toBase($product, $uomCode, $quantity)
    {
        // If the input is already the base (Stock UOM), no math needed.
        if ($uomCode === $this->normalizeUom($product->stock_uom)) {
            return $quantity;
        }

        $factor = $this->getConversionFactor($product, $uomCode);

        // If 1 Case = 12 PCS (Factor 12), then 2 Cases = 24 PCS (Multiply)
        return $quantity * $factor;
    }

    /**
     * Convert a quantity FROM the Product's Base UOM TO a target UOM.
     */
    private function fromBase($product, $uomCode, $quantityInBase)
    {
        if ($uomCode === $this->normalizeUom($product->stock_uom)) {
            return $quantityInBase;
        }

        $factor = $this->getConversionFactor($product, $uomCode);

        // If 1 Case = 12 PCS (Factor 12), then 24 PCS = 2 Cases (Divide)
        return ($factor != 0) ? $quantityInBase / $factor : 0;
    }

    /**
     * Calculate the "Factor to Base" for a given UOM.
     * Returns how many Base Units make up 1 of this UOM.
     */
    private function getConversionFactor($product, $uomCode)
    {
        // Determine which set of fields to look at
        if ($uomCode === $this->normalizeUom($product->order_uom)) {
            $conv = $product->order_uom_conversion;
            $oper = $product->order_uom_operator;
        } elseif ($uomCode === $this->normalizeUom($product->other_uom)) {
            $conv = $product->other_uom_conversion;
            $oper = $product->other_uom_operator;
        } else {
            // Unknown UOM or no conversion defined -> treat as 1:1 or throw error
            return 1;
        }

        // Normalize the factor relative to Base (Multiply logic)
        // If Operator is 'D' (Divide), it means 1 Unit = Base / Conversion
        // Example: If 1 Pack is 1/2 a Case, Factor is 0.5.
        if ($oper === 'D') {
            return ($conv != 0) ? (1 / $conv) : 0;
        }

        // If Operator is 'M' (Multiply), it means 1 Unit = Base * Conversion
        return $conv;
    }
}
