<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Role;
use App\Models\ProjectDivision;
use App\Services\MilestoneService;
use App\Services\ProjectProgressService;
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
        $query = Project::with(['customer', 'divisions.tasks', 'tasks'])->whereIn('status', ['ongoing', 'done']);
        
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
        $statuses = ['ongoing'];
        $customers = User::whereHas('role', fn($q) => $q->where('name', 'customer'))->get();
        
        return view('admin.projects.create', compact('categories', 'statuses', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:web,internet,cctv',
            'status' => 'nullable|in:ongoing',
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
                'new_customer_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\.\'-]+$/u'],
                'new_customer_email' => 'required|email|unique:users,email',
                'new_customer_password' => 'required|min:6|confirmed',
            ], [
                'new_customer_name.regex' => 'Nama PIC hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.',
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
            'status' => 'ongoing',
            'client_name' => $request->client_name ?? $customer->name,
            'customer_id' => $customer->id,
            'address' => $request->address,
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
            'sla' => $request->sla ?? 100,
            'rejection_reason' => null,
            'progress' => 0,
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
        $statuses = [$project->status === 'done' ? 'done' : 'ongoing'];
        $projectDivisions = $project->divisions->pluck('name')->toArray();
        $customers = User::whereHas('role', fn($q) => $q->where('name', 'customer'))->get();
        
        return view('admin.projects.edit', compact('project', 'categories', 'statuses', 'projectDivisions', 'customers'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:web,internet,cctv',
            'status' => 'nullable|string',
            'customer_id' => 'required|exists:users,id',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'start_date' => 'nullable|date',
            'sla' => 'nullable|integer|min:0|max:100',
            'divisions' => 'nullable|array',
            'divisions.*' => 'string|max:100',
        ]);

        $customer = User::findOrFail($request->customer_id);

        if ($project->status === 'done') {
            return back()
                ->withInput()
                ->with('error', 'Proyek yang sudah selesai tidak dapat diubah kembali oleh Admin.');
        }

        if ($project->category !== $request->category && $project->tasks()->exists()) {
            return back()
                ->withInput()
                ->withErrors(['category' => 'Kategori proyek tidak dapat diganti karena proyek sudah memiliki task.']);
        }

        $invalidTasks = $this->tasksOutsideProjectPeriod(
            $project,
            $request->start_date,
            $request->deadline
        );

        if ($invalidTasks->isNotEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'deadline' => 'Periode proyek tidak dapat diubah karena ada task di luar periode baru: '
                        . $invalidTasks->pluck('title')->implode(', '),
                ]);
        }
        
        $project->update([
            'name' => $request->name,
            'category' => $request->category,
            'status' => 'ongoing',
            'client_name' => $request->client_name ?? $customer->name,
            'customer_id' => $customer->id,
            'address' => $request->address,
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
            'sla' => $request->sla ?? 100,
            'rejection_reason' => null,
            'progress' => $project->progress,
        ]);

        if ($request->has('divisions')) {
            $divisionError = $this->syncProjectDivisions($project, $request->divisions ?? []);

            if ($divisionError) {
                return back()->withInput()->withErrors(['divisions' => $divisionError]);
            }
        }

        ProjectProgressService::syncProject($project);

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

    public function show(Project $project)
    {
        $project->load([
            'phases' => fn($q) => $q->orderBy('phase_order'),
            'customer',
            'completedBy',
            'divisions.tasks.assignee',
            'tasks.division',
            'tasks.assignee',
            'tasks.verifier',
        ]);

        $overallProgress = $project->overall_progress;
        $slaStatus = $project->project_sla_status;
        $slaSummary = $project->sla_summary;
        $timelineData = app(MilestoneService::class)->buildProjectTimeline($project);
        $canVerifyTasks = false;

        return view('projects.detail', compact('project', 'overallProgress', 'slaStatus', 'slaSummary', 'timelineData', 'canVerifyTasks'));
    }

    /**
     * ✅ BARU: API Endpoint untuk mengambil divisi berdasarkan kategori (digunakan oleh JS)
     */
    public function getDivisionsByCategory($category)
    {
        $divisions = self::CATEGORY_DIVISIONS[$category] ?? [];
        return response()->json($divisions);
    }

    private function tasksOutsideProjectPeriod(Project $project, ?string $startDate, ?string $deadline)
    {
        if (!$startDate || !$deadline) {
            return collect();
        }

        $projectStart = \Carbon\Carbon::parse($startDate)->startOfDay();
        $projectEnd = \Carbon\Carbon::parse($deadline)->startOfDay();

        return $project->tasks()
            ->get()
            ->filter(function ($task) use ($projectStart, $projectEnd) {
                $taskStart = $task->planned_start_date
                    ? \Carbon\Carbon::parse($task->planned_start_date)->startOfDay()
                    : null;
                $taskEnd = $task->deadline
                    ? \Carbon\Carbon::parse($task->deadline)->startOfDay()
                    : null;

                return ($taskStart && ($taskStart->lt($projectStart) || $taskStart->gt($projectEnd)))
                    || ($taskEnd && ($taskEnd->lt($projectStart) || $taskEnd->gt($projectEnd)));
            });
    }

    private function syncProjectDivisions(Project $project, array $selectedDivisions): ?string
    {
        $selectedDivisions = collect($selectedDivisions)
            ->map(fn($division) => trim((string) $division))
            ->filter()
            ->unique()
            ->values();

        foreach ($selectedDivisions as $divisionName) {
            ProjectDivision::firstOrCreate(
                ['project_id' => $project->id, 'name' => $divisionName],
                ['progress' => 0]
            );
        }

        $divisionsToRemove = $project->divisions()
            ->whereNotIn('name', $selectedDivisions->all())
            ->get();

        $usedDivisions = $divisionsToRemove
            ->filter(fn(ProjectDivision $division) => $division->tasks()->exists());

        if ($usedDivisions->isNotEmpty()) {
            return 'Divisi berikut masih memiliki task dan tidak boleh dihapus: '
                . $usedDivisions->pluck('name')->implode(', ')
                . '. Hapus atau pindahkan task terlebih dahulu.';
        }

        foreach ($divisionsToRemove as $division) {
            $division->delete();
        }

        return null;
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
