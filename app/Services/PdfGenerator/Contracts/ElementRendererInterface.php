<?php

declare(strict_types=1);

namespace App\Services\PdfGenerator\Contracts;

use setasign\Fpdi\Fpdi;

/**
 * Interface for all element rendering strategies.
 *
 * Ensures that every renderer has a `render` method with a consistent signature,
 * allowing the main service to treat them interchangeably.
 */
interface ElementRendererInterface
{
    /**
     * Renders a specific element onto the PDF page.
     *
     * @param Fpdi $pdf The FPDI instance used to manipulate the PDF.
     * @param array<string, mixed> $element The configuration for the element to render.
     * @param object|null $data The data object containing dynamic values (e.g., an Eloquent model).
     */
    public function render(Fpdi $pdf, array $element, ?object $data): void;
}
