<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Facture extends Model
{
    protected $fillable = [
        'customer_name',
        'montant',
        'date_facture',
        'document_path', // Path to the GENERATED PDF
        'template_id',   // ID of the Document TEMPLATE
        'sexe'
    ];

    /**
     * Get the document template associated with the Facture.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'template_id');
    }

    /**
     * Get the URL for the generated PDF.
     */
    public function generatedPdfUrl(): ?string
    {
        return $this->document_path ? Storage::url($this->document_path) : null;
    }
}
