<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AdminCustomerController extends Controller
{
    /**
     * Display list of customers (for Admin)
     */
    public function index(Request $request)
    {
        $query = User::whereHas('role', function($q) {
            $q->where('name', 'customer');
        })
        ->withCount('customerProjects')
        ->with(['customerProjects' => function($q) {
            $q->select('customer_id', 'category')
              ->groupBy('customer_id', 'category');
        }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category') && in_array($request->category, ['web', 'internet', 'cctv'])) {
            $category = $request->category;
            $query->whereHas('customerProjects', function($q) use ($category) {
                $q->where('category', $category);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'name_asc');
        switch($sort) {
            case 'name_desc':
                $query->orderByDesc('name');
                break;
            case 'projects_desc':
                $query->orderByDesc('customer_projects_count');
                break;
            case 'projects_asc':
                $query->orderBy('customer_projects_count');
                break;
            case 'created_desc':
                $query->orderByDesc('created_at');
                break;
            default:
                $query->orderBy('name');
        }

        $customers = $query->paginate(10);

        // Get category counts for each customer
        foreach($customers as $customer) {
            $customer->categories = $customer->customerProjects()
                ->select('category')
                ->distinct()
                ->pluck('category')
                ->toArray();
        }

        // ✅ SUMMARY STATS - Perbaikan: hitung distinct customer_id dengan benar
        $totalCustomers = Project::whereNotNull('customer_id')
            ->distinct()
            ->count('customer_id');

        $webCustomers = Project::where('category', 'web')
            ->whereNotNull('customer_id')
            ->distinct()
            ->count('customer_id');

        $internetCustomers = Project::where('category', 'internet')
            ->whereNotNull('customer_id')
            ->distinct()
            ->count('customer_id');

        $cctvCustomers = Project::where('category', 'cctv')
            ->whereNotNull('customer_id')
            ->distinct()
            ->count('customer_id');

        return view('admin.customers.index', compact(
            'customers',
            'totalCustomers',
            'webCustomers',
            'internetCustomers',
            'cctvCustomers'
        ));
    }

    /**
     * Show form to create new customer
     */
    public function create()
    {
        $categories = ['web', 'internet', 'cctv'];
        return view('admin.customers.create', compact('categories'));
    }

    /**
     * Store new customer
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'company' => 'required|string|max:255',
        ]);

        $role = Role::where('name', 'customer')->firstOrFail();

        $customer = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'phone' => $request->phone,
            'company' => $request->company,
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', "Customer {$request->name} berhasil ditambahkan!");
    }

    /**
     * Show form to edit customer
     */
    public function edit(User $customer)
    {
        if (!$customer->hasRole('customer')) {
            abort(404);
        }

        $categories = ['web', 'internet', 'cctv'];
        $assignedCategories = $customer->customerProjects()
            ->pluck('category')
            ->unique()
            ->toArray();

        return view('admin.customers.edit', compact('customer', 'categories', 'assignedCategories'));
    }

    /**
     * Update customer
     */
    public function update(Request $request, User $customer)
    {
        if (!$customer->hasRole('customer')) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($customer->id)],
            'phone' => 'nullable|string|max:20',
            'company' => 'required|string|max:255',
        ]);

        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', "Data customer {$customer->name} berhasil diperbarui!");
    }

    /**
     * Delete customer
     */
    public function destroy(User $customer)
    {
        if (!$customer->hasRole('customer')) {
            abort(404);
        }

        // Prevent delete if customer has ongoing projects
        if ($customer->customerProjects()->where('status', 'ongoing')->exists()) {
            return back()->with('error', 'Tidak dapat menghapus customer yang memiliki proyek aktif.');
        }

        $name = $customer->name;
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', "Customer {$name} berhasil dihapus.");
    }
}