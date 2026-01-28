<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PdfGenerator\PdfGeneratorService;
use Illuminate\Http\Response;
use stdClass;

class DocumentGeneratorController extends Controller
{
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
}