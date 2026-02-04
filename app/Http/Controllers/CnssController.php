<?php

namespace App\Http\Controllers;

use App\Models\Cnss;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Services\PdfGenerator\PdfGeneratorService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;

class CnssController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $cnsses = Cnss::all();
        return view('cnss.index', compact('cnsses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $documents = Document::all(['id', 'name']);
        return view('cnss.create', compact('documents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PdfGeneratorService $pdfGenerator): RedirectResponse
    {
        $validated = $request->validate([
            'patient' => 'required|string|max:255',
            'cin' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'sexe' => ['required', Rule::in(['F', 'H'])],
            'parente' => ['required', Rule::in(['Assuré', 'Enfant', 'Conjoint'])],
            'service_hospitalisation' => 'required|string|max:255',
            'inp' => 'required|string|max:255',
            'nature_hospitalisation' => 'required|string|max:255',
            'motif_hospitalisation' => 'required|string|max:255',
            'date_previsible_hospitalisation' => 'required|date',
            'date_en_urgence_le' => 'required|date',
            'nom_etablissement' => 'required|string|max:255',
            'code_etablissement' => 'required|string|max:255',
            'tel' => 'required|string|max:255',
            'total_estime' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'template_id' => ['required', 'integer', Rule::exists('documents', 'id')],
        ]);

        $cnss = Cnss::create($validated);

        try {
            $this->generateAndStoreCnssPdf($cnss, $pdfGenerator);
        } catch (FileNotFoundException $e) {
            \Log::error("PDF generation failed for new Cnss {$cnss->id}: " . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['pdf_generation' => 'Erreur lors de la génération du PDF : ' . $e->getMessage()]);
        }

        return redirect()->route('cnss.index')->with('success', 'Cnss créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cnss $cnss): View
    {
        return view('cnss.show', compact('cnss'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cnss $cnss): View
    {
        $documents = Document::all(['id', 'name']);
        return view('cnss.edit', compact('cnss', 'documents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cnss $cnss, PdfGeneratorService $pdfGenerator): RedirectResponse
    {
        $validated = $request->validate([
            'patient' => 'required|string|max:255',
            'cin' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'sexe' => ['required', Rule::in(['F', 'H'])],
            'parente' => ['required', Rule::in(['Assuré', 'Enfant', 'Conjoint'])],
            'service_hospitalisation' => 'required|string|max:255',
            'inp' => 'required|string|max:255',
            'nature_hospitalisation' => 'required|string|max:255',
            'motif_hospitalisation' => 'required|string|max:255',
            'date_previsible_hospitalisation' => 'required|date',
            'date_en_urgence_le' => 'required|date',
            'nom_etablissement' => 'required|string|max:255',
            'code_etablissement' => 'required|string|max:255',
            'tel' => 'required|string|max:255',
            'total_estime' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'template_id' => ['required', 'integer', Rule::exists('documents', 'id')],
        ]);

        $cnss->update($validated);

        try {
            $this->generateAndStoreCnssPdf($cnss, $pdfGenerator);
        } catch (FileNotFoundException $e) {
            \Log::error("PDF generation failed for Cnss {$cnss->id}: " . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['pdf_generation' => 'Erreur lors de la génération du PDF : ' . $e->getMessage()]);
        }

        return redirect()->route('cnss.index')->with('success', 'Cnss mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cnss $cnss): RedirectResponse
    {
        if ($cnss->document_path && Storage::disk('public')->exists($cnss->document_path)) {
            Storage::disk('public')->delete($cnss->document_path);
        }
        $cnss->delete();

        return redirect()->route('cnss.index')->with('success', 'Cnss supprimée avec succès.');
    }

    /**
     * Serves the generated PDF for a specific Cnss.
     */
    public function downloadPdf(Cnss $cnss): Response|RedirectResponse
    {
        if (!$cnss->document_path || !Storage::disk('public')->exists($cnss->document_path)) {
            return redirect()->back()->withErrors(['pdf_download' => 'Le PDF généré pour cette cnss est introuvable.']);
        }

        $filePath = Storage::disk('public')->path($cnss->document_path);
        $fileName = 'cnss_' . $cnss->id . '.pdf';

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Generates and stores the PDF for a given Cnss.
     */
    private function generateAndStoreCnssPdf(Cnss $cnss, PdfGeneratorService $pdfGenerator): void
    {
        $documentTemplate = $cnss->document;

        if (!$documentTemplate) {
            throw new FileNotFoundException("No document template found for Cnss ID: {$cnss->id} (template_id: {$cnss->template_id})");
        }

        if (!Storage::disk('public')->exists($documentTemplate->path)) {
            throw new FileNotFoundException("Source PDF template not found at path: {$documentTemplate->path}");
        }

        $pdfFileContent = Storage::disk('public')->get($documentTemplate->path);
        $elementsConfig = $documentTemplate->config['elements'] ?? [];

        $data = (object) $cnss->toArray();

        $generatedPdfContent = $pdfGenerator->generate($pdfFileContent, $elementsConfig, $data);

        $fileName = 'cnss/cnss_' . $cnss->id . '_' . now()->format('YmdHis') . '.pdf';

        if ($cnss->document_path && Storage::disk('public')->exists($cnss->document_path)) {
            Storage::disk('public')->delete($cnss->document_path);
        }

        Storage::disk('public')->put($fileName, $generatedPdfContent);

        $cnss->update(['document_path' => $fileName]);
    }
}
