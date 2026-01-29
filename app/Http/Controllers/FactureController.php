<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Document; // Import du modèle Document
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule; // Pour la validation de l'existence du document
use App\Services\PdfGenerator\PdfGeneratorService;
use Illuminate\Contracts\Filesystem\FileNotFoundException; // Ajouté
use Illuminate\Support\Facades\Storage;

class FactureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $factures = Facture::all();
        return view('facture.index', compact('factures'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $documents = Document::all(['id', 'name']); // Récupère tous les documents disponibles
        return view('facture.create', compact('documents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PdfGeneratorService $pdfGenerator): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_facture' => 'required|date',
        ]);

        $facture = Facture::create($validated);
        // génére le pdf du facture via notre service PdfGenerator
        $factureModel = Document::where('name', 'devis')->first();

        if (!Storage::disk('public')->exists($factureModel->path)) {
            throw new FileNotFoundException("Source PDF not found at path: {$factureModel->path}");
        }
        $pdfFileContent = Storage::disk('public')->get($factureModel->path);
        $elementsConfig = $factureModel->config['elements'] ?? [];

        // 2. Appel du service pour générer le contenu brut du PDF
        $pdfContent = $pdfGenerator->generate($pdfFileContent, $elementsConfig, $facture);

        $fileName = 'facture_' . $facture->id . '_' . now()->format('YmdHis') . '.pdf';
        $filePath = 'factures/' . $fileName;

        $facture->update([
            'document_path' => $filePath
        ]);

        Storage::disk('public')->put($filePath, $pdfContent);
        // 3. Définition des headers pour une prévisualisation dans le navigateur
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $factureModel->name . '.pdf"',
        ];

        return redirect()->route('facture.index')->with('success', 'Facture créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Facture $facture): View
    {
        return view('facture.show', compact('facture'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Facture $facture): View
    {
        $documents = Document::all(['id', 'name']); // Récupère tous les documents disponibles
        return view('facture.edit', compact('facture', 'documents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facture $facture, PdfGeneratorService $pdfGenerator): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_facture' => 'required|date',
        ]);

        // supprimer l'ancien pdf géné
        if ($facture->document_path && Storage::disk('public')->exists($facture->document_path)) {
            Storage::disk('public')->delete($facture->document_path);
        }
        // regénére à nouveau

        $factureModel = Document::where('name', 'devis')->first();

        if (!Storage::disk('public')->exists($factureModel->path)) {
            throw new FileNotFoundException("Source PDF not found at path: {$factureModel->path}");
        }
        $pdfFileContent = Storage::disk('public')->get($factureModel->path);
        $elementsConfig = $factureModel->config['elements'] ?? [];

        // 2. Appel du service pour générer le contenu brut du PDF
        $pdfContent = $pdfGenerator->generate($pdfFileContent, $elementsConfig, $facture);

        $fileName = 'facture_' . $facture->id . '_' . now()->format('YmdHis') . '.pdf';
        $filePath = 'factures/' . $fileName;

        $facture->update([
            ...$validated,
            'document_path' => $filePath
        ]);

        Storage::disk('public')->put($filePath, $pdfContent);
        // 3. Définition des headers pour une prévisualisation dans le navigateur
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $factureModel->name . '.pdf"',
        ];

        return redirect()->route('facture.index')->with('success', 'Facture mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facture $facture): RedirectResponse
    {
        if ($facture->document_path && Storage::disk('public')->exists($facture->document_path)) {
            Storage::disk('public')->delete($facture->document_path);
        }
        $facture->delete();

        return redirect()->route('facture.index')->with('success', 'Facture supprimée avec succès.');
    }
}
