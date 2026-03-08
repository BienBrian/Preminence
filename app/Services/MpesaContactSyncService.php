<?php

namespace App\Services;

use App\Models\MpesaPhone;
use App\Models\MissingMpesaPhone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * MpesaContactSyncService
 * 
 * Handles synchronization between MPESA transactions and contact phone hashes.
 * Ensures that after every MPESA transaction, the sender's phone is properly
 * hashed and linked to their contact record for future SMS delivery.
 */
class MpesaContactSyncService
{
    /**
     * Process a phone number from an MPESA transaction.
     * This should be called after every MPESA transaction confirmation.
     * 
     * @param string $msisdn The MSISDN from MPESA (can be hashed or plain)
     * @param string $firstName The first name from MPESA
     * @param string $transId The transaction ID for logging
     * @param float $amount The transaction amount
     * @return array Result with status and matched contact info
     */
    public function processTransactionPhone(string $msisdn, string $firstName, string $transId, float $amount): array
    {
        $result = [
            'status' => 'unknown',
            'phone_normalized' => null,
            'phone_hash' => null,
            'contact_found' => false,
            'user_id' => null,
            'mpesa_phone_created' => false,
            'missing_phone_recorded' => false,
        ];

        // Determine if MSISDN is already a hash or a plain number
        if ($this->isHashedMsisdn($msisdn)) {
            $result['phone_hash'] = $msisdn;
            $phoneHash = $msisdn;
            
            // Try to find existing mpesa_phone record by hash
            $mpesaPhone = MpesaPhone::withoutTenantScope()
                ->where('phone_hash', $phoneHash)
                ->first();
                
            if ($mpesaPhone) {
                $result['phone_normalized'] = $mpesaPhone->phone;
                $result['status'] = 'hash_matched_existing';
            } else {
                $result['status'] = 'hash_no_match';
            }
        } else {
            // Plain number - normalize and hash it
            $normalizedPhone = $this->normalizePhoneNumber($msisdn);
            $result['phone_normalized'] = $normalizedPhone;
            
            if ($normalizedPhone) {
                $phoneHash = hash('sha256', $normalizedPhone);
                $result['phone_hash'] = $phoneHash;
                
                // Check if mpesa_phone record exists
                $mpesaPhone = MpesaPhone::withoutTenantScope()
                    ->where('phone', $normalizedPhone)
                    ->orWhere('phone_hash', $phoneHash)
                    ->first();
                    
                if ($mpesaPhone) {
                    $result['status'] = 'plain_matched_existing';
                } else {
                    $result['status'] = 'plain_new_number';
                }
            } else {
                $result['status'] = 'invalid_phone_format';
                Log::warning("MpesaContactSync: Invalid phone format in transaction", [
                    'trans_id' => $transId,
                    'msisdn' => $msisdn,
                ]);
                return $result;
            }
        }

        // Try to find matching contact
        $contact = $this->findContactByPhone($result['phone_normalized'] ?? $msisdn);
        
        if ($contact) {
            $result['contact_found'] = true;
            $result['user_id'] = $contact->user_id;
            
            // Ensure mpesa_phone record exists for this contact
            if (!$this->hasMpesaPhoneRecord($result['phone_hash'])) {
                $this->createMpesaPhoneRecord(
                    $contact,
                    $result['phone_normalized'] ?? $this->normalizePhoneNumber($contact->phone),
                    $result['phone_hash']
                );
                $result['mpesa_phone_created'] = true;
                $result['status'] .= '_mpesa_phone_created';
            }
            
            // Remove from missing phones if it was there
            $this->removeFromMissingPhones($transId);
            
        } else {
            // No contact found - record in missing_mpesa_phones
            $result['contact_found'] = false;
            $this->recordMissingPhone($firstName, $transId, $result['phone_hash'], $amount);
            $result['missing_phone_recorded'] = true;
            $result['status'] .= '_missing_recorded';
        }

        Log::info("MpesaContactSync: Processed transaction phone", [
            'trans_id' => $transId,
            'status' => $result['status'],
            'contact_found' => $result['contact_found'],
            'user_id' => $result['user_id'],
        ]);

        return $result;
    }

    /**
     * Check if an MSISDN appears to be a SHA256 hash.
     */
    private function isHashedMsisdn(string $msisdn): bool
    {
        // SHA256 hashes are 64 hex characters
        return strlen($msisdn) === 64 && ctype_xdigit($msisdn);
    }

    /**
     * Normalize a phone number to 254XXXXXXXXX format.
     */
    private function normalizePhoneNumber(string $phone): ?string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        if (empty($cleaned)) {
            return null;
        }

        // Convert 07XXXXXXXX to 2547XXXXXXXX
        if (strlen($cleaned) === 10 && substr($cleaned, 0, 1) === '0') {
            return '254' . substr($cleaned, 1);
        }
        
