<?php

namespace App\Imports;

use App\Models\Pledge;
use App\Models\PledgeSms;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PledgesImport implements WithChunkReading, ToCollection, WithHeadingRow
{
    protected $activity_id = 0;
    protected $thankYouMessage = "";
    protected $phoneCode = '254';

    public function __construct($activityId, $thank_you_message)
    {
        $this->activity_id = $activityId;
        $this->thankYouMessage = $thank_you_message;
        $this->phoneCode = optional(\DB::table('settings')->first())->phone_code ?? '254';
    }

    public function chunkSize(): int
    {
        return 100;
    }

    private function sanitizeCsvValue(string $value): string
    {
        $value = trim($value);
        if (preg_match('/^[=+\-@\t\r]/', $value)) {
            $value = "'" . $value;
        }
        return $value;
    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $name = $this->sanitizeCsvValue($row['name'] ?? '');
            $phoneRaw = $this->sanitizeCsvValue($row['phone'] ?? '');
            $amount = max(0, floatval($row['amount'] ?? 0));

            if (strlen($phoneRaw) < 9) {
                continue;
            }

            $phones = explode("/", $phoneRaw);
            foreach ($phones as $p) {
                $p = trim($p);
                $p = preg_replace('/[^0-9]/', '', $p);
                if (strlen($p) < 9) continue;

                // Normalize phone with dynamic phone code
                if (strlen($p) <= 10) {
                    $phone = $this->phoneCode . ltrim($p, '0');
                } else {
                    $phone = $p;
                }

                $user = User::where('phone', $phone)->first();
                if ($user == null) {
                    $nameParts = explode(" ", $name);
                    $user = new User();
                    $user->phone = $phone;
                    $user->firstname = $nameParts[0] ?? '';
                    if (count($nameParts) > 1) {
                        $user->lastname = $nameParts[1];
                    }
                    if (count($nameParts) > 2) {
                        $user->surname = $nameParts[2];
                    }
                    $user->password = Hash::make(\Str::random(16));
                    $user->must_change_password = true;
                    $user->status = true;
                    $user->save();
                }

                if ($user != null) {
                    $pledge = new Pledge();
                    $pledge->activity = $this->activity_id;
                    $pledge->groups = 0;
                    $pledge->user_id = $user->id;
                    $pledge->paid = $amount;
                    $pledge->amount = $amount;
                    $pledge->status = true;
                    if ($pledge->save()) {
                        $pledgeSms = new PledgeSms();
                        $pledgeSms->pledge_id = $pledge->id;
                        $pledgeSms->message = $this->thankYouMessage;
                        $pledgeSms->save();
                    }
                }
            }
        }
    }
}
