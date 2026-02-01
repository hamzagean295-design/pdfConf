<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany; // Ajouté

class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'path',
        'config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
    ];

    /**
     * Get the factures that use this document as a template.
     */
    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class, 'template_id');
    }

    public function cnsses(): HasMany
    {
        return $this->hasMany(Cnss::class, 'template_id');
    }
}
