<?php

use App\Models\PrayerRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\PrayerWallController;

$controller = new PrayerWallController();

// Test 1: Match by Alternate Phone
echo "Test 1: Match by Alternate Phone (0706544124)\n";
$req1 = new Request([
    'title' => 'Test Alternate Match',
    'description' => 'Testing matching via alternate phone.',
    'contact_preference' => 'follow_up',
    'name' => 'Alt Tester',
    'alternate_phone' => '0706544124', // Should normalize to 254...
    'phone' => '0700000000' // Non-matching primary
]);
$controller->submit($req1);
$p1 = PrayerRequest::where('title', 'Test Alternate Match')->latest()->first();
echo "Submitted By: " . ($p1->submitted_by ? "User ID {$p1->submitted_by}" : "NULL") . "\n";
echo "Alt Phone: " . $p1->submitted_alternate_phone . "\n";

// Test 2: Match by Primary Phone
echo "\nTest 2: Match by Primary Phone (0706544124)\n";
$req2 = new Request([
    'title' => 'Test Primary Match',
    'description' => 'Testing matching via primary phone.',
    'contact_preference' => 'follow_up',
    'name' => 'Primary Tester',
    'phone' => '0706544124'
]);
$controller->submit($req2);
$p2 = PrayerRequest::where('title', 'Test Primary Match')->latest()->first();
echo "Submitted By: " . ($p2->submitted_by ? "User ID {$p2->submitted_by}" : "NULL") . "\n";

// Test 3: No Match
echo "\nTest 3: No Match (0712345678)\n";
$req3 = new Request([
    'title' => 'Test No Match',
    'description' => 'Testing non-matching phone.',
    'contact_preference' => 'follow_up',
    'name' => 'Stranger',
    'phone' => '0712345678'
]);
$controller->submit($req3);
$p3 = PrayerRequest::where('title', 'Test No Match')->latest()->first();
echo "Submitted By: " . ($p3->submitted_by ? "User ID {$p3->submitted_by}" : "NULL") . "\n";
