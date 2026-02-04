<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Facture;
use App\Services\PdfGenerator\PdfGeneratorService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
        $documents = Document::all(['id', 'name']);

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
            'template_id' => ['required', 'integer', Rule::exists('documents', 'id')],
            'sexe' => 'nullable|in:F,H',
        ]);

        $facture = Facture::create($validated);

        try {
            $this->generateAndStoreFacturePdf($facture, $pdfGenerator);
        } catch (FileNotFoundException $e) {
            // Log the error and redirect with an error message
            Log::error("PDF generation failed for new Facture {$facture->id}: ".$e->getMessage());

            return redirect()->back()->withInput()->withErrors(['pdf_generation' => 'Erreur lors de la génération du PDF : '.$e->getMessage()]);
        }

        return redirect()->route('factures.index')->with('success', 'Facture créée avec succès.');
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
        $documents = Document::all(['id', 'name']);

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
            'template_id' => ['required', 'integer', Rule::exists('documents', 'id')],
        ]);

        $facture->update($validated);

        try {
            $this->generateAndStoreFacturePdf($facture, $pdfGenerator);
        } catch (FileNotFoundException $e) {
            Log::error("PDF generation failed for Facture {$facture->id}: ".$e->getMessage());

            return redirect()->back()->withInput()->withErrors(['pdf_generation' => 'Erreur lors de la génération du PDF : '.$e->getMessage()]);
        }

        return redirect()->route('factures.index')->with('success', 'Facture mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facture $facture): RedirectResponse
    {
        if ($facture->document_path && Storage::exists($facture->document_path)) {
            Storage::delete($facture->document_path);
        }
        $facture->delete();

        return redirect()->route('factures.index')->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * Serves the generated PDF for a specific Facture.
     *
     * @return Response|RedirectResponse
     */
    public function downloadPdf(Facture $facture): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        if (! $facture->document_path || ! Storage::exists($facture->document_path)) {
            return redirect()->back()->withErrors(['pdf_download' => 'Le PDF généré pour cette facture est introuvable.']);
        }

        $filePath = Storage::path($facture->document_path);
        $fileName = 'facture_'.$facture->id.'.pdf';

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    /**
     * Generates and stores the PDF for a given Facture.
     *
     * @throws FileNotFoundException
     */
    private function generateAndStoreFacturePdf(Facture $facture, PdfGeneratorService $pdfGenerator): void
    {
        $documentTemplate = $facture->document; // Get the associated Document template

        if (! $documentTemplate) {
            throw new FileNotFoundException("No document template found for Facture ID: {$facture->id} (template_id: {$facture->template_id})");
        }

        if (! Storage::exists($documentTemplate->path)) {
            throw new FileNotFoundException("Source PDF template not found at path: {$documentTemplate->path}");
        }

        $pdfFileContent = Storage::get($documentTemplate->path);
        $elementsConfig = $documentTemplate->config['elements'] ?? [];

        // Prepare data for PDF generation using Facture model attributes
        $data = (object) [
            'customer_name' => $facture->customer_name,
            'montant' => $facture->montant,
            'date_facture' => $facture->date_facture,
            'sexe' => $facture->sexe,
            'invoice_number' => $facture->id, // Example: use Facture ID as invoice number
            // Add any other data you want to make available to the PDF template
        ];

        $generatedPdfContent = $pdfGenerator->generate($pdfFileContent, $elementsConfig, $data);

        // Define path to store the generated PDF
        $fileName = 'factures/facture_'.$facture->id.'_'.now()->format('YmdHis').'.pdf';

        // Delete old generated PDF if exists
        if ($facture->document_path && Storage::exists($facture->document_path)) {
            Storage::delete($facture->document_path);
        }

        Storage::put($fileName, $generatedPdfContent);

        $facture->update(['document_path' => $fileName]);
    }
}
