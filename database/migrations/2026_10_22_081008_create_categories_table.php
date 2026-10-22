<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabela 'categories' jest współdzielona między katalogiem produktów (catalog)
     * i blogiem (post). Pole 'type' (discriminator) rozróżnia przeznaczenie kategorii.
     *
     * Wzorzec Senior: Single Table Inheritance z discriminatorem zamiast
     * dwóch osobnych tabel (catalog_categories, post_categories).
     * Eliminuje redundancję schematu i ułatwia ewentualne przyszłe cross-category queries.
     *
     * Klucz złożony (bedrijf_id + type + slug) jest unikalny, co zapobiega
     * zduplikowanym slugom w obrębie tej samej firmy i tego samego modułu.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Multi-tenancy: każda kategoria należy do konkretnego tenanta.
            $table->foreignUlid('bedrijf_id')->constrained('bedrijven')->cascadeOnDelete();

            // Discriminator: 'catalog' lub 'post' — określa do czego służy kategoria.
            $table->string('type')->default('catalog');

            $table->string('name');

            // Slug unikalny w obrębie bedrijf + type (nie globalnie).
            $table->string('slug');

            $table->timestamps();

            // Indeks na bedrijf_id + type — najczęstszy wzorzec zapytania:
            // "daj mi kategorie typu catalog dla tenanta X"
            $table->index(['bedrijf_id', 'type']);

            // Slug musi być unikalny per tenant + type, nie globalnie.
            $table->unique(['bedrijf_id', 'type', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
