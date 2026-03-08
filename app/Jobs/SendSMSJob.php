<?php

namespace App\Jobs;

use App\Http\Controllers\Services\SendSMSController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $phone;
    protected $message;
    public int $tenantId;

    /**
     * Create a new job instance.
     */
    public function __construct($phone, $message)
    {
        $this->phone = $phone;
        $this->message = $message;
        // Capture tenant context at dispatch time
        $this->tenantId = config('app.tenant_id') ?? 1;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Restore tenant context
        config(['app.tenant_id' => $this->tenantId]);
        
        // Set Spatie permission team context
        if (app()->bound(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($this->tenantId);
        }
        
        (new SendSMSController)->sendSMS($this->phone, $this->message);
    }
}
