<?php

declare(strict_types=1);

namespace App\Services\PdfGenerator\Renderers;

use App\Services\PdfGenerator\Contracts\ElementRendererInterface;
use setasign\Fpdi\Fpdi;

final readonly class CheckboxRender implements ElementRendererInterface
{
    public function render(Fpdi $pdf, array $element, ?object $data): void
    {
        // 1. Get the key for the data from the element's 'value' (e.g., '{{gender}}' -> 'gender')
        $key = trim($element['value'] ?? '', '{} ');

        // If there's no key or no options, there's nothing to do.
        if (empty($key) || empty($element['options']) || ! is_array($element['options'])) {
            return;
        }

        // 2. Retrieve the actual value from the data source.
        $currentValue = data_get($data, $key, null);

        // We'll compare string values to avoid type issues.
        $currentValue = (string) $currentValue;

        // 3. Loop through the available options for this checkbox group.
        foreach ($element['options'] as $option) {
            // 4. Check if the option's value matches the data's value.
            if (isset($option['value']) && isset($option['x']) && isset($option['y']) && (string) $option['value'] === $currentValue) {
                // 5. If it matches, render the checkmark at the option's coordinates.

                // Set styles for the 'X' mark, using the element's main style properties.
                $fontStyle = '';
                if (($element['font_weight'] ?? 'normal') === 'bold') {
                    $fontStyle .= 'B';
                }
                if (($element['font_style'] ?? 'normal') === 'italic') {
                    $fontStyle .= 'I';
                }
                // Handle FPDI specific styles if provided directly
                if (isset($element['font_style']) && in_array($element['font_style'], ['B', 'I', 'BI', 'U', 'BIU'])) {
                    $fontStyle = $element['font_style'];
                }

                $pdf->SetFont(
                    $element['font_family'] ?? 'Helvetica',
                    $fontStyle,
                    $element['font_size'] ?? 10
                );

                if (isset($element['color']) && is_array($element['color']) && count($element['color']) === 3) {
                    $pdf->SetTextColor($element['color'][0], $element['color'][1], $element['color'][2]);
                } else {
                    $pdf->SetTextColor(0, 0, 0); // Default to black
                }
                // 'X' as mark.
                $x = (float) $option['x'];
                $y = (float) $option['y'];
                $pdf->SetXY($x, $y);
                $pdf->Text($x, $y, 'X');
                break;
            }
        }
    }
}
