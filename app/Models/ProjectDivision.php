<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'progress',
    ];

    /**
     * Relasi ke Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi ke Tasks (WAJIB specify foreign key: division_id)
     */
    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'division_id'); // ✅ Explicit foreign key
    }

    /**
     * Get completed tasks count
     */
    public function getCompletedTasksCountAttribute()
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    /**
     * Get total tasks count
     */
    public function getTotalTasksCountAttribute()
    {
        return $this->tasks()->count();
    }
}