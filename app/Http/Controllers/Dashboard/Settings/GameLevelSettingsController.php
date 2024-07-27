<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Controller;
use App\Models\AuctionTime;
use App\Models\GameLevel;
use App\Models\TimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class GameLevelSettingsController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth', 'verified']);
    $this->middleware(['permission:View Game Level Settings']);
    }
    public function index()
    {
        return view('dashboard.settings.game_level_settings');
    }
    public function addGameLevel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:0',
            'name' => 'required|string|unique:game_levels,name,'.$request->id,
            'win'=>'numeric|min:0',
            'lose' => 'numeric|min:0',
            'status' => 'required|integer|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $gameLevel = new GameLevel;
        if ($request->id > 0) {
            $gameLevel = GameLevel::findOrFail($request->id);
        }
        $gameLevel->name = $request->name;
        $gameLevel->win_percentage = $request->win;
        $gameLevel->lose_percentage = $request->lose;
        $gameLevel->status = $request->status;
        if ($gameLevel->save()) {
            return response()->json(['success' => "Game Level updated successfully!"]);
        } else {
            return response()->json(['error' => 'Unable to update Game Level'], 401);
        }
    }
    public function getGameLevels(Request $request)
    {
        return DataTables::of(GameLevel::orderBy('name', 'ASC'))
            ->filter(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'LIKE', $request->search_from . '%');
                });
            })->editColumn('win', function ($row) {
                return number_format($row->win_percentage, 2,'.',',').'%';
            })->editColumn('lose', function ($row) {
                return number_format($row->lose_percentage, 2,'.',',').'%';
            })->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->diffForHumans();
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>' .
                    '<span class="d-none name">' . $row->name . '</span>' .
                    '<span class="d-none win">' . $row->win_percentage . '</span>' .
                    '<span class="d-none lose">' . $row->lose_percentage . '</span>' .
                    '<span class="d-none status">' . $row->status . '</span>
                    <button class="btn-edit btn" data-toggle="modal" data-target="#userModal"><i class="fas fa-edit"></i></button> ';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }
}
