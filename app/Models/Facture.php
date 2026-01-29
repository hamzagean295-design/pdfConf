<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Facture extends Model
{
    protected $fillable = [
        'customer_name',
        'montant',
        'date_facture',
        'document_path',
    ];

    public function documentUrl()
    {
        return Storage::url($this->document_path);
    }
}
