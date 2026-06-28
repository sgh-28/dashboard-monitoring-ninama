<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Role;
use App\Models\ProjectDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminProjectController extends Controller
{
    // ✅ Mapping Divisi berdasarkan Kategori Bidang
    private const CATEGORY_DIVISIONS = [
        'web' => ['UI/UX', 'Frontend', 'Backend', 'Testing', 'DevOps', 'Project Management'],
        'internet' => ['Network Engineer', 'NOC', 'Technical Support', 'Server Administrator', 'Fiber Optic Technician', 'Maintenance', 'Project Management'],
        'cctv' => ['CCTV Installer', 'Configuration', 'Monitoring', 'Maintenance', 'Troubleshooting', 'Project Management'],
    ];

    public function index(Request $request)
    {
        $query = Project::with(['customer', 'divisions'])->whereIn('status', ['ongoing', 'done']);
        
        if ($request->filled('customer')) {
            $query->where('customer_id', $request->customer);
        }
        
        $projects = $query->orderByDesc('created_at')->paginate(10);
        
        $stats = [
            'total' => Project::whereIn('status', ['ongoing', 'done'])->count(),
            'ongoing' => Project::where('status', 'ongoing')->count(),
            'completed' => Project::where('status', 'done')->count(),
        ];
        
        return view('admin.projects.index', compact('projects', 'stats'));
    }

    public function offers()
    {
        $categories = ['web', 'internet', 'cctv'];
        $offerData = [];
        
        foreach ($categories as $cat) {
            $offerData[$cat] = [
                'accepted' => Project::where('category', $cat)->whereIn('status', ['ongoing', 'done'])->count(),
                'rejected' => Project::where('category', $cat)->where('status', 'rejected')->count(),
                'pending'  => 0,
            ];
        }
        
        $rejectedProjects = Project::where('status', 'rejected')
            ->with('customer')
            ->orderByDesc('created_at')
            ->get();
        
        return view('admin.offers.index', compact('offerData', 'rejectedProjects'));
    }

    public function create()
    {
        $categories = ['web', 'internet', 'cctv'];
        $statuses = ['ongoing', 'done'];
        $customers = User::whereHas('role', fn($q) => $q->where('name', 'customer'))->get();
        
        return view('admin.projects.create', compact('categories', 'statuses', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:web,internet,cctv',
            'status' => 'required|in:ongoing,done',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'start_date' => 'nullable|date',
            'sla' => 'nullable|integer|min:0|max:100',
            'divisions' => 'nullable|array',
            'divisions.*' => 'string|max:100',
        ]);

        $customer = null;
        $plainPassword = null;
        
        if ($request->filled('customer_id')) {
            $customer = User::findOrFail($request->customer_id);
        } elseif ($request->filled('new_customer_company')) {
            $request->validate([
                'new_customer_company' => 'required|string|max:255',
                'new_customer_name' => 'required|string|max:255',
                'new_customer_email' => 'required|email|unique:users,email',
                'new_customer_password' => 'required|min:6|confirmed',
            ]);

            $role = Role::where('name', 'customer')->firstOrFail();
            $plainPassword = $request->new_customer_password;
            
            $customer = User::create([
                'name' => $request->new_customer_name,
                'email' => $request->new_customer_email,
                'company' => $request->new_customer_company,
                'phone' => $request->new_customer_phone ?? null,
                'password' => Hash::make($request->new_customer_password),
                'role_id' => $role->id,
            ]);
        }

        if (!$customer) {
            return back()->withErrors(['customer_id' => 'Customer harus dipilih atau dibuat'])->withInput();
        }

        $project = Project::create([
            'name' => $request->name,
            'category' => $request->category,
            'status' => $request->status,
            'client_name' => $request->client_name ?? $customer->name,
            'customer_id' => $customer->id,
            'address' => $request->address,
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
            'sla' => $request->sla ?? 100,
            'rejection_reason' => null,
            'progress' => $request->status === 'done' ? 100 : 0,
        ]);

        if ($request->filled('divisions')) {
            foreach ($request->divisions as $divisionName) {
                ProjectDivision::create([
                    'project_id' => $project->id,
                    'name' => $divisionName,
                    'progress' => 0,
                ]);
            }
        }

        // ✅ KIRIM NOTIFIKASI JIKA CUSTOMER BARU DIBUAT
        if ($request->filled('new_customer_company') && $plainPassword) {
            $this->sendCustomerNotification($customer, $project, $plainPassword);
        }

        $message = "Proyek {$request->name} berhasil ditambahkan!";
        if ($request->filled('new_customer_company')) {
            $message .= " Customer baru '{$customer->company}' juga dibuat dengan akun login (Email: {$customer->email}).";
        }

        return redirect()->route('admin.projects.index')->with('success', $message);
    }

    public function edit(Project $project)
    {
        $categories = ['web', 'internet', 'cctv'];
        $statuses = ['ongoing', 'done'];
        $projectDivisions = $project->divisions->pluck('name')->toArray();
        $customers = User::whereHas('role', fn($q) => $q->where('name', 'customer'))->get();
        
        return view('admin.projects.edit', compact('project', 'categories', 'statuses', 'projectDivisions', 'customers'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:web,internet,cctv',
            'status' => 'required|in:ongoing,done',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'start_date' => 'nullable|date',
            'sla' => 'nullable|integer|min:0|max:100',
            'progress' => 'nullable|integer|min:0|max:100',
            'divisions' => 'nullable|array',
            'divisions.*' => 'string|max:100',
        ]);

        $customer = User::findOrFail($request->customer_id);
        
        $project->update([
            'name' => $request->name,
            'category' => $request->category,
            'status' => $request->status,
            'client_name' => $request->client_name ?? $customer->name,
            'customer_id' => $customer->id,
            'address' => $request->address,
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
            'sla' => $request->sla ?? 100,
            'rejection_reason' => null,
            'progress' => $request->progress ?? ($request->status === 'done' ? 100 : $project->progress),
        ]);

        if ($request->filled('divisions')) {
            $project->divisions()->delete();
            foreach ($request->divisions as $divisionName) {
                ProjectDivision::create([
                    'project_id' => $project->id,
                    'name' => $divisionName,
                    'progress' => 0,
                ]);
            }
        }

        if ($request->status === 'done' && $project->progress !== 100) {
            $project->update(['progress' => 100]);
        }

        return redirect()->route('admin.projects.index')
            ->with('success', "Proyek {$project->name} berhasil diperbarui!");
    }

    public function destroy(Project $project)
    {
        $name = $project->name;
        $project->delete();
        
        return redirect()->route('admin.projects.index')
            ->with('success', "Proyek {$name} berhasil dihapus.");
    }

    public function manage(Project $project)
    {
        $project->load(['divisions.tasks', 'customer']);
        $employees = User::whereHas('role', fn($q) => $q->where('name', 'pegawai'))->get();
        
        return view('admin.projects.manage', compact('project', 'employees'));
    }

    /**
     * ✅ BARU: API Endpoint untuk mengambil divisi berdasarkan kategori (digunakan oleh JS)
     */
    public function getDivisionsByCategory($category)
    {
        $divisions = self::CATEGORY_DIVISIONS[$category] ?? [];
        return response()->json($divisions);
    }

    /**
     * ✅ BARU: Kirim notifikasi Email & WhatsApp ke customer baru
     * FIX: Gunakan Mail::raw() untuk plain text email (Laravel 12 compatible)
     */
    private function sendCustomerNotification(User $customer, Project $project, string $plainPassword): void
    {
        $loginUrl = route('login');
        
        // 1. ✅ SEND EMAIL NOTIFICATION (FIXED FOR LARAVEL 12)
        try {
            $emailBody = "Halo {$customer->name},\n\n";
            $emailBody .= "Terima kasih telah mempercayakan proyek kepada PT. Ninama.\n\n";
            $emailBody .= "Akun Anda untuk memantau progress proyek telah berhasil dibuat.\n\n";
            $emailBody .= "📋 DETAIL LOGIN:\n";
            $emailBody .= "━━━━━━━━━━━━━━━━━━━━\n";
            $emailBody .= "• Email    : {$customer->email}\n";
            $emailBody .= "• Password : {$plainPassword}\n";
            $emailBody .= "• URL Login: {$loginUrl}\n";
            $emailBody .= "━━━━━━━━━━━━━━━━━━━━\n\n";
            $emailBody .= "📦 DETAIL PROYEK:\n";
            $emailBody .= "• Nama Proyek : {$project->name}\n";
            $emailBody .= "• Kategori    : " . ucfirst($project->category) . "\n";
            $emailBody .= "• Status      : " . ucfirst(str_replace('_', ' ', $project->status)) . "\n";
            $emailBody .= "• Deadline    : " . ($project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d F Y') : '-') . "\n\n";
            $emailBody .= "Silakan login ke dashboard customer untuk melihat progress proyek, task list, dan laporan pengerjaan secara real-time.\n\n";
            $emailBody .= "Jika Anda mengalami kendala saat login, silakan hubungi tim support kami.\n\n";
            $emailBody .= "Salam,\nTim PT. Ninama\n🌐 www.ninama.com";

            // ✅ Gunakan Mail::raw() untuk plain text (Laravel 12 compatible)
            Mail::raw($emailBody, function ($message) use ($customer, $project) {
                $message->to($customer->email)
                        ->subject('🎉 Akun Customer Ninama - ' . $customer->company);
            });
            
            Log::info("Email sent to {$customer->email} for project {$project->name}");
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$customer->email}: " . $e->getMessage());
        }

        // 2. ✅ SEND WHATSAPP NOTIFICATION (via Fonnte API)
        try {
            if ($customer->phone) {
                // Format phone: 08xx -> 628xx
                $phone = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $customer->phone));
                
                $waMessage = "Halo {$customer->name},\n\n";
                $waMessage .= "🎉 Akun Customer Ninama Anda telah dibuat!\n\n";
                $waMessage .= "📋 DETAIL LOGIN:\n";
                $waMessage .= "• Email: {$customer->email}\n";
                $waMessage .= "• Password: {$plainPassword}\n";
                $waMessage .= "• Login: {$loginUrl}\n\n";
                $waMessage .= "📦 PROYEK: {$project->name}\n";
                $waMessage .= "Silakan login untuk memantau progress proyek Anda.\n\n";
                $waMessage .= "Terima kasih,\nTim PT. Ninama";

                // Kirim via Fonnte API
                $response = Http::withHeaders([
                    'Authorization' => env('FONNTE_TOKEN', '')
                ])->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => $waMessage,
                    'countryCode' => '62',
                ]);

                if ($response->successful()) {
                    Log::info("WhatsApp sent to {$phone} for project {$project->name}");
                } else {
                    Log::warning("WhatsApp API response: " . $response->body());
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp to {$customer->phone}: " . $e->getMessage());
        }
    }
}
