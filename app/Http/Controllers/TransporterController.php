<?php

namespace App\Http\Controllers;

use App\Models\Transporter;
use App\Models\User;
use App\Exports\TransportersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TransporterController extends Controller
{
    public function addTransporter()
    {
        return view('transporters/addTransporter');
    }

    public function transportersList(Request $request)
{
    $query = Transporter::with(['owner']);

    // Filter by search term using parameter binding to prevent SQL injection (case-insensitive)
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $escapedSearchTerm = strtolower(addcslashes($searchTerm, '%_'));
        $pattern = '%' . $escapedSearchTerm . '%';
        $query->where(function ($q) use ($pattern) {
            $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
              ->orWhereRaw('LOWER(registration_number) LIKE ?', [$pattern]);
        });
    }

    // Filter by type
    if ($request->filled('type') && $request->type != 'Type') {
        $query->where('type', $request->type);
    }

    // Cast pagination parameter to integer to prevent injection
    $perPage = (int) ($request->per_page ?? 10);
    $perPage = min(max($perPage, 5), 100);

    $transporters = $query->orderBy('id', 'desc')->paginate($perPage);

    // Get all transporter types for filter dropdown
    $types = DB::table('company_types')->pluck('type');

    return view('transporters/transportersList', compact('transporters', 'types'));
}

    public function exportTransporters(Request $request)
    {
        // Generate filename with current date and filters
        $filename = 'transporters_' . now()->format('Y-m-d_His');
        
        if ($request->has('type') && $request->type != 'Type') {
            $filename .= '_' . str_replace(' ', '_', strtolower($request->type));
        }
        
        $filename .= '.xlsx';
        
        return Excel::download(new TransportersExport($request), $filename);
    }
    
    public function viewTransporter($id)
    {
        $transporter = Transporter::with('owner')->findOrFail($id);
        return view('transporters/viewTransporter', compact('transporter'));
    }
    
    public function editTransporter($id)
    {
        $transporter = Transporter::with('owner')->findOrFail($id);
        // Get potential owners (users)
        $users = User::all();
        // Get company types
        $types = DB::table('company_types')->pluck('type');
        
        return view('transporters/editTransporter', compact('transporter', 'users', 'types'));
    }
    
    public function updateTransporter(Request $request, $id)
    {
        $transporter = Transporter::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255|unique:transporters,registration_number,' . $transporter->id,
            'type' => 'required|exists:company_types,type',
            'user_id' => 'required|exists:users,id',
        ]);
        
        $transporter->update([
            'name' => $request->name,
            'registration_number' => $request->registration_number,
            'type' => $request->type,
            'user_id' => $request->user_id,
        ]);
        
        return redirect()->route('transportersList')->with('success', 'Transporter updated successfully');
    }
    
    public function deleteTransporter($id)
    {
        $transporter = Transporter::findOrFail($id);
        $transporter->delete();
        
        return redirect()->route('transportersList')->with('success', 'Transporter deleted successfully');
    }
    
    public function storeTransporter(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255|unique:transporters',
            'type' => 'required|exists:company_types,type',
            'user_id' => 'required|exists:users,id',
        ]);
        
        $transporter = Transporter::create([
            'name' => $request->name,
            'registration_number' => $request->registration_number,
            'type' => $request->type,
            'user_id' => $request->user_id,
            'creator_id' => $request->user_id, // Using user_id as creator_id
        ]);
        
        return redirect()->route('transportersList')->with('success', 'Transporter created successfully');
    }
}