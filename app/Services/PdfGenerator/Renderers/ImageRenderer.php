<?php

declare(strict_types=1);

namespace App\Services\PdfGenerator\Renderers;

use App\Services\PdfGenerator\Contracts\ElementRendererInterface;
use Illuminate\Support\Facades\File;
use setasign\Fpdi\Fpdi;

final readonly class ImageRenderer implements ElementRendererInterface
{
    public function render(Fpdi $pdf, array $element, ?object $data): void
    {
        $imagePath = public_path($element['value']);

        if (! File::exists($imagePath)) {
            // You could log an error here if needed
            return;
        }

        $width = $element['width'] ?? 0;
        $height = $element['height'] ?? 0;

        $pdf->Image($imagePath, $element['x'], $element['y'], $width, $height);
    }
}
