<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabela 'catalog_items' to serce systemu — BRANŻOWO NEUTRALNA lista pozycji.
     * Zamiast sztywnych kolumn logistycznych (pallet_count, cmr_number, trailer_type)
     * lub specyficznych dla hodowli (breed, gender, birth_date), wszystkie
     * atrybuty specyficzne dla branży trafiają do schemaless kolumny JSON 'attributes'.
     *
     * DLACZEGO to jest lepsze od poprzedniego podejścia (krytykowanego na obronach):
     * - Stara tabela invoices/quotes: dziesiątki kolumn dla logistyki, NULL przy innej branży.
     * - Nowa tabela catalog_items: 10 twardych, zawsze sensownych kolumn + 1 elastyczna JSON.
     *   Dla hodowcy: attributes = {"breed": "British Shorthair", "gender": "male"}
     *   Dla komisu:  attributes = {"year": 2021, "mileage": 85000, "fuel": "diesel"}
     *   Dla usługi:  attributes = {"duration_minutes": 60, "location": "remote"}
     *
     * Wzorzec Senior: 3NF dla twardych danych + schemaless JSON dla atrybutów
     * (wzorzec "EAV bez bólu" — Entity-Attribute-Value w JSON zamiast osobnych tabel).
     *
     * WAŻNE: price_minor przechowuje kwotę w GROSZACH (centach), nie w złotych.
     * To standard Stripe i BCMath — eliminuje błędy floating-point.
     * Wyświetlanie: price_minor / 100 — np. 15000 = 150,00 PLN.
     */
    public function up(): void
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Multi-tenancy: izolacja danych między tenantami.
            $table->foreignUlid('bedrijf_id')->constrained('bedrijven')->cascadeOnDelete();

            // Opcjonalna kategoria — nullable, bo pozycja może istnieć bez kategorii.
            $table->foreignUlid('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete(); // Usunięcie kategorii NIE kasuje pozycji.

            // Nazwa pozycji — np. "Apollo" (kot), "Suzuki Swift 2019", "Masaż klasyczny"
            $table->string('name');

            // Slug do URL-i na froncie, np. /katalog/apollo
            $table->string('slug')->index();

            // Status jako State Machine (Enum): available, reserved, sold_adopted, hidden
            $table->string('status')->default('available');

            // Cena w GROSZACH (minor units). Nullable — niektóre pozycje mają "Zapytaj o cenę".
            // bigInteger, bo np. nieruchomości mogą mieć ceny w milionach groszy.
            $table->unsignedBigInteger('price_minor')->nullable();

            $table->text('description')->nullable();

            // Schemaless atrybuty specyficzne dla branży — serce elastyczności systemu.
            // Brak kolumn: breed, gender, year, mileage — wszystko tu.
            $table->json('attributes')->nullable();

            // Automatyczny prefill tekstu WhatsApp po kliknięciu "Zapytaj".
            // np. "Dzień dobry, jestem zainteresowany kotem Apollo (ID: 01JCXY...)"
            $table->string('whatsapp_prefill_text')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Kompozytowy indeks: najczęstszy wzorzec zapytania na froncie.
            // "Daj mi dostępne pozycje katalogu dla tenanta X"
            $table->index(['bedrijf_id', 'status']);

            // Slug unikalny w obrębie tenanta — nie globalnie (różni tenanci mogą mieć "kot-apollo").
            $table->unique(['bedrijf_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
