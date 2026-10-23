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
        Schema::create('communications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('bedrijf_id')->constrained('bedrijven')->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->string('type');
            $table->ulidMorphs('related'); // To Quote or Invoice
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->index(['bedrijf_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
