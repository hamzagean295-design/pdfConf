<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PdfGenerator\PdfGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException; // Ajouté
use Illuminate\View\View;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use stdClass;

class DocumentGeneratorController extends Controller
{
    /**
     * Shows the advanced (canvas) PDF editor interface.
     */
    public function edit(Document $document): View
    {
        return view('setup', [
            'document' => $document,
            'pdfUrl' => Storage::url($document->path)
        ]);
    }

    /**
     * Shows the simple (form-based) PDF editor interface.
     */
    public function editSimple(Document $document): View
    {
        $pageCount = 0;
        if (Storage::disk('local')->exists($document->path)) {
            $fileContent = Storage::disk('local')->get($document->path);
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile(StreamReader::createByString($fileContent));
        }

        return view('edit-simple', [
            'document' => $document,
            'pdfUrl' => Storage::url($document->path),
            'totalPages' => $pageCount,
        ]);
    }

    /**
     * Generates and serves a customized PDF document for preview.
     *
     * @param Document $document The Document model instance, resolved via Route Model Binding.
     * @param PdfGeneratorService $pdfGenerator The service responsible for PDF creation.
     * @return Response
     */
    public function download(Document $document, PdfGeneratorService $pdfGenerator): Response
    {
        // --- Bonus "Pro" ---
        // On simule un objet de données complexe avec stdClass.
        // Cela prouve que le système est découplé et peut accepter n'importe quel
        // objet, pas seulement un modèle Eloquent.
        $data = new stdClass();
        $data->customer = (object) [
            'name' => 'John Doe Inc.',
            'age' => '30',
            'address' => '123 Laravel Lane',
        ];
        $data->invoice = (object) [
            'number' => 'INV-2024-00123',
            'date' => now()->format('d/m/Y'),
            'total' => 99.99,
        ];
        // --- Fin du Bonus ---

        // 1. Appel du service pour générer le contenu brut du PDF
        $pdfContent = $pdfGenerator->generate($document, $data);

        // 2. Définition des headers pour une prévisualisation dans le navigateur
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->name . '.pdf"',
        ];

        // 3. Retour d'une réponse Laravel avec le contenu et les headers
        return response($pdfContent, 200, $headers);
    }

    /**
     * Updates the document's configuration.
     *
     * @param Request $request
     * @param Document $document
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function saveConfig(Request $request, Document $document)
    {
        $validated = $request->validate([
            'config' => ['required', 'array'],
            'config.elements' => ['array'],
            'config.elements.*.type' => ['required', 'string', 'in:text,tag,image'],
            'config.elements.*.page' => ['required', 'integer', 'min:1'],
            'config.elements.*.value' => ['required', 'string'],
            'config.elements.*.x' => ['required', 'numeric'],
            'config.elements.*.y' => ['required', 'numeric'],
            'config.elements.*.font_family' => ['nullable', 'string'],
            'config.elements.*.font_style' => ['nullable', 'string'],
            'config.elements.*.font_size' => ['nullable', 'numeric'],
            'config.elements.*.font_weight' => ['nullable', 'string'], // Nouvelle propriété
            'config.elements.*.color' => ['nullable', 'string'], // Hex string from frontend
        ]);

        $elements = collect($validated['config']['elements'])->map(function ($element) {
            // Déterminer le type automatiquement si non fourni (pour les anciens éléments)
            if (!isset($element['type'])) {
                $element['type'] = preg_match('/^\{\{.*\}\}$/', $element['value']) ? 'tag' : 'text';
            }

            // Convert hex color string back to RGB array if present
            if (isset($element['color']) && is_string($element['color']) && preg_match('/^#([0-9a-fA-F]{3}){1,2}$/', $element['color'])) {
                $hex = ltrim($element['color'], '#');
                if (strlen($hex) == 3) {
                    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
                }
                $element['color'] = [
                    hexdec($hex[0] . $hex[1]),
                    hexdec($hex[2] . $hex[3]),
                    hexdec($hex[4] . $hex[5]),
                ];
            } else {
                // Ensure color is an array or null if not a valid hex string
                $element['color'] = null;
            }
            return $element;
        })->toArray();

        $document->config = ['elements' => $elements];
        $document->save();

        return response()->json(['message' => 'Configuration updated successfully']);
    }
}