        // Convert 7XXXXXXXX to 2547XXXXXXXX
        if (strlen($cleaned) === 9 && substr($cleaned, 0, 1) === '7') {
            return '254' . $cleaned;
        }
        
        // Already in 254XXXXXXXXX format
        if (strlen($cleaned) === 12 && substr($cleaned, 0, 3) === '254') {
            return $cleaned;
        }
        
        // Handle +254 prefix
        if (strlen($cleaned) === 13 && substr($cleaned, 0, 4) === '2547') {
            return substr($cleaned, 1); // Remove the leading + if present
        }

        // Unknown format, return as-is if it looks like a phone number
        if (strlen($cleaned) >= 9 && strlen($cleaned) <= 15) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Find a contact by phone number (tries various formats).
     */
    private function findContactByPhone(string $phone): ?object
    {
        $normalized = $this->normalizePhoneNumber($phone);
        
        if (!$normalized) {
            return null;
        }

        // Try exact match on normalized format
        $contact = DB::table('contacts')
            ->where('phone', $normalized)
            ->first();
            
        if ($contact) {
            return $contact;
        }

        // Try with leading zero (07XXXXXXXX format)
        $withZero = '0' . substr($normalized, 3);
        $contact = DB::table('contacts')
            ->where('phone', $withZero)
            ->first();
            
        if ($contact) {
            return $contact;
        }

        // Try without country code (7XXXXXXXX format)
        $withoutCountry = substr($normalized, 3);
        $contact = DB::table('contacts')
            ->where('phone', $withoutCountry)
            ->orWhere('phone', 'like', '%' . $withoutCountry)
            ->first();

        return $contact;
    }

    /**
     * Check if an mpesa_phone record exists for a given hash.
     */
    private function hasMpesaPhoneRecord(string $phoneHash): bool
    {
        return MpesaPhone::withoutTenantScope()
            ->where('phone_hash', $phoneHash)
            ->exists();
    }

    /**
     * Create a new mpesa_phone record from contact data.
     */
    private function createMpesaPhoneRecord(object $contact, ?string $normalizedPhone, string $phoneHash): void
    {
        if (!$normalizedPhone) {
            return;
        }

        // Get user name from users table
        $user = DB::table('users')
            ->where('id', $contact->user_id)
            ->first();

        $name = $user ? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) : 'Unknown';

        MpesaPhone::create([
            'tenant_id' => $contact->tenant_id ?? 1,
            'name' => $name,
            'phone' => $normalizedPhone,
            'phone_hash' => $phoneHash,
        ]);

        Log::info("MpesaContactSync: Created new mpesa_phone record", [
            'user_id' => $contact->user_id,
            'phone' => substr($normalizedPhone, 0, 6) . '****',
        ]);
    }

    /**
     * Record a missing phone for later matching.
     */
    private function recordMissingPhone(string $name, string $transId, string $phoneHash, float $amount): void
    {
        // Check if already recorded for this transaction
        $exists = MissingMpesaPhone::withoutTenantScope()
            ->where('trans_id', $transId)
            ->exists();

        if (!$exists) {
            MissingMpesaPhone::create([
                'tenant_id' => 1,
                'name' => $name,
                'trans_id' => $transId,
                'phone_hash' => $phoneHash,
                'trans_date' => Carbon::now(),
                'amount' => $amount,
            ]);

            Log::info("MpesaContactSync: Recorded missing phone", [
                'trans_id' => $transId,
                'name' => $name,
            ]);
        }
    }

    /**
     * Remove a transaction from missing phones (if contact was later matched).
     */
    private function removeFromMissingPhones(string $transId): void
    {
        MissingMpesaPhone::withoutTenantScope()
            ->where('trans_id', $transId)
            ->delete();
    }

    /**
     * Retroactively rehash all contacts that don't have mpesa_phone records.
     * This can be run periodically or on-demand.
     * 
     * @return array Statistics about the operation
     */
    public function rehashAllContacts(): array
    {
        $stats = [
            'processed' => 0,
            'created' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        $contacts = DB::table('contacts')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

        foreach ($contacts as $contact) {
            $stats['processed']++;

            try {
                $normalized = $this->normalizePhoneNumber($contact->phone);
                
                if (!$normalized) {
                    $stats['skipped']++;
                    continue;
                }

                $phoneHash = hash('sha256', $normalized);

                // Skip if already exists
                if ($this->hasMpesaPhoneRecord($phoneHash)) {
                    $stats['skipped']++;
                    continue;
                }

                $this->createMpesaPhoneRecord($contact, $normalized, $phoneHash);
                $stats['created']++;

            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("MpesaContactSync: Error rehashing contact", [
                    'contact_id' => $contact->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("MpesaContactSync: Completed bulk rehash", $stats);

        return $stats;
    }
}
