<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    public function index()
    {
        // Ambil user internal (pegawai, project manager, dan marketing)
        $users = User::whereHas('role', function($q) {
                $q->whereIn('name', ['pegawai', 'project_manager', 'marketing', 'direktur']);
            })
            ->with('role')
            ->orderBy('name')
            ->get();

        $directorCount = User::whereHas('role', fn($q) => $q->where('name', 'direktur'))->count();

        return view('admin.users.index', compact('users', 'directorCount'));
    }

    public function create()
    {
        $roleNames = ['pegawai', 'project_manager', 'marketing', 'direktur'];

        if (User::whereHas('role', fn($q) => $q->where('name', 'marketing'))->exists()) {
            $roleNames = array_values(array_diff($roleNames, ['marketing']));
        }

        if (User::whereHas('role', fn($q) => $q->where('name', 'direktur'))->exists()) {
            $roleNames = array_values(array_diff($roleNames, ['direktur']));
        }

        $roles = Role::whereIn('name', $roleNames)->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request, WhatsAppService $whatsAppService)
    {
        $role = Role::find($request->role_id);
        $isPegawai = $role?->name === 'pegawai';
        $isProjectManager = $role?->name === 'project_manager';
        $needsBidang = $isPegawai || $isProjectManager;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'required|string|max:20',
            'bidang' => [$needsBidang ? 'required' : 'nullable', 'in:web,internet,cctv'],
            'jabatan' => [$isPegawai ? 'required' : 'nullable', 'string', 'max:255'],
        ]);

        if ($role?->name === 'marketing' && User::whereHas('role', fn($q) => $q->where('name', 'marketing'))->exists()) {
            return back()
                ->withInput()
                ->withErrors(['role_id' => 'Akun marketing hanya boleh 1 akun untuk semua bidang.']);
        }

        if ($role?->name === 'direktur' && User::whereHas('role', fn($q) => $q->where('name', 'direktur'))->exists()) {
            return back()
                ->withInput()
                ->withErrors(['role_id' => 'Akun direktur hanya boleh 1 akun.']);
        }

        $rawPassword = $validated['password'];
        $validated['password'] = Hash::make($validated['password']);
        $validated['bidang'] = $needsBidang ? $validated['bidang'] : null;
        $validated['jabatan'] = match (true) {
            $isProjectManager => 'Project Management',
            $isPegawai => $validated['jabatan'],
            $role?->name === 'marketing' => 'Marketing',
            default => null,
        };

        $user = User::create($validated);

        try {
            $message = "*INFORMASI AKUN INTERNAL BARU - NINAMA*\n\n" .
                       "Halo *{$user->name}*,\n\n" .
                       "Akun Anda telah berhasil dibuat oleh Admin. Berikut adalah detail informasi akun Anda untuk login ke Dashboard Ninama:\n\n" .
                       "📧 *Email:* {$user->email}\n" .
                       "🔑 *Password:* {$rawPassword}\n" .
                       "💼 *Jabatan:* {$user->jabatan}\n" .
                       "🛠️ *Bidang:* " . ($user->bidang ?? '-') . "\n\n" .
                       "Silakan login melalui tautan berikut:\n" .
                       config('app.url') . "/login\n\n" .
                       "_Harap segera ganti password Anda setelah berhasil masuk demi keamanan data._\n" .
                       "_Pesan otomatis dari sistem Ninama_";

            $whatsAppService->sendMessage($user->phone, $message);
            Log::info("WhatsApp akun kredensial terkirim ke {$user->phone}");
        } catch (\Exception $e) {
            Log::error("Gagal kirim WhatsApp kredensial ke {$user->phone}: " . $e->getMessage());
            return redirect()->route('admin.users.index')->with('success', 'Akun internal berhasil ditambahkan (Namun gagal mengirim notifikasi WhatsApp).');
        }

        return redirect()->route('admin.users.index')->with('success', 'Akun internal berhasil ditambahkan dan detail login telah dikirim melalui WhatsApp.');
    }

    public function edit(User $user)
    {
        $roles = Role::whereIn('name', ['pegawai', 'project_manager', 'marketing', 'direktur'])->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $role = Role::find($request->role_id);
        $isPegawai = $role?->name === 'pegawai';
        $isProjectManager = $role?->name === 'project_manager';
        $needsBidang = $isPegawai || $isProjectManager;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'required|string|max:20',
            'bidang' => [$needsBidang ? 'required' : 'nullable', 'in:web,internet,cctv'],
            'jabatan' => [$isPegawai ? 'required' : 'nullable', 'string', 'max:255'],
        ]);

        if ($role?->name === 'marketing' && User::where('id', '!=', $user->id)->whereHas('role', fn($q) => $q->where('name', 'marketing'))->exists()) {
            return back()
                ->withInput()
                ->withErrors(['role_id' => 'Akun marketing hanya boleh 1 akun untuk semua bidang.']);
        }

        if ($role?->name === 'direktur' && User::where('id', '!=', $user->id)->whereHas('role', fn($q) => $q->where('name', 'direktur'))->exists()) {
            return back()
                ->withInput()
                ->withErrors(['role_id' => 'Akun direktur hanya boleh 1 akun.']);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['bidang'] = $needsBidang ? $validated['bidang'] : null;
        $validated['jabatan'] = match (true) {
            $isProjectManager => 'Project Management',
            $isPegawai => $validated['jabatan'],
            $role?->name === 'marketing' => 'Marketing',
            default => null,
        };

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Akun internal berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if (($user->role?->name ?? '') === 'direktur' && User::whereHas('role', fn($q) => $q->where('name', 'direktur'))->count() <= 1) {
            return back()->with('error', 'Akun direktur utama tidak dapat dihapus karena minimal harus ada 1 akun direktur.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Akun internal berhasil dihapus.');
    }
}
