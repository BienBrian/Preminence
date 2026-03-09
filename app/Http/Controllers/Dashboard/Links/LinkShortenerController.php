<?php

namespace App\Http\Controllers\Dashboard\Links;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\ShortLink;
use App\Models\ShortLinkClick;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class LinkShortenerController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:Manage Links']);
    }

    public function index()
    {
        return view('dashboard.links.index');
    }

    public function datatable(Request $request)
    {
        $query = ShortLink::with('creator')
            ->orderBy('created_at', 'DESC');

        return DataTables::of($query)
            ->addColumn('short_url', function ($row) {
                return '<a href="' . $row->short_url . '" target="_blank" class="text-primary">' . $row->short_url . '</a>';
            })
            ->addColumn('original_url_display', function ($row) {
                $url = strlen($row->original_url) > 60 
                    ? substr($row->original_url, 0, 60) . '...' 
                    : $row->original_url;
                return '<a href="' . $row->original_url . '" target="_blank" title="' . e($row->original_url) . '">' . $url . '</a>';
            })
            ->addColumn('status', function ($row) {
                if (!$row->is_active) {
                    return '<span class="badge bg-secondary">Disabled</span>';
                }
                if ($row->isExpired()) {
                    return '<span class="badge bg-danger">Expired</span>';
                }
                return '<span class="badge bg-success">Active</span>';
            })
            ->addColumn('created_by_name', function ($row) {
                return $row->creator ? $row->creator->firstname . ' ' . $row->creator->lastname : '-';
            })
            ->addColumn('expires', function ($row) {
                if (!$row->expires_at) {
                    return '<span class="text-muted">Never</span>';
                }
                if ($row->isExpired()) {
                    return '<span class="text-danger">' . $row->expires_at->format('d M, Y') . '</span>';
                }
                return $row->expires_at->format('d M, Y');
            })
            ->addColumn('actions', function ($row) {
                $actions = '<button class="btn btn-sm btn-info btn-view-stats mr-1" data-id="' . $row->id . '" title="View Stats">' .
                    '<i class="fas fa-chart-bar"></i></button>';
                
                $actions .= '<button class="btn btn-sm btn-primary btn-copy-url mr-1" data-url="' . $row->short_url . '" title="Copy URL">' .
                    '<i class="fas fa-copy"></i></button>';
                
                $actions .= '<button class="btn btn-sm btn-warning btn-edit-link mr-1" data-id="' . $row->id . '" title="Edit">' .
                    '<i class="fas fa-edit"></i></button>';
                
                $actions .= '<button class="btn btn-sm btn-danger btn-delete-link" data-id="' . $row->id . '" title="Delete">' .
                    '<i class="fas fa-trash"></i></button>';
                
                return $actions;
            })
            ->rawColumns(['short_url', 'original_url_display', 'status', 'expires', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'original_url' => 'required|url|max:2048',
            'title' => 'nullable|string|max:255',
            'custom_code' => 'nullable|string|max:20|alpha_num|unique:short_links,short_code',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        // Generate short code
        $shortCode = $request->filled('custom_code') 
            ? $request->custom_code 
            : ShortLink::generateUniqueCode();

        // Check if custom code is already taken
        if ($request->filled('custom_code') && ShortLink::where('short_code', $shortCode)->exists()) {
            return response()->json(['errors' => ['custom_code' => ['This short code is already taken.']]], 400);
        }

        $link = ShortLink::create([
            'tenant_id' => config('app.tenant_id'),
            'short_code' => $shortCode,
            'original_url' => $request->original_url,
            'title' => $request->title,
            'created_by' => Auth::id(),
            'expires_at' => $request->expires_at,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => 'Short link created successfully!',
            'link' => $link,
            'short_url' => $link->short_url,
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:short_links,id',
            'original_url' => 'required|url|max:2048',
            'title' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $link = ShortLink::findOrFail($request->id);
        
        // Only allow editing if user created the link or has superadmin permissions
        if ($link->created_by != Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            return response()->json(['error' => 'You do not have permission to edit this link.'], 403);
        }

        $link->update([
            'original_url' => $request->original_url,
            'title' => $request->title,
            'expires_at' => $request->expires_at,
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'success' => 'Short link updated successfully!',
            'link' => $link,
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:short_links,id']);

        $link = ShortLink::findOrFail($request->id);
        
        // Only allow deleting if user created the link or has superadmin permissions
        if ($link->created_by != Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            return response()->json(['error' => 'You do not have permission to delete this link.'], 403);
        }

        $link->delete();

        return response()->json(['success' => 'Short link deleted successfully!']);
    }

    public function stats(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:short_links,id']);

        $link = ShortLink::withCount('clicks')->findOrFail($request->id);

        // Get click stats by date (last 30 days)
        $clicksByDate = ShortLinkClick::where('short_link_id', $link->id)
            ->where('clicked_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get top referrers
        $topReferrers = ShortLinkClick::where('short_link_id', $link->id)
            ->whereNotNull('referer')
            ->selectRaw('referer, COUNT(*) as count')
            ->groupBy('referer')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Get recent clicks
        $recentClicks = ShortLinkClick::where('short_link_id', $link->id)
            ->orderByDesc('clicked_at')
            ->limit(20)
            ->get();

        return response()->json([
            'link' => $link,
            'clicks_by_date' => $clicksByDate,
            'top_referrers' => $topReferrers,
            'recent_clicks' => $recentClicks,
        ]);
    }

    /**
     * Public redirect method
     */
    public function redirect($code)
    {
        $link = ShortLink::where('short_code', $code)->first();

        if (!$link || !$link->is_active || $link->isExpired()) {
            abort(404, 'This link is invalid or has expired.');
        }

        // Record the click
        $this->recordClick($link);

        // Increment click count
        $link->increment('click_count');

        return redirect()->away($link->original_url);
    }

    private function recordClick(ShortLink $link): void
    {
        try {
            ShortLinkClick::create([
                'short_link_id' => $link->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referer' => request()->header('referer'),
                'clicked_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't break the redirect if logging fails
            \Log::warning('Failed to record short link click: ' . $e->getMessage());
        }
    }
}
