<?php

declare(strict_types=1);

namespace App\Services\PdfGenerator\Renderers;

use App\Services\PdfGenerator\Contracts\ElementRendererInterface;
use setasign\Fpdi\Fpdi;

final readonly class DynamicTagRenderer implements ElementRendererInterface
{
    public function render(Fpdi $pdf, array $element, ?object $data): void
    {
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

        if (isset($element['color'])) {
            // Convert RGB array to individual components
            $pdf->SetTextColor($element['color'][0], $element['color'][1], $element['color'][2]);
        } else {
            $pdf->SetTextColor(0, 0, 0);
        }

        // Extract property path from {{...}} placeholder
        $key = trim($element['value'], '{} ');

        // Retrieve the value from the data object, default to an empty string
        $value = data_get($data, $key, '');

        if (empty($value)) {
            $value = $element['value'];
        }
        $pdf->SetXY($element['x'], $element['y']);
        $pdf->Write(0, iconv('UTF-8', 'windows-1252', (string) $value));
    }
}
