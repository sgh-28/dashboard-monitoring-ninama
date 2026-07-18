<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_offer_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status');
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['marketing_offer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_offer_histories');
    }
};
