<?php

namespace App\Core;

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
     * | format | description                 |
     * |--------|-----------------------------|
     * |  null  | the original value          |
     * |   B    | value in bytes (B)          |
     * |   K    | value in kilobytes (KB)     |
     * |   M    | value in megabytes (MB)     |
     * |   G    | value in gigabytes (GB)     |
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
            'K' => 1,
            'M' => 2,
            'G' => 3,
        };

        $valueInBytes = $originalValue * pow(1024, $exponentToBytes);

        $exponentToDisplayedValue = match (strtoupper((string) $unit)) {
            'B' => 0,
            'K' => 1,
            'M' => 2,
            'G' => 3,
            'AUTO' => (int) log($valueInBytes, 1024),
            default => 0,
        };

        $displayedValue = match (strtoupper((string) $unit)) {
            null => $value,
            'B', 'K', 'M', 'G', 'AUTO' => round($valueInBytes / pow(1024, $exponentToDisplayedValue), 0, PHP_ROUND_HALF_DOWN),
            default => $value,
        };

        $displayedUnit = match ($exponentToDisplayedValue) {
            0 => 'B',
            1 => 'KB',
            2 => 'MB',
            3 => 'GB',
            4 => 'TB',
        };

        return $displayedValue . ($displayUnit ? $displayedUnit : '');
    }
}
