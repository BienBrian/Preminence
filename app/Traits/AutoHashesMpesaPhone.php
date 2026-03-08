<?php

namespace App\Traits;

use App\Models\MpesaPhone;
use Illuminate\Support\Facades\DB;

trait AutoHashesMpesaPhone
{
    protected function createMpesaHashAndMatch(string $phone, string $name, int $userId): void
    {
        // Normalize to 254... format
        $normalized = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($normalized) == 10 && substr($normalized, 0, 1) == '0') {
            $normalized = '254' . substr($normalized, 1);
        } elseif (strlen($normalized) == 9) {
            $normalized = '254' . $normalized;
        }
        if (strlen($normalized) != 12) return;

        $hash = hash('sha256', $normalized);

        // Use withoutTenantScope() so this works in both HTTP contexts (tenant set)
        // and artisan commands / queue jobs (tenant context may not be configured).
        if (!MpesaPhone::withoutTenantScope()->where('phone_hash', $hash)->exists()) {
            MpesaPhone::create([
                'name'       => $name,
                'phone'      => $normalized,
                'phone_hash' => $hash,
            ]);
        }

        // Retro-match: find unmatched funds where the mpesa transaction MSISDN matches this hash
        $unmatchedFunds = DB::table('funds')
            ->where('user_id', 0)
            ->where('source', 1)
            ->get();

        foreach ($unmatchedFunds as $fund) {
            $transaction = DB::table('mpesa_transactions')
                ->where('TransAmount', $fund->amount)
                ->whereDate('created_at', \Carbon\Carbon::parse($fund->created_at)->toDateString())
                ->first();

            if (!$transaction) continue;

            if ($transaction->MSISDN === $hash || $transaction->MSISDN === $normalized) {
                DB::table('funds')->where('id', $fund->id)->update(['user_id' => $userId]);
                DB::table('missing_mpesa_phones')
                    ->where('trans_id', $transaction->TransID)
                    ->delete();
            }
        }
    }
}
