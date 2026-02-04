<?php

declare(strict_types=1);

namespace App\Services\PdfGenerator;

use App\Models\Document;
use App\Services\PdfGenerator\Renderers\CheckboxRender;
use App\Services\PdfGenerator\Renderers\DynamicTagRenderer;
use App\Services\PdfGenerator\Renderers\ImageRenderer;
use App\Services\PdfGenerator\Renderers\StaticTextRenderer;
use InvalidArgumentException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

final class PdfGeneratorService
{
    /**
     * @param  StaticTextRenderer  $staticTextRenderer  Strategy for rendering simple text.
     * @param  DynamicTagRenderer  $dynamicTagRenderer  Strategy for rendering dynamic data tags.
     * @param  ImageRenderer  $imageRenderer  Strategy for rendering images.
     * @param  CheckboxRender  $checkboxRender  Strategy for rendering checkboxes.
     */
    public function __construct(
        private readonly StaticTextRenderer $staticTextRenderer,
        private readonly DynamicTagRenderer $dynamicTagRenderer,
        private readonly ImageRenderer $imageRenderer,
        private readonly CheckboxRender $checkboxRender,
    ) {}

    /**
     * Generates a PDF by adding elements to a source template.
     *
     * @param  string  $pdfFileContent  The raw content of the source PDF file.
     * @param  array<string, mixed>  $elementsConfig  The configuration for the elements to render.
     * @param  object|array<string, mixed>  $data  The data object or array to populate dynamic tags.
     * @return string The raw content of the generated PDF file.
     *
     * @throws InvalidArgumentException If an unknown element type is found in the configuration.
     */
    public function generate(string $pdfFileContent, array $elementsConfig, object|array $data): string
    {
        // It's better to convert array to object for consistent `data_get` behavior.
        $dataObject = is_array($data) ? (object) $data : $data;

        $pdf = new Fpdi;

        // Load the source PDF from its raw content string.
        $pageCount = $pdf->setSourceFile(StreamReader::createByString($pdfFileContent));

        // Process all pages of the source document.
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // Render elements on the current page.
            foreach ($elementsConfig as $element) {
                // Render element only if it belongs to the current page (default to page 1)
                if ((int) ($element['page'] ?? 1) === $pageNo) {
                    match ($element['type']) {
                        'text' => $this->staticTextRenderer->render($pdf, $element, $dataObject),
                        'tag' => $this->dynamicTagRenderer->render($pdf, $element, $dataObject),
                        'image' => $this->imageRenderer->render($pdf, $element, $dataObject),
                        'checkbox' => $this->checkboxRender->render($pdf, $element, $dataObject),
                        default => throw new InvalidArgumentException("Unknown element type: '{$element['type']}'"),
                    };
                }
            }
        }

        // Return the generated PDF as a string.
        return $pdf->Output('S');
    }
}
