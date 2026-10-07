<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$allottee = DB::table('allottees')->where('name', 'like', 'RAHEELA HAROON%')->first();
if ($allottee) {
    echo "Allottee ID: {$allottee->id}\n";
    echo "Amount Paid: {$allottee->amount_paid}\n";
    echo "Payment Date: {$allottee->payment_date}\n";
    echo "Payment Mode: {$allottee->payment_mode}\n";
    
    $txs = DB::table('payment_transactions')->where('allottee_id', $allottee->id)->get();
    echo "Transactions count: " . $txs->count() . "\n";
    foreach ($txs as $t) {
        echo "Tx ID: {$t->id} | Amount: {$t->amount} | Date: {$t->payment_date}\n";
    }
} else {
    echo "Raheela Haroon not found.\n";
}
