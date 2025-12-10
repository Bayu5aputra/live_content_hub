<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::with('users')->paginate(15);
        return view('admin.organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('admin.organizations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:organizations',
            'domain' => 'nullable|string|max:255',
            'admin_email' => 'required|email',
            'admin_name' => 'required|string|max:255',
            'admin_password' => 'required|string|min:8',
        ]);

        $organization = Organization::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'domain' => $validated['domain'],
        ]);

        // Create admin user
        $user = User::firstOrCreate(
            ['email' => $validated['admin_email']],
            [
                'name' => $validated['admin_name'],
                'password' => bcrypt($validated['admin_password']),
            ]
        );

        // Attach user to organization as admin
        $organization->users()->attach($user->id, ['role' => 'admin']);

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization created successfully!');
    }

    public function edit(Organization $organization)
    {
        return view('admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug,' . $organization->id,
            'domain' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $organization->update($validated);

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization updated successfully!');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();
        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully!');
    }

    public function addUser(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,editor,viewer',
        ]);

        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'password' => bcrypt($validated['password']),
            ]
        );

        $organization->users()->syncWithoutDetaching([
            $user->id => ['role' => $validated['role']]
        ]);

        return back()->with('success', 'User added to organization!');
    }

    public function removeUser(Organization $organization, User $user)
    {
        $organization->users()->detach($user->id);
        return back()->with('success', 'User removed from organization!');
    }
}
