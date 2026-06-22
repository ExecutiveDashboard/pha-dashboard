<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ComplaintCategory;
use App\Models\User;
use App\Models\MaintenanceStaff;

foreach(['Electrical', 'Plumbing', 'Water Supply', 'Sewerage', 'Civil Works', 'Carpentry', 'Painting', 'Lift/Elevator', 'Cleaning', 'Security', 'Street Lights', 'Common Area Maintenance', 'Horticulture', 'Waste Management', 'Others'] as $name) {
    ComplaintCategory::firstOrCreate(['name' => $name]);
}

$user = User::firstOrCreate(
    ['email' => 'plumber@example.com'],
    [
        'name' => 'Muhammad Ali',
        'password' => bcrypt('password'),
        'role' => 'maintenance_staff'
    ]
);

MaintenanceStaff::firstOrCreate(
    ['user_id' => $user->id],
    [
        'name' => 'Muhammad Ali',
        'designation' => 'Plumber',
        'phone' => '03001112222'
    ]
);

echo "CMS Seeded Successfully!\n";
