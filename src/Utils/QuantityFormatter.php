<?php

namespace App\Utils;

/**
 * Utility class to format quantities.
 */
class QuantityFormatter
{
    /**
     * Format a computer value with the desired unit.
     * 
     * Available units:
     * 
     * |  unit  | description                 |
     * |--------|-----------------------------|
     * |  null  | the original value          |
     * |  B     | value in bytes (B)          |
     * |  K(B)  | value in kilobytes (KB)     |
     * |  M(B)  | value in megabytes (MB)     |
     * |  G(B)  | value in gigabytes (GB)     |
     * |  T(B)  | value in terabytes (TB)     |
     * |  auto  | value with the closest unit |
     * 
     * @param int|string  $value       Value to be formatted.
     * @param null|string $unit        See table above.
     * @param bool        $displayUnit Add the unit at the end of the value. Eg: '2MB'.
     * 
     * @return string Formatted value.
     */
    public static function formatQuantity(
        int|string $value,
        ?string $unit = null,
        bool $displayUnit = true,
    ): string {
        $originalValue = (int) substr($value, 0, -1);
        $originalUnit = (string) substr($value, -1, 1);

        // If no unit (= last character is digit), then unit = bytes (B)
        if ($originalUnit == ((int) $originalUnit)) {
            $originalValue = (int) $value;
            $originalUnit = 'B';
        }

        $exponentToBytes = match (strtoupper($originalUnit)) {
            'B' => 0,
            'K', 'KB' => 1,
            'M', 'MB' => 2,
            'G', 'GB' => 3,
            'T', 'TB' => 4,
        };

        $valueInBytes = $originalValue * pow(1024, $exponentToBytes);

        $exponentToDisplayedValue = match (strtoupper((string) $unit)) {
            'B' => 0,
            'K', 'KB' => 1,
            'M', 'MB' => 2,
            'G', 'GB' => 3,
            'T', 'TB' => 4,
            'AUTO' => (int) log($valueInBytes, 1024),
            default => 0,
        };

        $displayedValue = match (strtoupper((string) $unit)) {
            'B',
            'K', 'KB',
            'M', 'MB',
            'G', 'GB',
            'T', 'TB',
            'AUTO' => intval($valueInBytes / pow(1024, $exponentToDisplayedValue)),
            default => $value,
        };

        $displayedUnit = match ($exponentToDisplayedValue) {
            0 => 'B',
            1 => 'KB',
            2 => 'MB',
            3 => 'GB',
            4 => 'TB',
        };

        // $unit = null should return the original value, as-is
        $displayedUnit = is_null($unit) ? '' : $displayedUnit;

        return $displayedValue . ($displayUnit ? $displayedUnit : '');
    }
}
