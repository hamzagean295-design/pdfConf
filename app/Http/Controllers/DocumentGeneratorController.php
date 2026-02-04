<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PdfGenerator\PdfGeneratorService;
use Illuminate\Contracts\Filesystem\FileNotFoundException; // Ajouté
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

    public function index()
    {
        $documents = Document::all();
        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function show(Document $document)
    {
        // Génère une URL signée valable 5 minutes
        $url = Storage::disk('s3')->temporaryUrl(
            $document->path,
            now()->addMinutes(5)
        );

        return redirect($url);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'document' => 'required|file|mimes:pdf',
        ]);

        $path = $request->file('document')->store('templates', 's3', 'public');

        Document::create([
            'name' => $validated['name'],
            'path' => $path,
            'config' => [],
        ]);
        return to_route('documents.index');
    }

    public function edit(Document $document): View
    {

        $pageCount = 0;
        $dimensionsPage = ['width' => 210, 'height' => 297];

        try {
            // On récupère le contenu pour FPDI
            $fileContent = Storage::disk('s3')->get($document->path);

            $pdf = new \setasign\Fpdi\Fpdi();
            // Utilisation du StreamReader pour lire le flux binaire
            $pageCount = $pdf->setSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($fileContent));
        } catch (\Exception $e) {
            \Log::error("Erreur S3/PDF : " . $e->getMessage());
            $pageCount = 0;
        }

        $fonts = ['Arial', 'Courier', 'Helvetica', 'Symbol', 'Times', 'ZapfDingbats'];

        return view('documents.edit', [
            'document' => $document,
            // CRUCIAL : URL signée pour que le navigateur puisse afficher le PDF
            'pdfUrl' => Storage::disk('s3')->temporaryUrl($document->path, now()->addHours(1)),
            'totalPages' => $pageCount,
            'dimensionsPage' => $dimensionsPage,
            'fonts' => $fonts
        ]);
    }


    public function download(Document $document, PdfGeneratorService $pdfGenerator): Response
    {

        // 1. Lire le contenu du fichier PDF et extraire la configuration des éléments
        if (!Storage::exists($document->path)) {
            throw new FileNotFoundException("Source PDF not found at path: {$document->path}");
        }
        $data = [];
        $pdfFileContent = Storage::get($document->path);
        $elementsConfig = $document->config['elements'] ?? [];
        foreach ($elementsConfig as $el) {
            if ($el['type'] == 'tag' || $el['type'] == 'checkbox') {
                $key = trim($el['value'], '{} ');
                $data[$key] = $el['valueTest'];
            }
        }

        // 2. Appel du service pour générer le contenu brut du PDF
        $pdfContent = $pdfGenerator->generate($pdfFileContent, $elementsConfig, $data);

        // 3. Définition des headers pour une prévisualisation dans le navigateur
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->name . '.pdf"',
        ];

        // 4. Retour d'une réponse Laravel avec le contenu et les headers
        return response($pdfContent, 200, $headers);
    }

    public function saveConfig(Request $request, Document $document)
    {
        // --- Pre-process input to normalize color format ---
        $config = $request->input('config', []);
        if (isset($config['elements']) && is_array($config['elements'])) {
            $elements = $config['elements'];
            foreach ($elements as $key => $element) {
                // If color is a valid hex string, convert it to an RGB array before validation.
                if (isset($element['color']) && is_string($element['color']) && preg_match('/^#([0-9a-fA-F]{3}){1,2}$/', $element['color'])) {
                    $hex = ltrim($element['color'], '#');
                    if (strlen($hex) == 3) {
                        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
                    }
                    $elements[$key]['color'] = [
                        hexdec(substr($hex, 0, 2)),
                        hexdec(substr($hex, 2, 2)),
                        hexdec(substr($hex, 4, 2)),
                    ];
                }
            }
            $config['elements'] = $elements;
            // Replace the request's config with the normalized one.
            $request->merge(['config' => $config]);
        }

        // --- Validation ---
        $validated = $request->validate([
            'config' => ['required', 'array'],
            'config.elements' => ['nullable', 'array'],
            'config.elements.*.type' => ['required', 'string', 'in:text,tag,image,checkbox'],
            'config.elements.*.label' => ['required', 'string', 'max:255'],
            'config.elements.*.page' => ['required', 'integer', 'min:1'],
            'config.elements.*.value' => ['required', 'string'],
            'config.elements.*.valueTest' => ['nullable', 'string'],

            // X and Y are not required for the checkbox group container itself.
            'config.elements.*.x' => ['required_unless:config.elements.*.type,checkbox', 'nullable', 'numeric'],
            'config.elements.*.y' => ['required_unless:config.elements.*.type,checkbox', 'nullable', 'numeric'],

            'config.elements.*.font_family' => ['nullable', 'string'],
            'config.elements.*.font_style' => ['nullable', 'string'],
            'config.elements.*.font_size' => ['nullable', 'numeric'],
            'config.elements.*.font_weight' => ['nullable', 'string'],

            // Color is now expected to be an array of 3 integers.
            'config.elements.*.color' => ['nullable', 'array'],
            'config.elements.*.color.0' => ['required_with:config.elements.*.color', 'integer', 'min:0', 'max:255'], // R
            'config.elements.*.color.1' => ['required_with:config.elements.*.color', 'integer', 'min:0', 'max:255'], // G
            'config.elements.*.color.2' => ['required_with:config.elements.*.color', 'integer', 'min:0', 'max:255'], // B

            // Validation for checkbox options.
            'config.elements.*.options' => ['required_if:config.elements.*.type,checkbox', 'nullable', 'array'],
            'config.elements.*.options.*.label' => ['sometimes', 'required', 'string'],
            'config.elements.*.options.*.value' => ['sometimes', 'required', 'string'],
            'config.elements.*.options.*.x' => ['sometimes', 'required', 'numeric'],
            'config.elements.*.options.*.y' => ['sometimes', 'required', 'numeric'],
        ]);

        // --- Final Processing & Saving ---
        $finalElements = collect($validated['config']['elements'] ?? [])->map(function ($element) {
            // For non-checkbox types, ensure 'options' is not persisted.
            if ($element['type'] !== 'checkbox') {
                unset($element['options']);
            }
            // Ensure x/y are not null for types that need them.
            if ($element['type'] !== 'checkbox') {
                $element['x'] = $element['x'] ?? 0;
                $element['y'] = $element['y'] ?? 0;
            }
            return $element;
        })->toArray();

        $document->config = ['elements' => $finalElements];
        $document->save();

        return response()->json(['message' => 'Configuration updated successfully']);
    }

    public function destroy(Document $document)
    {
        try {
            Storage::disk('s3')->delete($document->path);
            $document->delete();
            return back();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
