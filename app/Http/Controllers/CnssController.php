<?php

namespace App\Http\Controllers;

use App\Http\Requests\CnssRequest;
use App\Models\Cnss;
use App\Models\Document;
use App\Services\PdfGenerator\PdfGeneratorService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CnssController extends Controller
{
    public function index(): View
    {
        $cnsses = Cnss::all();

        return view('cnss.index', compact('cnsses'));
    }

    public function create(): View
    {
        $documents = Document::all(['id', 'name']);

        return view('cnss.create', compact('documents'));
    }

    public function store(CnssRequest $request, PdfGeneratorService $pdfGenerator): RedirectResponse
    {
        $cnss = Cnss::create($request->validated());

        try {
            $this->generateAndStoreCnssPdf($cnss, $pdfGenerator);
        } catch (FileNotFoundException $e) {
            Log::error("PDF generation failed for new Cnss {$cnss->id}: ".$e->getMessage());

            return redirect()->back()->withInput()->withErrors(['pdf_generation' => 'Erreur lors de la génération du PDF : '.$e->getMessage()]);
        }

        return redirect()->route('cnss.index')->with('success', 'Cnss créée avec succès.');
    }

    public function show(Cnss $cnss): View
    {
        return view('cnss.show', compact('cnss'));
    }

    public function edit(Cnss $cnss): View
    {
        $documents = Document::all(['id', 'name']);

        return view('cnss.edit', compact('cnss', 'documents'));
    }

    public function update(CnssRequest $request, Cnss $cnss, PdfGeneratorService $pdfGenerator): RedirectResponse
    {
        $cnss->update($request->validated());

        try {
            $this->generateAndStoreCnssPdf($cnss, $pdfGenerator);
        } catch (FileNotFoundException $e) {
            Log::error("PDF generation failed for Cnss {$cnss->id}: ".$e->getMessage());

            return redirect()->back()->withInput()->withErrors(['pdf_generation' => 'Erreur lors de la génération du PDF : '.$e->getMessage()]);
        }

        return redirect()->route('cnss.index')->with('success', 'Cnss mise à jour avec succès.');
    }

    public function destroy(Cnss $cnss): RedirectResponse
    {
        if ($cnss->document_path && Storage::exists($cnss->document_path)) {
            Storage::delete($cnss->document_path);
        }
        $cnss->delete();

        return redirect()->route('cnss.index')->with('success', 'Cnss supprimée avec succès.');
    }

    public function downloadPdf(Cnss $cnss): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        if (! $cnss->document_path || ! Storage::exists($cnss->document_path)) {
            return redirect()->back()->withErrors(['pdf_download' => 'Le PDF généré pour cette cnss est introuvable.']);
        }

        $filePath = Storage::path($cnss->document_path);
        $fileName = 'cnss_'.$cnss->id.'.pdf';

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    private function generateAndStoreCnssPdf(Cnss $cnss, PdfGeneratorService $pdfGenerator): void
    {
        $documentTemplate = $cnss->document;

        if (! $documentTemplate) {
            throw new FileNotFoundException("No document template found for Cnss ID: {$cnss->id} (template_id: {$cnss->template_id})");
        }

        if (! Storage::exists($documentTemplate->path)) {
            throw new FileNotFoundException("Source PDF template not found at path: {$documentTemplate->path}");
        }

        $pdfFileContent = Storage::get($documentTemplate->path);
        $elementsConfig = $documentTemplate->config['elements'] ?? [];

        $data = (object) $cnss->toArray();

        $generatedPdfContent = $pdfGenerator->generate($pdfFileContent, $elementsConfig, $data);

        $fileName = 'cnss/cnss_'.$cnss->id.'_'.now()->format('YmdHis').'.pdf';

        if ($cnss->document_path && Storage::exists($cnss->document_path)) {
            Storage::delete($cnss->document_path);
        }

        Storage::put($fileName, $generatedPdfContent);

        $cnss->update(['document_path' => $fileName]);
    }
}
