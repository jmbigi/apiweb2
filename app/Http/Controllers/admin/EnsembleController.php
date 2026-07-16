<?php

namespace App\Http\Controllers\admin;

use DataTables;
use App\Models\Ensemble;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EnsembleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Ensemble::with('owner')->select('*');
            $counter = 1;
            return Datatables::of($data)
                ->filter(function ($instance) use ($request) {
                    if ($request->has('search') && !is_null($request->get('search')['value'])) {
                        $regex = $request->get('search')['value'];
                        $instance->where(function ($q) use ($regex) {
                            $q->where('name', 'like', '%' . $regex . '%')
                              ->orWhere('cif', 'like', '%' . $regex . '%');
                        });
                    }
                })
                ->addColumn('index', function ($row) use (&$counter) {
                    return $row->id;
                })
                ->addColumn('owner_name', function ($row) {
                    return $row->owner ? $row->owner->name : 'N/A';
                })
                ->addColumn('member_count', function ($row) {
                    return $row->members()->count();
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->status
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('ensemble.show', $row->id) . '" class="btn btn-sm btn-clean btn-icon mr-2" title="View"><i class="la la-eye"></i></a>';
                    $btn .= '<a href="' . route('ensemble.edit', $row->id) . '" class="btn btn-sm btn-clean btn-icon mr-2" title="Edit"><i class="la la-edit"></i></a>';
                    $btn .= '<form action="' . route('ensemble.destroy', $row->id) . '" method="POST" style="display:inline" onsubmit="return confirm(\'Delete this ensemble?\')">'
                        . csrf_field()
                        . method_field('DELETE')
                        . '<button type="submit" class="btn btn-sm btn-clean btn-icon" title="Delete"><i class="la la-trash"></i></button>'
                        . '</form>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $ensembles = Ensemble::with('owner')->latest()->paginate(20);
        return view('admin.ensemble.index')->with('ensembles', $ensembles);
    }

    public function show($id)
    {
        $ensemble = Ensemble::with(['owner', 'members', 'folders', 'rehearsals'])->findOrFail($id);
        return view('admin.ensemble.show', compact('ensemble'));
    }

    public function create()
    {
        $users = User::where('status', 1)->get();
        return view('admin.ensemble.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cif' => 'nullable|string|max:20|unique:ensembles,cif',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'status' => 'boolean',
        ]);

        Ensemble::create($request->all());

        return redirect()->route('ensemble.index')
            ->with('success', 'Ensemble created successfully.');
    }

    public function edit($id)
    {
        $ensemble = Ensemble::findOrFail($id);
        $users = User::where('status', 1)->get();
        return view('admin.ensemble.edit', compact('ensemble', 'users'));
    }

    public function update(Request $request, $id)
    {
        $ensemble = Ensemble::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'cif' => 'nullable|string|max:20|unique:ensembles,cif,' . $id,
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'status' => 'boolean',
        ]);

        $ensemble->update($request->all());

        return redirect()->route('ensemble.index')
            ->with('success', 'Ensemble updated successfully.');
    }

    public function destroy($id)
    {
        $ensemble = Ensemble::findOrFail($id);
        $ensemble->members()->detach();
        $ensemble->folders()->delete();
        $ensemble->rehearsals()->delete();
        $ensemble->scores()->update(['ensemble_id' => null]);
        $ensemble->delete();

        return redirect()->route('ensemble.index')
            ->with('success', 'Ensemble deleted successfully.');
    }

    public function getMembers(Request $request, $id)
    {
        $ensemble = Ensemble::findOrFail($id);
        $members = $ensemble->members()->withPivot('role', 'status')->get();
        return response()->json($members);
    }

    public function updateMemberStatus(Request $request, $id, $userId)
    {
        $ensemble = Ensemble::findOrFail($id);
        $ensemble->members()->updateExistingPivot($userId, [
            'status' => $request->boolean('status'),
        ]);
        return redirect()->back()->with('success', 'Member status updated.');
    }

    public function removeMember($id, $userId)
    {
        $ensemble = Ensemble::findOrFail($id);
        $ensemble->members()->detach($userId);
        return redirect()->back()->with('success', 'Member removed.');
    }
}
