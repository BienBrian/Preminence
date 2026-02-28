<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ChildrenImport implements WithChunkReading, ToCollection, WithHeadingRow
{
    protected $phoneCode = '254';
    protected $imported = 0;
    protected $skipped = 0;

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
            $firstname = $this->sanitizeCsvValue(trim($row['first_name'] ?? ''));
            $parentPhone = $this->sanitizeCsvValue(trim($row['parent_phone'] ?? ''));

            // Skip rows missing required fields
            if (empty($firstname) || empty($parentPhone)) {
                $this->skipped++;
                continue;
            }

            $surname = trim($row['surname'] ?? '');
            $lastname = trim($row['last_name'] ?? '');
            $gender = trim($row['gender'] ?? 'Male');
            $dob = $row['date_of_birth'] ?? null;
            $residence = trim($row['residence'] ?? '');
            $sundaySchoolClass = trim($row['sunday_school_class'] ?? '');
            $relationship = trim($row['relationship'] ?? 'Child');

            // Normalize parent phone
            $phone = preg_replace('/[^0-9]/', '', $parentPhone);
            if (strlen($phone) <= 10) {
                $phone = $this->phoneCode . ltrim($phone, '0');
            }

            // Look up parent
            $parent = User::where('phone', $phone)->first();
            $userId = $parent ? $parent->id : 0;

            // Parse DOB
            $parsedDob = null;
            if ($dob) {
                try {
                    $parsedDob = \Carbon\Carbon::parse($dob);
                } catch (\Exception $e) {
                    $parsedDob = null;
                }
            }

            // Create child
            $childId = \DB::table('children')->insertGetId([
                'firstname' => $firstname,
                'surname' => $surname,
                'lastname' => $lastname,
                'gender' => strtoupper($gender),
                'dob' => $parsedDob,
                'residence' => $residence,
                'sunday_school_class' => $sundaySchoolClass,
                'user_id' => $userId,
                'created_at' => now(),
            ]);

            // Create guardian relationship if parent found
            if ($parent && $childId) {
                \DB::table('guardians')->insert([
                    'child_id' => $childId,
                    'user_id' => $parent->id,
                    'relationship' => $relationship,
                    'created_at' => now(),
                ]);
            }

            $this->imported++;
        }
    }
}
