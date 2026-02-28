<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use DB;

class SettingsLookupController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Payment Settings']);
    }

    public function index()
    {
        return view('dashboard.settings.lookups');
    }

    // ========== Sunday School Classes ==========

    public function getSundaySchoolClasses(Request $request)
    {
        $query = DB::table('sunday_school_classes')->orderBy('name', 'ASC');

        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                if ($request->search) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%');
                }
            })->addColumn('usage_count', function ($row) {
                return DB::table('children')
                    ->where('sunday_school_class', $row->name)
                    ->count();
            })->addColumn('action', function ($row) {
                return '<div class="text-end text-right">' .
                    '<span class="d-none item-id">' . $row->id . '</span>' .
                    '<span class="d-none item-name">' . e($row->name) . '</span>' .
                    '<button class="btn btn-primary btn-sm btn-edit-class" data-toggle="modal" data-target="#classModal">' .
                    '<i class="fas fa-edit"></i></button> ' .
                    '<button class="btn btn-danger btn-sm btn-delete-class">' .
                    '<i class="fas fa-trash"></i></button>' .
                    '</div>';
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function addSundaySchoolClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:0',
            'name' => 'required|string|max:100|unique:sunday_school_classes,name,' . $request->id,
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        if ($request->id > 0) {
            $old = DB::table('sunday_school_classes')->where('id', $request->id)->first();
            DB::table('sunday_school_classes')->where('id', $request->id)->update([
                'name' => $request->name,
                'updated_at' => now(),
            ]);
            // Update children records that reference the old name
            if ($old) {
                DB::table('children')
                    ->where('sunday_school_class', $old->name)
                    ->update(['sunday_school_class' => $request->name]);
            }
        } else {
            DB::table('sunday_school_classes')->insert([
                'name' => $request->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => 'Sunday school class saved successfully!']);
    }

    public function deleteSundaySchoolClass($id)
    {
        $class = DB::table('sunday_school_classes')->where('id', $id)->first();
        if (!$class) {
            return response()->json(['error' => 'Class not found'], 404);
        }
        $inUse = DB::table('children')->where('sunday_school_class', $class->name)->exists();
        if ($inUse) {
            return response()->json(['error' => 'Cannot delete: this class is in use by children records.'], 400);
        }
        DB::table('sunday_school_classes')->where('id', $id)->delete();
        return response()->json(['success' => 'Class deleted successfully!']);
    }

    public function searchSundaySchoolClasses(Request $request)
    {
        $results = DB::table('sunday_school_classes')
            ->where('name', 'LIKE', '%' . $request->q . '%')
            ->orderBy('name', 'asc')
            ->limit(20)
            ->get();

        return response()->json($results);
    }

    // ========== Residences ==========

    public function getResidences(Request $request)
    {
        $query = DB::table('residences')->orderBy('name', 'ASC');

        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                if ($request->search) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%');
                }
            })->addColumn('usage_count', function ($row) {
                $childCount = DB::table('children')->where('residence', $row->name)->count();
                $scontactCount = DB::table('scontacts')->where('resident', $row->name)->count();
                return $childCount + $scontactCount;
            })->addColumn('action', function ($row) {
                return '<div class="text-end text-right">' .
                    '<span class="d-none item-id">' . $row->id . '</span>' .
                    '<span class="d-none item-name">' . e($row->name) . '</span>' .
                    '<button class="btn btn-primary btn-sm btn-edit-residence" data-toggle="modal" data-target="#residenceModal">' .
                    '<i class="fas fa-edit"></i></button> ' .
                    '<button class="btn btn-danger btn-sm btn-delete-residence">' .
                    '<i class="fas fa-trash"></i></button>' .
                    '</div>';
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function addResidence(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:0',
            'name' => 'required|string|max:100|unique:residences,name,' . $request->id,
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        if ($request->id > 0) {
            $old = DB::table('residences')->where('id', $request->id)->first();
            DB::table('residences')->where('id', $request->id)->update([
                'name' => $request->name,
                'updated_at' => now(),
            ]);
            // Update records that reference the old name
            if ($old) {
                DB::table('children')
                    ->where('residence', $old->name)
                    ->update(['residence' => $request->name]);
                DB::table('scontacts')
                    ->where('resident', $old->name)
                    ->update(['resident' => $request->name]);
            }
        } else {
            DB::table('residences')->insert([
                'name' => $request->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => 'Residence saved successfully!']);
    }

    public function deleteResidence($id)
    {
        $residence = DB::table('residences')->where('id', $id)->first();
        if (!$residence) {
            return response()->json(['error' => 'Residence not found'], 404);
        }
        $inUse = DB::table('children')->where('residence', $residence->name)->exists()
            || DB::table('scontacts')->where('resident', $residence->name)->exists();
        if ($inUse) {
            return response()->json(['error' => 'Cannot delete: this residence is in use.'], 400);
        }
        DB::table('residences')->where('id', $id)->delete();
        return response()->json(['success' => 'Residence deleted successfully!']);
    }

    public function searchResidences(Request $request)
    {
        $results = DB::table('residences')
            ->where('name', 'LIKE', '%' . $request->q . '%')
            ->orderBy('name', 'asc')
            ->limit(20)
            ->get();

        return response()->json($results);
    }
}
