<?php

declare(strict_types=1);

namespace App\Services\PdfGenerator;

use App\Models\Document;
use App\Services\PdfGenerator\Renderers\DynamicTagRenderer;
use App\Services\PdfGenerator\Renderers\ImageRenderer;
use App\Services\PdfGenerator\Renderers\StaticTextRenderer;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

final class PdfGeneratorService
{
    /**
     * @param StaticTextRenderer $staticTextRenderer Strategy for rendering simple text.
     * @param DynamicTagRenderer $dynamicTagRenderer Strategy for rendering dynamic data tags.
     * @param ImageRenderer $imageRenderer Strategy for rendering images.
     */
    public function __construct(
        private readonly StaticTextRenderer $staticTextRenderer,
        private readonly DynamicTagRenderer $dynamicTagRenderer,
        private readonly ImageRenderer $imageRenderer,
    ) {}

    /**
     * Generates a PDF by adding elements to a source template.
     *
     * @param Document $document The document model containing the path to the source PDF and the configuration.
     * @param object|array<string, mixed> $data The data object or array to populate dynamic tags.
     * @return string The raw content of the generated PDF file.
     * @throws FileNotFoundException If the source PDF file does not exist in storage.
     * @throws InvalidArgumentException If an unknown element type is found in the configuration.
     */
    public function generate(Document $document, object|array $data): string
    {
        // Ensure the source PDF exists in the specified storage disk.
        if (!Storage::disk('local')->exists($document->path)) {
            throw new FileNotFoundException("Source PDF not found at path: {$document->path}");
        }

        // It's better to convert array to object for consistent `data_get` behavior.
        $dataObject = is_array($data) ? (object) $data : $data;

        $pdf = new Fpdi();

        // Load the source PDF from its raw content string.
        $fileContent = Storage::disk('local')->get($document->path);
        $pageCount = $pdf->setSourceFile(StreamReader::createByString($fileContent));

        // Process all pages of the source document.
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // Render elements on the current page.
            foreach ($document->config['elements'] ?? [] as $element) {
                // Render element only if it belongs to the current page (default to page 1)
                if ((int)($element['page'] ?? 1) === $pageNo) {
                    match ($element['type']) {
                        'text'  => $this->staticTextRenderer->render($pdf, $element, $dataObject),
                        'tag'   => $this->dynamicTagRenderer->render($pdf, $element, $dataObject),
                        'image' => $this->imageRenderer->render($pdf, $element, $dataObject),
                        default => throw new InvalidArgumentException("Unknown element type: '{$element['type']}'"),
                    };
                }
            }
        }

        // Return the generated PDF as a string.
        return $pdf->Output('S');
    }
}
