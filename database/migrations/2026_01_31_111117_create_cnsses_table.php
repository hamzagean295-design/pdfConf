<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cnsses', function (Blueprint $table) {
            $table->id();
            $table->string('patient');
            $table->string('cin');
            $table->string('adresse');
            $table->date('date_naissance');
            $table->string('sexe');
            $table->string('parente'); // assuré, enfant, conjoint
            $table->string('service_hospitalisation');
            $table->string('inp');
            $table->string('nature_hospitalisation'); // maladie, maternité, accident
            $table->string('motif_hospitalisation');
            $table->date('date_previsible_hospitalisation');
            $table->date('date_en_urgence_le');
            $table->string('nom_etablissement');
            $table->string('code_etablissement');
            $table->string('tel');
            $table->decimal('total_estime');
            $table->decimal('total');
            $table->string('document_path')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('documents')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnsses');
    }
};
