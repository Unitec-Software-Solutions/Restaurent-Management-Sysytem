<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Branch;

$branches = Branch::select('id', 'name', 'is_head_office', 'type')->orderBy('id')->get();

echo "All Branches:\n";
foreach ($branches as $branch) {
    $headOffice = $branch->is_head_office ? "HEAD OFFICE" : "regular";
    echo "- ID {$branch->id}: {$branch->name} ({$headOffice}, type: {$branch->type})\n";
}

echo "\nNon-head-office branches:\n";
$nonHeadOfficeBranches = Branch::where('is_head_office', false)->get();
foreach ($nonHeadOfficeBranches as $branch) {
    echo "- ID {$branch->id}: {$branch->name} (type: {$branch->type})\n";
}
