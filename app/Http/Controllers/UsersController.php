<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UsersController extends Controller
{
    public function addUser()
    {
        return view('users/addUser');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with($value, '@sandpitnquarry.com')) {
                        $fail('The email must be a @sandpitnquarry.com address.');
                    }
                },
            ],
            'password' => 'required|string|min:8',
            'number' => 'nullable|string|max:30',
            'desc' => 'nullable|string|max:1000',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->phone = $request->number;
        $user->save();

        return redirect()->route('usersList')->with('success', 'User created successfully!');
    }
    
    public function usersGrid(Request $request)
    {
        $query = User::query();
        
        // Handle search (case-insensitive)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strtolower($request->search);
            $pattern = "%{$searchTerm}%";
            $query->where(function($q) use ($pattern) {
                $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$pattern]);
            });
        }
        
        // Paginate results for grid view
        $perPage = $request->get('per_page', 12);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return view('users/usersGrid', compact('users'));
    }

    public function usersList(Request $request)
    {
        $query = User::query();
        
        // Handle search (case-insensitive)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strtolower($request->search);
            $pattern = "%{$searchTerm}%";
            $query->where(function($q) use ($pattern) {
                $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$pattern]);
            });
        }
        
        // Handle status filter
        if ($request->has('status') && $request->status !== 'Status') {
            if ($request->status === 'Active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'Inactive') {
                $query->whereNull('email_verified_at');
            }
        }
        
        // Paginate results
        $perPage = $request->get('per_page', 10);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return view('users/usersList', compact('users'));
    }

    public function exportUsers(Request $request)
    {
        // Generate filename with current date and filters
        $filename = 'users_' . now()->format('Y-m-d_His');
        
        if ($request->has('status') && $request->status !== 'Status') {
            $filename .= '_' . strtolower($request->status);
        }
        
        $filename .= '.xlsx';
        
        return Excel::download(new UsersExport($request), $filename);
    }
    
    public function viewProfile($id = null)
    {
        if ($id) {
            $user = User::findOrFail($id);
            return view('users/viewProfile', compact('user'));
        }
        
        // If no ID provided, show current user or redirect to users list
        return redirect()->route('usersList');
    }
    
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('users/editUser', compact('user'));
    }
    
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'department' => $request->department,
            'designation' => $request->designation,
        ];
        
        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path && file_exists(storage_path('app/public/' . $user->profile_photo_path))) {
                unlink(storage_path('app/public/' . $user->profile_photo_path));
            }
            
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo_path'] = $path;
        }
        
        $user->update($userData);
        
        return redirect()->route('viewProfile', $user->id)->with('success', 'User updated successfully!');
    }
    
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Delete profile photo if exists
        if ($user->profile_photo_path && file_exists(storage_path('app/public/' . $user->profile_photo_path))) {
            unlink(storage_path('app/public/' . $user->profile_photo_path));
        }
        
        $user->delete();
        
        return redirect()->back()->with('success', 'User deleted successfully!');
    }
}
