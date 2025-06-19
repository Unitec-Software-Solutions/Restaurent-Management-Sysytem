<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;
use Carbon\Carbon;
use App\Models\Organization;
use App\Models\Branch;

class EmployeeSeeder extends Seeder
{
    protected array $roles = ['manager', 'chef', 'steward', 'cashier', 'waiter'];
    protected array $firstNames = [
        'Nimal', 'Sunil', 'Kumari', 'Nadeesha', 'Ruwantha', 'Dilani', 'Kavindu', 'Chathurika',
        'Malsha', 'Tharindu', 'Sajith', 'Sachini', 'Isuru', 'Hansani', 'Kalum', 'Madhushani',
        'Niroshan', 'Thilini', 'Pasindu', 'Yasodha', 'Lahiru', 'Sanduni', 'Anura', 'Dulani',
        'Ravindu', 'Kanchana', 'Manoj', 'Rashmi', 'Pradeep', 'Shanika', 'Heshan', 'Pavithra',
        'Dilshan', 'Samantha', 'Lasith', 'Anjali', 'Upul', 'Nadee', 'Chamara', 'Apsara',
        'Dinesh', 'Harini', 'Ruwan', 'Iresha', 'Lakshan', 'Shalini', 'Kasun', 'Dilini',
        'Janaka', 'Yasmin', 'Tharaka', 'Dulanjana', 'Sandamali', 'Chathura', 'Prashan',
        'Nisansala', 'Kamalan', 'Suresh', 'Shanika', 'Hiran', 'Vimukthi', 'Maleesha',
        'Ashan', 'Himali', 'Sanjaya', 'Piumi', 'Chamara', 'Nimantha', 'Gayan', 'Sewwandi',
        'Lahiru', 'Ishara', 'Madhawa', 'Sachitha', 'Eshan', 'Sithara', 'Bimsara', 'Nisansala',
        'Ruwantha', 'Dilani', 'Chamara', 'Supun', 'Nadeesha', 'Iroshini'
    ];
    protected array $middleNames = [
        'N.', 'S.', 'K.', 'R.', 'T.', 'M.', 'D.', 'I.', 'H.', 'P.', 'L.', 'J.', 'G.', 'C.',
        'V.', 'F.', 'B.', 'A.', 'E.', 'W.', 'Y.', 'Z.', 'Q.', 'X.', 'O.', 'U.', 'N.W.', 'S.K.', 'P.L.'
    ];
    protected array $lastNames = [
        'Perera', 'Jayasinghe', 'Fernando', 'Weerasinghe', 'Silva', 'Senanayake', 'Gunawardena',
        'Wickramasinghe', 'Abeysekara', 'Rajapaksha', 'Hettiarachchi', 'Jayawardena', 'Dias',
        'Ranasinghe', 'Bandara', 'Rathnayake', 'Karunaratne', 'Alwis', 'De Silva', 'Wijesinghe',
        'Amarasinghe', 'Herath', 'Liyanage', 'Nawarathne', 'Rajapakse', 'Peris', 'Samaraweera',
        'Vithanage', 'Kulasekara', 'Senarath', 'Hewawasam', 'Wimalasena', 'Premaratne', 'Jayalath',
        'Fonseka', 'Hettiarachchi', 'Jayathilaka', 'Dharmasena', 'Bandara', 'Pathirana', 'Dissanayake',
        'Kumara', 'Wijeratne', 'Gamage', 'Ratnayake', 'Amarasiri', 'Hettiarachchi', 'Abeyrathne',
        'Wijayapala', 'Jayawardhana', 'Samarasinghe', 'Fernando', 'Kulathunga', 'Samaranayake',
        'Hewage', 'Jayasuriya', 'Gunasekara', 'Herath', 'Ranathunga', 'Mahawela', 'Mudalige'
    ];

    public function run(): void
    {
        $faker = Faker::create();

        if (!$this->hasEnoughOrganizations(5)) {
            $this->command->warn("  🚨 Expected at least 5 organizations, aborting seeder.");
            return;
        }

        $branches = Branch::with('organization')->get();
        $empIdMap = [];

        $employees = [];

        // Step 1: Ensure each branch has at least one employee per role
        foreach ($branches as $branch) {
            $orgId = $branch->organization_id;
            $branchId = $branch->id;
            $empIdMap[$orgId][$branchId] = 0;

            foreach ($this->roles as $role) {
                $employees[] = $this->createEmployee($faker, $orgId, $branchId, ++$empIdMap[$orgId][$branchId], $role);
            }
        }

        // Step 2: Add extra random employees up to 500 total
        $extraEmployeesCount = max(0, 500 - count($employees));

        for ($i = 0; $i < $extraEmployeesCount; $i++) {
            $branch = $branches->random();
            $orgId = $branch->organization_id;
            $branchId = $branch->id;

            $empIdMap[$orgId][$branchId] = ($empIdMap[$orgId][$branchId] ?? 0) + 1;

            $employees[] = $this->createEmployee(
                $faker,
                $orgId,
                $branchId,
                $empIdMap[$orgId][$branchId],
                $faker->randomElement($this->roles)
            );
        }

        try {
            DB::table('employees')->insert($employees);
            $this->command->info("  ✅ Inserted " . count($employees) . " employees with full role coverage per branch.");
        } catch (\Exception $e) {
            $this->command->error("  ❌ Failed to insert employees: " . $e->getMessage());
            Log::error("EmployeeSeeder insert failed", ['error' => $e]);
        }
    }

    protected function hasEnoughOrganizations(int $minimum): bool
    {
        return Organization::count() >= $minimum;
    }

    protected function createEmployee($faker, int $orgId, int $branchId, int $empIndex, string $role): array
    {
        $joinedDate = $faker->dateTimeBetween('-3 years', 'now');
        $createdAt = Carbon::instance($faker->dateTimeBetween($joinedDate, 'now'));
        $empId = "ORG{$orgId}-BR{$branchId}-EMP" . str_pad($empIndex, 3, '0', STR_PAD_LEFT);

        return [
            'emp_id' => $empId,
            'name' => $this->generateUniqueSLName($faker),
            'email' => $faker->unique()->safeEmail,
            'phone' => $faker->numerify('07########'),
            'role' => $role,
            'branch_id' => $branchId,
            'organization_id' => $orgId,
            'is_active' => true,
            'joined_date' => $joinedDate,
            'address' => $faker->address,
            'emergency_contact' => $faker->numerify('07########') . ' (' . $this->generateUniqueSLName($faker) . ')',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    protected function generateUniqueSLName($faker): string
    {
        $first = $faker->randomElement($this->firstNames);
        $middle = $faker->optional()->randomElement($this->middleNames);
        $last = $faker->randomElement($this->lastNames);

        return trim("{$first} {$middle} {$last}");
    }
}
