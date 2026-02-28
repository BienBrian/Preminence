<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class IntegrationsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Payment Settings']);
    }

    public function index()
    {
        return view('dashboard.settings.integrations');
    }

    public function datatable(Request $request)
    {
        $query = Integration::query()->orderBy('type')->orderBy('name');

        if ($request->filled('type') && $request->type !== 'all') {
            $query->ofType($request->type);
        }

        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                if ($request->filled('search')) {
                    $s = $request->search;
                    $q->where(function ($qq) use ($s) {
                        $qq->where('name', 'LIKE', "%{$s}%")
                           ->orWhere('provider', 'LIKE', "%{$s}%")
                           ->orWhere('type', 'LIKE', "%{$s}%");
                    });
                }
            })
            ->addColumn('type_badge', function ($row) {
                $colors = [
                    'mpesa' => 'success',
                    'sms' => 'info',
                    'email' => 'warning',
                    'custom' => 'secondary',
                ];
                $color = $colors[$row->type] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . ucfirst(e($row->type)) . '</span>';
            })
            ->addColumn('status', function ($row) {
                $active = $row->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
                $default = $row->is_default
                    ? ' <span class="badge badge-primary">Default</span>'
                    : '';
                return $active . $default;
            })
            ->addColumn('action', function ($row) {
                $toggleLabel = $row->is_active ? 'Deactivate' : 'Activate';
                $toggleIcon = $row->is_active ? 'fa-toggle-on' : 'fa-toggle-off';
                $defaultBtn = '';
                if ($row->is_active && !$row->is_default) {
                    $defaultBtn = '<button class="btn btn-outline-primary btn-sm btn-set-default" data-id="' . $row->id . '" title="Set as Default"><i class="fas fa-star"></i></button> ';
                }
                return '<div class="text-end text-right">' .
                    '<span class="d-none item-id">' . $row->id . '</span>' .
                    '<button class="btn btn-primary btn-sm btn-edit-integration" data-id="' . $row->id . '" title="Edit"><i class="fas fa-edit"></i></button> ' .
                    '<button class="btn btn-outline-secondary btn-sm btn-toggle-integration" data-id="' . $row->id . '" title="' . $toggleLabel . '"><i class="fas ' . $toggleIcon . '"></i></button> ' .
                    $defaultBtn .
                    '<button class="btn btn-danger btn-sm btn-delete-integration" data-id="' . $row->id . '" title="Delete"><i class="fas fa-trash"></i></button>' .
                    '</div>';
            })
            ->addIndexColumn()
            ->escapeColumns([])
            ->make();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:0',
            'type' => 'required|string|in:mpesa,sms,email,custom',
            'name' => 'required|string|max:100',
            'provider' => 'nullable|string|max:100',
            'is_active' => 'nullable',
            'is_default' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        // Build config from request
        $config = [];
        if ($request->type === 'custom') {
            $keys = $request->input('config_keys', []);
            $values = $request->input('config_values', []);
            foreach ($keys as $i => $key) {
                $key = trim($key);
                if ($key !== '') {
                    $config[$key] = $values[$i] ?? '';
                }
            }
        } else {
            $schema = $this->getSchemaForType($request->type);
            foreach ($schema as $field) {
                $fieldKey = 'config_' . $field['key'];
                $value = $request->input($fieldKey);
                // When editing, if a password field is empty, keep the old value
                if ($field['type'] === 'password' && empty($value) && $request->id > 0) {
                    $existing = Integration::find($request->id);
                    if ($existing) {
                        $value = $existing->getConfigValue($field['key']);
                    }
                }
                $config[$field['key']] = $value ?? ($field['default'] ?? '');
            }
        }

        $isActive = $request->has('is_active') ? 1 : 0;
        $isDefault = $request->has('is_default') ? 1 : 0;

        if ($request->id > 0) {
            $integration = Integration::findOrFail($request->id);
            $integration->update([
                'type' => $request->type,
                'name' => $request->name,
                'provider' => $request->provider,
                'config' => $config,
                'is_active' => $isActive,
                'is_default' => $isDefault,
            ]);
        } else {
            $integration = Integration::create([
                'type' => $request->type,
                'name' => $request->name,
                'provider' => $request->provider,
                'config' => $config,
                'is_active' => $isActive,
                'is_default' => $isDefault,
            ]);
        }

        // If set as default, unset others of the same type
        if ($isDefault) {
            Integration::where('type', $integration->type)
                ->where('id', '!=', $integration->id)
                ->update(['is_default' => false]);
        }

        return response()->json(['success' => 'Integration saved successfully!', 'id' => $integration->id]);
    }

    public function delete(Request $request, $id)
    {
        $integration = Integration::findOrFail($id);
        $integration->delete();
        return response()->json(['success' => 'Integration deleted successfully!']);
    }

    public function toggleActive(Request $request, $id)
    {
        $integration = Integration::findOrFail($id);
        $integration->is_active = !$integration->is_active;
        // If deactivating a default, remove default status
        if (!$integration->is_active && $integration->is_default) {
            $integration->is_default = false;
        }
        $integration->save();

        return response()->json([
            'success' => $integration->is_active ? 'Integration activated.' : 'Integration deactivated.',
            'is_active' => $integration->is_active,
        ]);
    }

    public function setDefault(Request $request, $id)
    {
        $integration = Integration::findOrFail($id);

        if (!$integration->is_active) {
            return response()->json(['error' => 'Cannot set inactive integration as default.'], 400);
        }

        // Unset other defaults of same type
        Integration::where('type', $integration->type)
            ->where('id', '!=', $integration->id)
            ->update(['is_default' => false]);

        $integration->is_default = true;
        $integration->save();

        return response()->json(['success' => $integration->name . ' set as default.']);
    }

    public function getFieldSchema(Request $request)
    {
        $type = $request->input('type', 'mpesa');
        $schema = $this->getSchemaForType($type);

        // If editing, include existing values (masked for sensitive fields)
        $values = [];
        if ($request->filled('id') && $request->id > 0) {
            $integration = Integration::find($request->id);
            if ($integration) {
                $values = $integration->getMaskedConfig();
            }
        }

        return response()->json([
            'schema' => $schema,
            'values' => $values,
        ]);
    }

    private function getSchemaForType($type)
    {
        $schemas = [
            'mpesa' => [
                ['key' => 'shortcode', 'label' => 'Paybill/Till Number', 'type' => 'text', 'required' => true],
                ['key' => 'consumer_key', 'label' => 'Consumer Key', 'type' => 'password', 'required' => true],
                ['key' => 'consumer_secret', 'label' => 'Consumer Secret', 'type' => 'password', 'required' => true],
                ['key' => 'passkey', 'label' => 'LNMO Passkey', 'type' => 'password', 'required' => true],
                ['key' => 'stk_url', 'label' => 'STK Push URL', 'type' => 'url', 'required' => false, 'default' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'],
                ['key' => 'oauth_url', 'label' => 'OAuth URL', 'type' => 'url', 'required' => false, 'default' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'],
                ['key' => 'c2b_register_url', 'label' => 'C2B Register URL', 'type' => 'url', 'required' => false, 'default' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl'],
                ['key' => 'callback_base_url', 'label' => 'Callback Base URL', 'type' => 'url', 'required' => false],
                ['key' => 'account_ref', 'label' => 'Account Reference', 'type' => 'text', 'required' => false, 'default' => 'Church Donation'],
            ],
            'sms' => [
                ['key' => 'url', 'label' => 'API URL', 'type' => 'url', 'required' => true],
                ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'required' => true],
                ['key' => 'partner_id', 'label' => 'Partner ID', 'type' => 'text', 'required' => true],
                ['key' => 'short_code', 'label' => 'Short Code / Sender ID', 'type' => 'text', 'required' => true],
            ],
            'email' => [
                ['key' => 'host', 'label' => 'SMTP Host', 'type' => 'text', 'required' => true],
                ['key' => 'port', 'label' => 'SMTP Port', 'type' => 'number', 'required' => true, 'default' => '465'],
                ['key' => 'username', 'label' => 'Username', 'type' => 'text', 'required' => true],
                ['key' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true],
                ['key' => 'encryption', 'label' => 'Encryption', 'type' => 'text', 'required' => false, 'default' => 'ssl'],
                ['key' => 'from_address', 'label' => 'From Address', 'type' => 'email', 'required' => true],
                ['key' => 'from_name', 'label' => 'From Name', 'type' => 'text', 'required' => false],
            ],
        ];

        return $schemas[$type] ?? [];
    }
}
