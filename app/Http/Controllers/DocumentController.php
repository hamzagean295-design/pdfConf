<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDocumentConfigRequest;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Services\PdfGenerator\PdfGeneratorService;
use Illuminate\Contracts\Filesystem\FileNotFoundException; // Ajouté
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class DocumentController extends Controller
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
        $url = Storage::url($document->path);

        return redirect($url);
    }

    public function store(StoreDocumentRequest $request)
    {
        $validated = $request->validated();

        $path = $request->file('document')->store('templates');

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
        $dimensionsPage = ['width' => 210, 'height' => 297]; // Valeurs par défaut (A4)
        if (Storage::exists($document->path)) {
            $fileContent = Storage::get($document->path);
            $pdf = new Fpdi;
            $pageCount = $pdf->setSourceFile(StreamReader::createByString($fileContent));
        }

        $fonts = [
            'Arial',
            'Courier',
            'Helvetica',
            'Symbol',
            'Times',
            'ZapfDingbats',
        ];

        return view('documents.edit', [
            'document' => $document,
            'pdfUrl' => Storage::url($document->path),
            'totalPages' => $pageCount,
            'dimensionsPage' => $dimensionsPage,
            'fonts' => $fonts,
        ]);
    }

    public function download(Document $document, PdfGeneratorService $pdfGenerator): Response
    {

        // 1. Lire le contenu du fichier PDF et extraire la configuration des éléments
        if (! Storage::exists($document->path)) {
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
            'Content-Disposition' => 'inline; filename="'.$document->name.'.pdf"',
        ];

        // 4. Retour d'une réponse Laravel avec le contenu et les headers
        return response($pdfContent, 200, $headers);
    }

    public function saveConfig(SaveDocumentConfigRequest $request, Document $document)
    {
        $validated = $request->validated();

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
        if (Storage::exists($document->path)) {
            Storage::delete($document->path);
        }
        // 2. Supprimer l'enregistrement en base de données
        $document->delete();

        return back();
    }
}
