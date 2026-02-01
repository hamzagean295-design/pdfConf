<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Cnss extends Model
{
    protected $fillable = [
        'patient',
        'cin',
        'adresse',
        'date_naissance',
        'sexe',
        'parente',
        'service_hospitalisation',
        'inp',
        'nature_hospitalisation',
        'motif_hospitalisation',
        'date_previsible_hospitalisation',
        'date_en_urgence_le',
        'nom_etablissement',
        'code_etablissement',
        'tel',
        'total_estime',
        'total',
        'template_id',
        'document_path'
    ];

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
