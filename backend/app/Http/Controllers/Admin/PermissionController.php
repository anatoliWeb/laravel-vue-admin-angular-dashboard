<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

/**
 * PermissionController
 *
 * Handles CRUD operations for permissions in admin panel.
 *
 * Permissions define what actions users are allowed to perform
 * (e.g. manage_users, manage_tokens).
 */
class PermissionController extends Controller
{
    /**
     * Display list of all permissions.
     */
    public function index()
    {
        // Get all permissions from database
        $permissions = Permission::all();

        // Return list view
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show form to create new permission.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store new permission in database.
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        // Create new permission
        Permission::create([
            'name' => $request->name,
            // For now description = name (можна покращити пізніше)
            'description' => $request->name,
        ]);

        // Redirect back to list with success message
        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission created');
    }

    /**
     * Show edit form for selected permission.
     */
    public function edit($id)
    {
        // Find permission or fail (404)
        $permission = Permission::findOrFail($id);

        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update existing permission.
     */
    public function update(Request $request, $id)
    {
        // Find permission
        $permission = Permission::findOrFail($id);

        // Validate (ignore current permission id for unique rule)
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);

        // Update permission data
        $permission->update([
            'name' => $request->name,
            'description' => $request->name,
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission updated');
    }

    /**
     * Delete permission.
     */
    public function destroy($id)
    {
        // Delete permission (will fail if not found)
        Permission::findOrFail($id)->delete();

        return back()->with('success', 'Permission deleted');
    }
}
