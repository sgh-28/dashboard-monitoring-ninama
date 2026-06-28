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
        // Ambil user internal (pegawai & marketing saja)
        $users = User::whereHas('role', function($q) {
                $q->whereIn('name', ['pegawai', 'marketing']);
            })
            ->with('role')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['pegawai', 'marketing'])->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request, WhatsAppService $whatsAppService)
    {
        $role = Role::find($request->role_id);
        $isPegawai = $role?->name === 'pegawai';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'required|string|max:20',
            'bidang' => [$isPegawai ? 'required' : 'nullable', 'in:web,internet,cctv'],
            'jabatan' => [$isPegawai ? 'required' : 'nullable', 'string', 'max:255'],
        ]);

        $rawPassword = $validated['password'];
        $validated['password'] = Hash::make($validated['password']);
        $validated['bidang'] = $isPegawai ? $validated['bidang'] : null;
        $validated['jabatan'] = $isPegawai ? $validated['jabatan'] : 'Marketing';

        $user = User::create($validated);

        try {
            $message = "*INFORMASI AKUN PEGAWAI BARU - NINAMA*\n\n" .
                       "Halo *{$user->name}*,\n\n" .
                       "Akun pegawai Anda telah berhasil dibuat oleh Admin. Berikut adalah detail informasi akun Anda untuk login ke Dashboard Ninama:\n\n" .
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
            return redirect()->route('admin.users.index')->with('success', 'Akun pegawai berhasil ditambahkan (Namun gagal mengirim notifikasi WhatsApp).');
        }

        return redirect()->route('admin.users.index')->with('success', 'Akun pegawai berhasil ditambahkan dan detail login telah dikirim melalui WhatsApp.');
    }

    public function edit(User $user)
    {
        $roles = Role::whereIn('name', ['pegawai', 'marketing'])->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $role = Role::find($request->role_id);
        $isPegawai = $role?->name === 'pegawai';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'bidang' => [$isPegawai ? 'required' : 'nullable', 'in:web,internet,cctv'],
            'jabatan' => [$isPegawai ? 'required' : 'nullable', 'string', 'max:255'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['bidang'] = $isPegawai ? $validated['bidang'] : null;
        $validated['jabatan'] = $isPegawai ? $validated['jabatan'] : 'Marketing';

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Akun pegawai berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Akun pegawai berhasil dihapus.');
    }
}
