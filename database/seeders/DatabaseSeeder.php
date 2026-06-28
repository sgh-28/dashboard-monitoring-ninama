<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectPhase;
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

        $customerKlinik = User::firstOrCreate(['email' => 'admin@kliniksehatprima.com'], [
            'name' => 'Klinik Sehat Prima',
            'password' => Hash::make('password'),
            'role_id' => $roleCustomer->id,
            'company' => 'Klinik Sehat Prima',
            'phone' => '081277771001',
        ]);

        $customerGudang = User::firstOrCreate(['email' => 'it@gudangnusantara.co.id'], [
            'name' => 'PT Gudang Nusantara',
            'password' => Hash::make('password'),
            'role_id' => $roleCustomer->id,
            'company' => 'PT Gudang Nusantara',
            'phone' => '081288882002',
        ]);

        $customerApartemen = User::firstOrCreate(['email' => 'ops@apartemensentosa.com'], [
            'name' => 'Apartemen Sentosa',
            'password' => Hash::make('password'),
            'role_id' => $roleCustomer->id,
            'company' => 'Apartemen Sentosa',
            'phone' => '081299993003',
        ]);

        // ================= PROJECTS =================
        
        // 1. Penawaran Marketing
        $projOffer1 = Project::firstOrCreate(['name' => 'Website E-Commerce'], [
            'category' => 'web',
            'status' => 'ongoing',
            'client_name' => 'PT. Digital Kreatif',
            'customer_id' => $customer1->id,
            'address' => 'Jl. Inovasi No.1, Jakarta',
            'created_at' => Carbon::parse('2024-07-13'),
        ]);

        $projOffer2 = Project::firstOrCreate(['name' => 'Aplikasi Mobile Banking'], [
            'category' => 'web',
            'status' => 'ongoing',
            'client_name' => 'Startup Maju Terus',
            'customer_id' => $customer2->id,
            'address' => 'Jl. Teknologi No. 22, Bandung',
            'created_at' => Carbon::parse('2024-07-18'),
        ]);

        // 2. Progres Penawaran
        Project::firstOrCreate(['name' => 'Sistem Informasi Akademik'], [
            'category' => 'web',
            'status' => 'ongoing',
            'client_name' => 'PT. Abadi Sentosa',
            'customer_id' => null,
            'status_text' => 'Follow Up',
        ]);

        // 3. Penawaran Ditolak
        Project::firstOrCreate(['name' => 'Website Portal Berita'], [
            'category' => 'web',
            'status' => 'done',
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

        // Penawaran Deal yang sudah dibuatkan customer, project, divisi, dan task
        $projectKlinik = Project::updateOrCreate(
            ['name' => 'Sistem Booking Klinik Sehat Prima'],
            [
                'category' => 'web',
                'status' => 'ongoing',
                'client_name' => 'Klinik Sehat Prima',
                'customer_id' => $customerKlinik->id,
                'address' => 'Jl. Melati Raya No. 17, Bogor',
                'progress' => 45,
                'start_date' => Carbon::parse('2026-06-03'),
                'deadline' => Carbon::parse('2026-07-20'),
                'sla' => 95,
            ]
        );

        $klinikUiux = ProjectDivision::firstOrCreate(
            ['project_id' => $projectKlinik->id, 'name' => 'UI/UX'],
            ['progress' => 80]
        );
        $klinikFrontend = ProjectDivision::firstOrCreate(
            ['project_id' => $projectKlinik->id, 'name' => 'Frontend'],
            ['progress' => 45]
        );
        $klinikBackend = ProjectDivision::firstOrCreate(
            ['project_id' => $projectKlinik->id, 'name' => 'Backend'],
            ['progress' => 30]
        );

        ProjectTask::firstOrCreate(
            ['division_id' => $klinikUiux->id, 'title' => 'Membuat flow booking dan wireframe pasien'],
            [
                'project_id' => $projectKlinik->id,
                'assigned_to' => $pegawai1->id,
                'status' => 'done',
                'progress' => 100,
                'deadline' => Carbon::parse('2026-06-12'),
                'completed_at' => Carbon::parse('2026-06-11'),
                'completion_notes' => 'Flow pasien, dokter, dan admin sudah disetujui PIC klinik.',
            ]
        );
        ProjectTask::firstOrCreate(
            ['division_id' => $klinikFrontend->id, 'title' => 'Implementasi dashboard antrean dokter'],
            [
                'project_id' => $projectKlinik->id,
                'assigned_to' => $pegawai1->id,
                'status' => 'ongoing',
                'progress' => 45,
                'deadline' => Carbon::parse('2026-07-02'),
            ]
        );
        ProjectTask::firstOrCreate(
            ['division_id' => $klinikBackend->id, 'title' => 'API jadwal dokter dan booking pasien'],
            [
                'project_id' => $projectKlinik->id,
                'assigned_to' => $pegawai2->id,
                'status' => 'ongoing',
                'progress' => 35,
                'deadline' => Carbon::parse('2026-07-08'),
            ]
        );

        MarketingOffer::updateOrCreate(
            ['company_name' => 'Klinik Sehat Prima'],
            [
                'company_address' => 'Jl. Melati Raya No. 17, Bogor',
                'contact_person' => 'dr. Maya Lestari',
                'contact_position' => 'Direktur Klinik',
                'contact_phone' => '081277771001',
                'contact_email' => 'admin@kliniksehatprima.com',
                'category' => 'web',
                'offer_description' => 'Sistem booking dokter, antrean pasien, dan dashboard admin klinik',
                'estimated_value' => 120000000,
                'offer_date' => Carbon::parse('2026-05-22'),
                'follow_up_date' => Carbon::parse('2026-05-27'),
                'meeting_date' => Carbon::parse('2026-05-30 10:00'),
                'status' => 'deal',
                'reason' => null,
                'notes' => 'Customer dan project sudah dibuat. Task awal sudah dibagikan ke UI/UX, frontend, dan backend.',
                'employee_id' => $marketing1->id,
                'project_id' => $projectKlinik->id,
            ]
        );

        $projectGudang = Project::updateOrCreate(
            ['name' => 'Jaringan Internet Gudang Nusantara'],
            [
                'category' => 'internet',
                'status' => 'ongoing',
                'client_name' => 'PT Gudang Nusantara',
                'customer_id' => $customerGudang->id,
                'address' => 'Kawasan Pergudangan Taman Tekno Blok C7, Tangerang Selatan',
                'progress' => 60,
                'start_date' => Carbon::parse('2026-06-01'),
                'deadline' => Carbon::parse('2026-07-05'),
                'sla' => 98,
            ]
        );

        $gudangNetwork = ProjectDivision::firstOrCreate(
            ['project_id' => $projectGudang->id, 'name' => 'Network Engineer'],
            ['progress' => 65]
        );
        $gudangNoc = ProjectDivision::firstOrCreate(
            ['project_id' => $projectGudang->id, 'name' => 'NOC'],
            ['progress' => 55]
        );

        ProjectTask::firstOrCreate(
            ['division_id' => $gudangNetwork->id, 'title' => 'Survey jalur kabel dan titik access point'],
            [
                'project_id' => $projectGudang->id,
                'assigned_to' => $pegawai2->id,
                'status' => 'done',
                'progress' => 100,
                'deadline' => Carbon::parse('2026-06-08'),
                'completed_at' => Carbon::parse('2026-06-07'),
            ]
        );
        ProjectTask::firstOrCreate(
            ['division_id' => $gudangNetwork->id, 'title' => 'Instalasi rack dan konfigurasi router utama'],
            [
                'project_id' => $projectGudang->id,
                'assigned_to' => $pegawai2->id,
                'status' => 'ongoing',
                'progress' => 60,
                'deadline' => Carbon::parse('2026-06-25'),
            ]
        );
        ProjectTask::firstOrCreate(
            ['division_id' => $gudangNoc->id, 'title' => 'Setup monitoring bandwidth dan alert downtime'],
            [
                'project_id' => $projectGudang->id,
                'assigned_to' => $pegawai1->id,
                'status' => 'pending',
                'progress' => 0,
                'deadline' => Carbon::parse('2026-07-02'),
            ]
        );

        MarketingOffer::updateOrCreate(
            ['company_name' => 'PT Gudang Nusantara'],
            [
                'company_address' => 'Kawasan Pergudangan Taman Tekno Blok C7, Tangerang Selatan',
                'contact_person' => 'Bapak Fadli Rahman',
                'contact_position' => 'IT Supervisor',
                'contact_phone' => '081288882002',
                'contact_email' => 'it@gudangnusantara.co.id',
                'category' => 'internet',
                'offer_description' => 'Internet dedicated 100 Mbps, rack jaringan, access point gudang, dan monitoring NOC',
                'estimated_value' => 175000000,
                'offer_date' => Carbon::parse('2026-05-24'),
                'follow_up_date' => Carbon::parse('2026-05-28'),
                'meeting_date' => Carbon::parse('2026-05-31 14:00'),
                'status' => 'deal',
                'reason' => null,
                'notes' => 'Customer dan project sudah dibuat. Divisi network dan NOC sudah memiliki task.',
                'employee_id' => $marketing2->id,
                'project_id' => $projectGudang->id,
            ]
        );

        $projectApartemen = Project::updateOrCreate(
            ['name' => 'CCTV Area Publik Apartemen Sentosa'],
            [
                'category' => 'cctv',
                'status' => 'ongoing',
                'client_name' => 'Apartemen Sentosa',
                'customer_id' => $customerApartemen->id,
                'address' => 'Jl. Boulevard Sentosa No. 9, Surabaya',
                'progress' => 35,
                'start_date' => Carbon::parse('2026-06-10'),
                'deadline' => Carbon::parse('2026-07-18'),
                'sla' => 96,
            ]
        );

        $apartemenInstaller = ProjectDivision::firstOrCreate(
            ['project_id' => $projectApartemen->id, 'name' => 'CCTV Installer'],
            ['progress' => 40]
        );
        $apartemenConfig = ProjectDivision::firstOrCreate(
            ['project_id' => $projectApartemen->id, 'name' => 'Configuration'],
            ['progress' => 20]
        );

        ProjectTask::firstOrCreate(
            ['division_id' => $apartemenInstaller->id, 'title' => 'Pemasangan kamera area lobby dan parkir'],
            [
                'project_id' => $projectApartemen->id,
                'assigned_to' => $pegawai1->id,
                'status' => 'ongoing',
                'progress' => 40,
                'deadline' => Carbon::parse('2026-06-30'),
            ]
        );
        ProjectTask::firstOrCreate(
            ['division_id' => $apartemenConfig->id, 'title' => 'Konfigurasi NVR dan akses monitoring security'],
            [
                'project_id' => $projectApartemen->id,
                'assigned_to' => $pegawai2->id,
                'status' => 'pending',
                'progress' => 0,
                'deadline' => Carbon::parse('2026-07-10'),
            ]
        );

        MarketingOffer::updateOrCreate(
            ['company_name' => 'Apartemen Sentosa'],
            [
                'company_address' => 'Jl. Boulevard Sentosa No. 9, Surabaya',
                'contact_person' => 'Ibu Nadya Putri',
                'contact_position' => 'Building Manager',
                'contact_phone' => '081299993003',
                'contact_email' => 'ops@apartemensentosa.com',
                'category' => 'cctv',
                'offer_description' => 'Pemasangan CCTV lobby, parkir, lift, dan ruang keamanan dengan akses monitoring',
                'estimated_value' => 98000000,
                'offer_date' => Carbon::parse('2026-06-01'),
                'follow_up_date' => Carbon::parse('2026-06-04'),
                'meeting_date' => Carbon::parse('2026-06-07 09:30'),
                'status' => 'deal',
                'reason' => null,
                'notes' => 'Customer dan project sudah dibuat. Instalasi tahap pertama sedang berjalan.',
                'employee_id' => $marketing2->id,
                'project_id' => $projectApartemen->id,
            ]
        );

        // Satu laporan deal sengaja belum dibuatkan akun customer/project.
        MarketingOffer::updateOrCreate(
            ['company_name' => 'CV. Logistik Cepat'],
            [
                'company_address' => 'Jl. Gatot Subroto No. 12, Tangerang',
                'contact_person' => 'Ibu Sari',
                'contact_position' => 'Owner',
                'contact_phone' => '081298765432',
                'contact_email' => 'sari@logistikcepat.com',
                'category' => 'internet',
                'offer_description' => 'Instalasi jaringan LAN & WAN untuk 3 cabang',
                'estimated_value' => 250000000,
                'offer_date' => Carbon::parse('2026-06-15'),
                'follow_up_date' => Carbon::parse('2026-06-18'),
                'meeting_date' => Carbon::parse('2026-06-22 13:00'),
                'status' => 'deal',
                'reason' => null,
                'notes' => 'Deal sudah disetujui. Admin perlu membuat akun customer dan project dari tombol copy data.',
                'employee_id' => $marketing2->id,
                'project_id' => null,
            ]
        );

        // ================= PROJECT PHASES, TASKS & TIMELINE =================

        // Project operasional hanya memakai status ongoing dan done.
        Project::whereIn('status', ['offer', 'progress_offer', 'rejected'])->delete();

        Project::whereIn('status', ['ongoing', 'done'])
            ->orderBy('id')
            ->get()
            ->each(function (Project $project) use ($pegawai1, $pegawai2) {
                $this->seedProjectTimelineAndTasks($project, [$pegawai1->id, $pegawai2->id]);
            });

        foreach (['web', 'internet', 'cctv'] as $category) {
            if (!Project::where('category', $category)->where('status', 'done')->exists()) {
                $fallbackProject = Project::create([
                    'name' => 'Contoh Proyek Selesai ' . strtoupper($category),
                    'category' => $category,
                    'status' => 'done',
                    'client_name' => 'Demo Customer',
                    'progress' => 100,
                    'start_date' => now()->subDays(45)->toDateString(),
                    'end_date' => now()->subDays(5)->toDateString(),
                    'deadline' => now()->subDays(3)->toDateString(),
                    'sla' => 100,
                ]);

                $this->seedProjectTimelineAndTasks($fallbackProject, [$pegawai1->id, $pegawai2->id]);
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

    private function seedProjectTimelineAndTasks(Project $project, array $employeeIds): void
    {
        $project->refresh();

        $timeline = [
            ['name' => 'Analisis Kebutuhan', 'division' => 'Project Management', 'days' => 6],
            ['name' => 'Desain UI/UX', 'division' => 'UI/UX', 'days' => 8],
            ['name' => 'Development', 'division' => $project->category === 'internet' ? 'Network Engineer' : ($project->category === 'cctv' ? 'CCTV Installer' : 'Frontend'), 'days' => 14],
            ['name' => 'Testing', 'division' => $project->category === 'internet' ? 'NOC' : ($project->category === 'cctv' ? 'Configuration' : 'Testing'), 'days' => 7],
            ['name' => 'Deployment', 'division' => $project->category === 'internet' ? 'Technical Support' : ($project->category === 'cctv' ? 'Monitoring' : 'DevOps'), 'days' => 4],
        ];

        $start = $project->start_date ? Carbon::parse($project->start_date) : now()->subDays($project->status === 'done' ? 45 : 20);
        $cursor = $start->copy();
        $phaseCount = count($timeline);
        $completedLimit = $project->status === 'done' ? $phaseCount : 2;
        $ongoingOrder = $project->status === 'done' ? null : 3;

        ProjectPhase::where('project_id', $project->id)->delete();

        foreach ($timeline as $index => $item) {
            $order = $index + 1;
            $phaseStart = $cursor->copy();
            $phaseTarget = $cursor->copy()->addDays($item['days']);
            $cursor = $phaseTarget->copy()->addDay();

            $status = 'pending';
            $progress = 0;
            $completedDate = null;
            $slaStatus = 'on_track';

            if ($order <= $completedLimit) {
                $status = 'completed';
                $progress = 100;
                $completedDate = $phaseTarget->copy()->subDay();
                $slaStatus = 'completed';
            } elseif ($order === $ongoingOrder) {
                $status = 'ongoing';
                $progress = 55;
            }

            ProjectPhase::create([
                'project_id' => $project->id,
                'phase_name' => $item['name'],
                'phase_order' => $order,
                'status' => $status,
                'progress' => $progress,
                'start_date' => $phaseStart->toDateString(),
                'target_date' => $phaseTarget->toDateString(),
                'completed_date' => $completedDate?->toDateString(),
                'sla_days' => $item['days'],
                'actual_days' => $completedDate ? $phaseStart->diffInDays($completedDate) : null,
                'sla_status' => $slaStatus,
                'notes' => "Seeder timeline {$item['name']} untuk {$project->name}",
            ]);

            $division = ProjectDivision::firstOrCreate(
                ['project_id' => $project->id, 'name' => $item['division']],
                ['progress' => $progress]
            );

            $taskStatus = match ($status) {
                'completed' => 'done',
                'ongoing' => 'ongoing',
                default => 'pending',
            };

            ProjectTask::updateOrCreate(
                ['project_id' => $project->id, 'title' => $item['name']],
                [
                    'division_id' => $division->id,
                    'assigned_to' => $employeeIds[$index % count($employeeIds)] ?? null,
                    'description' => "Task {$item['name']} untuk milestone proyek {$project->name}.",
                    'deadline' => $phaseTarget->toDateString(),
                    'status' => $taskStatus,
                    'progress' => $progress,
                    'planned_start_date' => $phaseStart->toDateString(),
                    'planned_end_date' => $phaseTarget->toDateString(),
                    'actual_start_date' => $status === 'pending' ? null : $phaseStart->toDateString(),
                    'actual_end_date' => $status === 'completed' ? $completedDate->toDateString() : null,
                    'completed_at' => $status === 'completed' ? $completedDate->copy()->setTime(16, 30) : null,
                    'completion_notes' => $status === 'completed' ? "Fase {$item['name']} selesai sesuai rencana." : null,
                    'sla_target' => 100,
                    'is_notified' => false,
                ]
            );
        }

        $project->update([
            'progress' => $project->status === 'done' ? 100 : 55,
            'end_date' => $project->status === 'done' ? ($project->end_date ?? $cursor->copy()->subDay()->toDateString()) : null,
            'deadline' => $project->deadline ?? $cursor->copy()->subDay()->toDateString(),
        ]);
    }
}
