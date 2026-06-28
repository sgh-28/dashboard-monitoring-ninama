<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectTask;
use App\Models\MarketingOffer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ================= ROLES =================
        $roleAdmin      = Role::firstOrCreate(['name' => 'super_admin']);
        $roleDirektur   = Role::firstOrCreate(['name' => 'direktur']);
        $rolePegawai    = Role::firstOrCreate(['name' => 'pegawai']);
        $roleCustomer   = Role::firstOrCreate(['name' => 'customer']);
        $roleMarketing  = Role::firstOrCreate(['name' => 'marketing']); // ✅ BARU

        // ================= USERS =================
        
        // Super Admin
        $admin = User::firstOrCreate(['email' => 'admin@ninama.com'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role_id' => $roleAdmin->id,
        ]);

        // Direktur
        $direktur = User::firstOrCreate(['email' => 'direktur@ninama.com'], [
            'name' => 'Direktur Utama',
            'password' => Hash::make('password'),
            'role_id' => $roleDirektur->id,
        ]);

        // Pegawai (Teknis/Development)
        $pegawai1 = User::firstOrCreate(['email' => 'pegawai1@ninama.com'], [
            'name' => 'Budi Santoso (Frontend)',
            'password' => Hash::make('password'),
            'role_id' => $rolePegawai->id,
        ]);

        $pegawai2 = User::firstOrCreate(['email' => 'pegawai2@ninama.com'], [
            'name' => 'Siti Nurhaliza (Backend)',
            'password' => Hash::make('password'),
            'role_id' => $rolePegawai->id,
        ]);

        // ✅ MARKETING TEAM (BARU - Terpisah dari Pegawai)
        $marketing1 = User::firstOrCreate(['email' => 'marketing1@ninama.com'], [
            'name' => 'Anita Wijaya (Marketing Web)',
            'password' => Hash::make('password'),
            'role_id' => $roleMarketing->id,
        ]);

        $marketing2 = User::firstOrCreate(['email' => 'marketing2@ninama.com'], [
            'name' => 'Rudi Hermawan (Marketing Internet)',
            'password' => Hash::make('password'),
            'role_id' => $roleMarketing->id,
        ]);

        // Customer
        $customer1 = User::firstOrCreate(['email' => 'customer@ninama.com'], [
            'name' => 'PT. Digital Kreatif',
            'password' => Hash::make('password'),
            'role_id' => $roleCustomer->id,
            'company' => 'PT. Digital Kreatif',
        ]);

        $customer2 = User::firstOrCreate(['email' => 'startup@maju.com'], [
            'name' => 'Startup Maju Terus',
            'password' => Hash::make('password'),
            'role_id' => $roleCustomer->id,
            'company' => 'Startup Maju Terus',
        ]);

        // ================= PROJECTS =================
        
        // 1. Penawaran Marketing
        $projOffer1 = Project::firstOrCreate(['name' => 'Website E-Commerce'], [
            'category' => 'web',
            'status' => 'offer',
            'client_name' => 'PT. Digital Kreatif',
            'customer_id' => $customer1->id,
            'address' => 'Jl. Inovasi No.1, Jakarta',
            'created_at' => Carbon::parse('2024-07-13'),
        ]);

        $projOffer2 = Project::firstOrCreate(['name' => 'Aplikasi Mobile Banking'], [
            'category' => 'web',
            'status' => 'offer',
            'client_name' => 'Startup Maju Terus',
            'customer_id' => $customer2->id,
            'address' => 'Jl. Teknologi No. 22, Bandung',
            'created_at' => Carbon::parse('2024-07-18'),
        ]);

        // 2. Progres Penawaran
        Project::firstOrCreate(['name' => 'Sistem Informasi Akademik'], [
            'category' => 'web',
            'status' => 'progress_offer',
            'client_name' => 'PT. Abadi Sentosa',
            'customer_id' => null,
            'status_text' => 'Follow Up',
        ]);

        // 3. Penawaran Ditolak
        Project::firstOrCreate(['name' => 'Website Portal Berita'], [
            'category' => 'web',
            'status' => 'rejected',
            'client_name' => 'CV. Terang Benderang',
            'customer_id' => null,
            'rejection_reason' => 'Anggaran tidak sesuai',
        ]);

        // 4. Proyek On-Going
        $projectWeb1 = Project::firstOrCreate(['name' => 'Dashboard Web & Aplikasi'], [
            'category' => 'web',
            'status' => 'ongoing',
            'client_name' => 'Internal Project',
            'customer_id' => null,
            'progress' => 40,
            'start_date' => Carbon::parse('2024-06-01'),
            'deadline' => Carbon::parse('2024-07-30'),
        ]);

        $projectWeb2 = Project::firstOrCreate(['name' => 'Aplikasi Mobile E-Learning'], [
            'category' => 'web',
            'status' => 'ongoing',
            'client_name' => 'Edukasi Cerdas',
            'customer_id' => null,
            'progress' => 85,
            'start_date' => Carbon::parse('2024-06-15'),
            'deadline' => Carbon::parse('2024-08-15'),
        ]);

        // 5. Proyek Selesai
        Project::firstOrCreate(['name' => 'Website Company Profile'], [
            'category' => 'web',
            'status' => 'done',
            'client_name' => 'Jaya Abadi',
            'customer_id' => null,
            'progress' => 100,
            'start_date' => Carbon::parse('2024-06-01'),
            'end_date' => Carbon::parse('2024-06-15'),
            'deadline' => Carbon::parse('2024-06-20'),
            'sla' => 100,
        ]);

        Project::firstOrCreate(['name' => 'Landing Page Produk'], [
            'category' => 'web',
            'status' => 'done',
            'client_name' => 'Lancar Jaya',
            'customer_id' => null,
            'progress' => 100,
            'start_date' => Carbon::parse('2024-06-15'),
            'end_date' => Carbon::parse('2024-06-28'),
            'deadline' => Carbon::parse('2024-06-30'),
            'sla' => 100,
        ]);

        // Internet & CCTV
        Project::firstOrCreate(['name' => 'Instalasi Jaringan Fiber Optic'], [
            'category' => 'internet',
            'status' => 'ongoing',
            'client_name' => 'Kawasan Industri Sentosa',
            'customer_id' => null,
            'progress' => 95,
            'start_date' => Carbon::parse('2024-05-01'),
            'deadline' => Carbon::parse('2024-05-15'),
        ]);

        Project::firstOrCreate(['name' => 'Instalasi Wifi Publik'], [
            'category' => 'internet',
            'status' => 'done',
            'client_name' => 'Taman Kota Sejahtera',
            'customer_id' => null,
            'progress' => 100,
            'start_date' => Carbon::parse('2024-05-01'),
            'end_date' => Carbon::parse('2024-05-10'),
            'deadline' => Carbon::parse('2024-05-12'),
            'sla' => 100,
        ]);

        Project::firstOrCreate(['name' => 'Pemasangan 64 Titik Kamera'], [
            'category' => 'cctv',
            'status' => 'done',
            'client_name' => 'Mall Megah',
            'customer_id' => null,
            'progress' => 100,
            'start_date' => Carbon::parse('2024-04-10'),
            'end_date' => Carbon::parse('2024-04-25'),
            'deadline' => Carbon::parse('2024-04-30'),
            'sla' => 100,
        ]);

        // ================= DIVISIONS & TASKS =================
        
        // Divisions untuk projectWeb1
        $divUIUX = ProjectDivision::firstOrCreate(
            ['project_id' => $projectWeb1->id, 'name' => 'UI/UX'], 
            ['progress' => 40]
        );
        $divFE = ProjectDivision::firstOrCreate(
            ['project_id' => $projectWeb1->id, 'name' => 'Frontend'], 
            ['progress' => 30]
        );
        $divBE = ProjectDivision::firstOrCreate(
            ['project_id' => $projectWeb1->id, 'name' => 'Backend'], 
            ['progress' => 20]
        );

        // ✅ TASKS dengan KOLOM BARU: assigned_to, deadline, proof_image, completion_notes
        // Task 1: Sudah selesai dengan bukti foto
        ProjectTask::firstOrCreate(
            ['division_id' => $divUIUX->id, 'title' => 'Menganalisis kebutuhan pengguna'],
            [
                'project_id' => $projectWeb1->id,
                'assigned_to' => $pegawai1->id, // ✅ Gunakan assigned_to, bukan employee_id
                'status' => 'done',
                'deadline' => Carbon::parse('2024-07-10'),
                'proof_image' => 'task_proofs/sample1.jpg',
                'completion_notes' => 'Telah melakukan wawancara dengan 5 user dan membuat user persona.',
                'completed_at' => Carbon::parse('2024-07-09'),
            ]
        );

        // Task 2: Sedang dikerjakan
        ProjectTask::firstOrCreate(
            ['division_id' => $divFE->id, 'title' => 'Implementasi React Dashboard'],
            [
                'project_id' => $projectWeb1->id,
                'assigned_to' => $pegawai1->id,
                'status' => 'ongoing',
                'deadline' => Carbon::parse('2024-07-20'),
                'proof_image' => null,
                'completion_notes' => null,
                'completed_at' => null,
            ]
        );

        // Task 3: Belum dimulai (pending)
        ProjectTask::firstOrCreate(
            ['division_id' => $divBE->id, 'title' => 'Setup API Authentication'],
            [
                'project_id' => $projectWeb1->id,
                'assigned_to' => $pegawai2->id,
                'status' => 'pending',
                'deadline' => Carbon::parse('2024-07-25'),
                'proof_image' => null,
                'completion_notes' => null,
                'completed_at' => null,
            ]
        );

        // Task 4: OVERDUE (untuk testing warning)
        ProjectTask::firstOrCreate(
            ['division_id' => $divFE->id, 'title' => 'Integrasi Payment Gateway'],
            [
                'project_id' => $projectWeb1->id,
                'assigned_to' => $pegawai1->id,
                'status' => 'pending',
                'deadline' => Carbon::parse('2024-06-01'), // ✅ Sudah lewat
                'proof_image' => null,
                'completion_notes' => null,
                'completed_at' => null,
            ]
        );

        // ================= MARKETING OFFERS (Sample Data) =================
        
        // Penawaran oleh Marketing Web
        MarketingOffer::firstOrCreate(
            ['company_name' => 'PT. Retail Modern'],
            [
                'company_address' => 'Jl. Sudirman No. 88, Jakarta',
                'contact_person' => 'Bapak Hendra',
                'contact_phone' => '081234567890',
                'contact_email' => 'hendra@retailmodern.com',
                'category' => 'web',
                'offer_description' => 'Pembuatan website e-commerce dengan fitur inventory management',
                'estimated_value' => 150000000,
                'offer_date' => Carbon::parse('2024-07-01'),
                'follow_up_date' => Carbon::parse('2024-07-15'),
                'status' => 'negosiasi',
                'reason' => null,
                'notes' => 'Client meminta diskon 10% untuk pembayaran upfront',
                'employee_id' => $marketing1->id,
            ]
        );

        // Penawaran Deal (sukses)
        MarketingOffer::firstOrCreate(
            ['company_name' => 'CV. Logistik Cepat'],
            [
                'company_address' => 'Jl. Gatot Subroto No. 12, Tangerang',
                'contact_person' => 'Ibu Sari',
                'contact_phone' => '081298765432',
                'contact_email' => 'sari@logistikcepat.com',
                'category' => 'internet',
                'offer_description' => 'Instalasi jaringan LAN & WAN untuk 3 cabang',
                'estimated_value' => 250000000,
                'offer_date' => Carbon::parse('2024-06-15'),
                'follow_up_date' => Carbon::parse('2024-06-20'),
                'meeting_date' => Carbon::parse('2024-06-25'),
                'status' => 'deal',
                'reason' => null,
                'notes' => 'Kontrak sudah ditandatangani, project mulai Agustus 2024',
                'employee_id' => $marketing2->id,
                'project_id' => null, // Bisa di-link ke Project jika sudah jadi
            ]
        );

        // Penawaran Rejected
        MarketingOffer::firstOrCreate(
            ['company_name' => 'Toko Online Murah'],
            [
                'company_address' => 'Jl. Merdeka No. 5, Bekasi',
                'contact_person' => 'Pak Joko',
                'contact_phone' => '081122334455',
                'contact_email' => 'joko@tokomurah.com',
                'category' => 'web',
                'offer_description' => 'Website marketplace sederhana',
                'estimated_value' => 50000000,
                'offer_date' => Carbon::parse('2024-07-10'),
                'status' => 'rejected',
                'reason' => 'Budget client hanya 20 juta, tidak sesuai dengan scope pekerjaan',
                'notes' => 'Client mungkin akan kembali dengan budget lebih besar',
                'employee_id' => $marketing1->id,
            ]
        );

        // Penawaran Pending
        MarketingOffer::firstOrCreate(
            ['company_name' => 'Sekolah Cerdas Bangsa'],
            [
                'company_address' => 'Jl. Pendidikan No. 10, Depok',
                'contact_person' => 'Bu Ani',
                'contact_phone' => '081334455667',
                'contact_email' => 'ani@sekolahcerdas.sch.id',
                'category' => 'cctv',
                'offer_description' => 'Pemasangan 32 titik kamera CCTV di area sekolah',
                'estimated_value' => 85000000,
                'offer_date' => Carbon::parse('2024-07-05'),
                'status' => 'menunggu_keputusan',
                'reason' => null,
                'notes' => 'Menunggu approval dari yayasan, estimasi keputusan minggu depan',
                'employee_id' => $marketing2->id,
            ]
        );

        // ================= PROJECT PHASES (Untuk Timeline & SLA) =================
        
        // Tambahkan phases untuk projectWeb1 jika belum ada
        if ($projectWeb1->phases()->count() === 0) {
            $phases = [
                ['phase_name' => 'Analisis Kebutuhan', 'phase_order' => 1, 'status' => 'completed', 'progress' => 100, 'sla_days' => 7, 'sla_status' => 'completed'],
                ['phase_name' => 'Desain UI/UX', 'phase_order' => 2, 'status' => 'completed', 'progress' => 100, 'sla_days' => 10, 'sla_status' => 'completed'],
                ['phase_name' => 'Development', 'phase_order' => 3, 'status' => 'ongoing', 'progress' => 40, 'sla_days' => 21, 'sla_status' => 'on_track'],
                ['phase_name' => 'Testing', 'phase_order' => 4, 'status' => 'pending', 'progress' => 0, 'sla_days' => 7, 'sla_status' => 'on_track'],
                ['phase_name' => 'Deployment', 'phase_order' => 5, 'status' => 'pending', 'progress' => 0, 'sla_days' => 3, 'sla_status' => 'on_track'],
            ];

            foreach ($phases as $phaseData) {
                $projectWeb1->phases()->create([
                    ...$phaseData,
                    'start_date' => Carbon::parse('2024-06-01')->addDays(($phaseData['phase_order'] - 1) * 5),
                    'target_date' => Carbon::parse('2024-06-01')->addDays(($phaseData['phase_order'] - 1) * 5 + $phaseData['sla_days']),
                ]);
            }
        }

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📋 Login Credentials:');
        $this->command->info('  Super Admin : admin@ninama.com / password');
        $this->command->info('  Direktur    : direktur@ninama.com / password');
        $this->command->info('  Pegawai 1   : pegawai1@ninama.com / password');
        $this->command->info('  Pegawai 2   : pegawai2@ninama.com / password');
        $this->command->info('  Marketing 1 : marketing1@ninama.com / password');
        $this->command->info('  Marketing 2 : marketing2@ninama.com / password');
        $this->command->info('  Customer 1  : customer@ninama.com / password');
    }
}