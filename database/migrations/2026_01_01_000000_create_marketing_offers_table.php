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
        Schema::create('marketing_offers', function (Blueprint $table) {
            $table->id();
            
            // Informasi Perusahaan
            $table->string('company_name');
            $table->text('company_address');
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            
            // Kategori Bidang
            $table->enum('category', ['web', 'internet', 'cctv']);
            
            // Detail Penawaran
            $table->text('offer_description')->nullable();
            $table->decimal('estimated_value', 15, 2)->nullable();
            
            // Timeline
            $table->date('offer_date');
            $table->date('follow_up_date')->nullable();
            $table->timestamp('meeting_date')->nullable();
            
            // Status Penawaran
            $table->enum('status', [
                'penawaran',           // Penawaran dikirim
                'follow_up',           // Follow up
                'meeting',             // Meeting dijadwalkan
                'menunggu_keputusan',  // Menunggu keputusan client
                'negosiasi',           // Dalam negosiasi
                'deal',                // Deal/Closing
                'pending',             // Pending
                'rejected',            // Ditolak
                'no_response'          // Tidak ada respon
            ])->default('penawaran');
            
            // Alasan (untuk rejected/pending)
            $table->text('reason')->nullable();
            
            // Hasil/Notes
            $table->text('notes')->nullable();
            
            // Relasi ke User (Marketing)
            $table->foreignId('employee_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Relasi ke Project (jika deal)
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Index untuk performa
            $table->index('category');
            $table->index('status');
            $table->index('offer_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_offers');
    }
};