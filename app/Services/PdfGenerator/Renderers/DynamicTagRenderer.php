<?php

declare(strict_types=1);

namespace App\Services\PdfGenerator\Renderers;

use App\Services\PdfGenerator\Contracts\ElementRendererInterface;
use setasign\Fpdi\Fpdi;

final readonly class DynamicTagRenderer implements ElementRendererInterface
{
    public function render(Fpdi $pdf, array $element, ?object $data): void
    {
        $pdf->SetFont(
            $element['font_family'] ?? 'Helvetica',
            $element['font_style'] ?? '',
            $element['font_size'] ?? 10
        );

        if (isset($element['color'])) {
            $pdf->SetTextColor(...$element['color']);
        } else {
            $pdf->SetTextColor(0, 0, 0);
        }

        // Extract property path from {{...}} placeholder
        $key = trim($element['value'], '{} ');

        // Retrieve the value from the data object, default to an empty string
        $value = data_get($data, $key, '');

        $pdf->SetXY($element['x'], $element['y']);
        $pdf->Write(0, iconv('UTF-8', 'windows-1252', (string) $value));
    }
}
