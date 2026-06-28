<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * ✅ PENTING: Selalu eager load relasi 'role'
     * Mencegah error "role is null" di middleware & controller
     */
    protected $with = ['role'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'company',
        'bidang',      // ✅ BARU: Web/Internet/CCTV (untuk Pegawai)
        'jabatan',     // ✅ BARU: Jabatan custom (untuk Pegawai & Marketing)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi: User belongs to Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Helper: Cek apakah user memiliki role tertentu
     */
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }

    public function isDirektur()
    {
        return $this->hasRole('direktur');
    }

    public function isPegawai()
    {
        return $this->hasRole('pegawai');
    }

    public function isCustomer()
    {
        return $this->hasRole('customer');
    }

    public function isMarketing()
    {
        return $this->hasRole('marketing');
    }

    /**
     * Relasi: Customer memiliki banyak Project
     */
    public function customerProjects()
    {
        return $this->hasMany(Project::class, 'customer_id');
    }

    /**
     * Relasi: Employee memiliki banyak Task
     */
    public function assignedTasks()
    {
        return $this->hasMany(ProjectTask::class, 'employee_id');
    }

    /**
     * Helper: Get nama bidang (untuk Pegawai)
     */
    public function getBidangNameAttribute()
    {
        $bidangMap = [
            'web' => 'Web & Aplikasi',
            'internet' => 'Internet & Jaringan',
            'cctv' => 'CCTV',
        ];

        return $bidangMap[$this->bidang] ?? '-';
    }
}