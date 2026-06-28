<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeeTaskController extends Controller
{
    /**
     * Display task list for the logged-in employee/marketing.
     */
    public function index(Request $request)
    {
        $query = ProjectTask::where('assigned_to', Auth::id())
            ->with(['project', 'division']);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderByRaw("CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'ongoing' THEN 2 
                WHEN status = 'done' THEN 3 
                ELSE 4 END")
            ->orderBy('deadline')
            ->paginate(10);
        
        return view('employee.tasks.index', compact('tasks'));
    }

    /**
     * Show task detail (read-only).
     */
    public function show(ProjectTask $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            abort(403);
        }
        return view('employee.tasks.show', compact('task'));
    }

    /**
     * Show form to submit task completion.
     */
    public function submitForm(ProjectTask $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses task ini.');
        }
        return view('employee.tasks.submit', compact('task'));
    }

    /**
     * Handle task submission with proof image.
     */
    public function submit(Request $request, ProjectTask $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Jika hanya update status (tombol Mulai Kerjakan)
        if ($request->has('status') && $request->status === 'ongoing' && !$request->has('completion_notes')) {
            $task->update([
                'status' => 'ongoing',
                'progress' => 50,
            ]);
            return redirect()->route('employee.dashboard')
                ->with('success', '🚀 Status task diubah menjadi "Dalam Pengerjaan"!');
        }

        // Submit selesai (dengan bukti)
        $validated = $request->validate([
            'completion_notes' => 'required|string|max:1000',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'completion_notes.required' => 'Keterangan pengerjaan wajib diisi.',
            'proof_image.required' => 'Bukti pengerjaan (foto) wajib diupload.',
        ]);

        if ($request->hasFile('proof_image')) {
            if ($task->proof_image && Storage::disk('public')->exists($task->proof_image)) {
                Storage::disk('public')->delete($task->proof_image);
            }
            $path = $request->file('proof_image')->store('task_proofs', 'public');
            $validated['proof_image'] = $path;
        }

        $validated['status'] = 'done';
        $validated['completed_at'] = now();
        $validated['progress'] = 100;
        
        $task->update($validated);

        return redirect()->route('employee.dashboard')
            ->with('success', '📤 Laporan pengerjaan berhasil dikirim!');
    }
}