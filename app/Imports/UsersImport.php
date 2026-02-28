<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

class UsersImport implements WithChunkReading, ToCollection, WithHeadingRow
{
    protected $phoneCode = '254';
    protected $imported = 0;
    protected $skipped = 0;
    protected $errors = 0;

    public function __construct()
    {
        $this->phoneCode = optional(\DB::table('settings')->first())->phone_code ?? '254';
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getErrors(): int
    {
        return $this->errors;
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
        $memberRole = Role::where('name', 'Member')->first();
        if (!$memberRole) {
            $memberRole = Role::where('name', 'User')->first();
        }

        foreach ($collection as $row) {
            $firstname = $this->sanitizeCsvValue(trim($row['first_name'] ?? ''));
            $phoneRaw = $this->sanitizeCsvValue(trim($row['phone'] ?? ''));

            // Skip rows without required fields
            if (empty($firstname) || empty($phoneRaw)) {
                $this->errors++;
                continue;
            }

            // Normalize phone
            $phone = preg_replace('/[^0-9]/', '', $phoneRaw);
            if (strlen($phone) <= 10) {
                $phone = $this->phoneCode . ltrim($phone, '0');
            }

            // Skip duplicate phones
            if (User::where('phone', $phone)->exists()) {
                $this->skipped++;
                continue;
            }

            $surname = trim($row['surname'] ?? '');
            $lastname = trim($row['last_name'] ?? '');
            $email = trim($row['email'] ?? '');
            $gender = trim($row['gender'] ?? '');
            $dob = $row['date_of_birth'] ?? null;

            // Map gender to integer
            $genderValue = null;
            if (strtolower($gender) === 'male') $genderValue = 0;
            elseif (strtolower($gender) === 'female') $genderValue = 1;

            try {
                $user = new User();
                $user->firstname = $firstname;
                $user->surname = $surname;
                $user->lastname = $lastname;
                $user->phone = $phone;
                $user->email = !empty($email) ? $email : null;
                $user->password = Hash::make(\Str::random(16));
                $user->must_change_password = true;
                $user->status = true;
                $user->save();

                // Assign Member role
                if ($memberRole) {
                    $user->assignRole($memberRole->name);
                }

                // Save gender and DOB to contacts table
                if ($genderValue !== null || $dob) {
                    $contactData = [];
                    if ($genderValue !== null) $contactData['gender'] = $genderValue;
                    if ($dob) {
                        try {
                            $contactData['dob'] = \Carbon\Carbon::parse($dob);
                        } catch (\Exception $e) {}
                    }
                    if (!empty($contactData)) {
                        \DB::table('contacts')->updateOrInsert(
                            ['user_id' => $user->id],
                            $contactData
                        );
                    }
                }

                $this->imported++;
            } catch (\Exception $e) {
                \Log::warning('UsersImport: Failed to import row - ' . $e->getMessage());
                $this->errors++;
            }
        }
    }
}
