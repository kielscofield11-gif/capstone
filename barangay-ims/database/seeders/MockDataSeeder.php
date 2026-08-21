<?php

namespace Database\Seeders;

use App\Models\Blotter;
use App\Models\DocumentRequest;
use App\Models\DocumentTemplate;
use App\Models\DocumentType;
use App\Models\Household;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->guardEnvironment();
        $this->guardSchema();

        DB::transaction(function () {
            $creator = User::where('role', User::ROLE_ADMIN)->where('is_active', true)->first()
                ?? User::where('is_active', true)->firstOrFail();

            $template = DocumentTemplate::updateOrCreate(['name' => 'Mock Demonstration Template'], [
                'title' => '{{document_type}}', 'header_line_1' => 'Republic of the Philippines',
                'header_line_2' => 'Province of {{province}}', 'header_line_3' => 'Municipality/City of {{municipality}}',
                'office_name' => 'BARANGAY {{barangay_name}}', 'barangay_name' => 'Sample Barangay',
                'municipality' => 'Sample Municipality', 'province' => 'Sample Province',
                'opening_phrase' => 'TO WHOM IT MAY CONCERN:',
                'body' => 'This mock document certifies that {{resident_name}} resides at {{address}}. It is requested for {{purpose}}.',
                'closing_text' => 'Issued on {{issue_date}} under control number {{control_number}}.',
                'signatory_name' => 'Sample Signatory', 'signatory_position' => 'Punong Barangay (Mock Data)',
                'footer_text' => 'MOCK DATA — FOR DEMONSTRATION ONLY', 'show_control_number' => true,
                'show_fee' => true, 'show_issue_date' => true, 'show_resident_photo' => false,
                'show_logo' => false, 'is_default' => false, 'is_active' => true, 'created_by' => $creator->id,
            ]);

            $types = collect([
                ['Mock Barangay Clearance', 'Valid government-issued ID', 1, 50],
                ['Mock Certificate of Residency', "Valid ID\nProof of address", 2, 30],
                ['Mock Certificate of Indigency', 'Barangay interview', 3, 0],
            ])->map(fn ($row) => DocumentType::updateOrCreate(['name' => $row[0]], [
                'description' => 'Mock document type for development demonstrations.', 'requirements' => $row[1],
                'processing_days' => $row[2], 'fee_amount' => $row[3], 'is_active' => true,
                'document_template_id' => $template->id,
            ]));

            $households = collect(range(1, 8))->map(fn ($number) => Household::updateOrCreate(
                ['household_number' => sprintf('MOCK-HH-%03d', $number)],
                ['purok' => 'Purok '.((($number - 1) % 4) + 1), 'street_address' => $number.' Sample Street',
                    'is_active' => true, 'created_by' => $creator->id],
            ));

            $firstNames = ['Juan','Maria','Jose','Ana','Pedro','Rosa','Carlo','Liza','Miguel','Elena','Paolo','Teresa','Ramon','Grace','Daniel','Sofia','Marco','Nina','Luis','Carmen','Andres','Joy','Renato','Mila','Ben','Diana','Noel','Irene','Victor','Alma'];
            $lastNames = ['Dela Cruz','Santos','Reyes','Garcia','Mendoza','Torres','Flores','Ramos','Bautista','Aquino'];
            $residents = collect($firstNames)->map(function ($firstName, $index) use ($lastNames, $households, $creator) {
                $household = $households[$index % $households->count()];
                return Resident::updateOrCreate(['email' => sprintf('mock.resident%02d@example.test', $index + 1)], [
                    'first_name' => $firstName, 'middle_name' => $index % 3 ? 'Sample' : null,
                    'last_name' => $lastNames[$index % count($lastNames)], 'suffix' => null,
                    'birth_date' => now()->subYears(18 + ($index % 55))->subDays($index * 11)->toDateString(),
                    'birthplace' => 'Sample Municipality', 'gender' => $index % 2 ? 'female' : 'male',
                    'civil_status' => ['single','married','widowed','separated'][$index % 4],
                    'occupation' => ['Farmer','Vendor','Teacher','Driver','Student'][$index % 5], 'nationality' => 'Filipino',
                    'blood_type' => ['O+','A+','B+','AB+'][$index % 4], 'phone' => sprintf('0917000%04d', $index + 1),
                    'purok' => $household->purok, 'street_address' => $household->street_address,
                    'is_voter' => $index % 3 !== 0, 'is_pwd' => $index % 13 === 0,
                    'is_senior' => $index % 10 === 0, 'is_4ps' => $index % 7 === 0,
                    'household_id' => $household->id, 'is_household_head' => $index < $households->count(),
                    'created_by' => $creator->id,
                ]);
            });

            foreach (range(1, 18) as $number) {
                $status = ['pending','approved','released','cancelled'][($number - 1) % 4];
                $created = now()->subDays($number * 3);
                $type = $types[($number - 1) % $types->count()];
                DocumentRequest::updateOrCreate(['control_number' => sprintf('MOCK-DC-%03d', $number)], [
                    'resident_id' => $residents[($number - 1) % $residents->count()]->id, 'document_type_id' => $type->id,
                    'purpose' => ['Employment','School enrollment','Financial assistance'][$number % 3],
                    'remarks' => 'Mock request for demonstration only.', 'status' => $status, 'fee_amount' => $type->fee_amount,
                    'requested_by' => $creator->id, 'approved_by' => in_array($status, ['approved','released']) ? $creator->id : null,
                    'approved_date' => in_array($status, ['approved','released']) ? $created->copy()->addDay()->toDateString() : null,
                    'released_date' => $status === 'released' ? $created->copy()->addDays(2)->toDateString() : null,
                    'created_at' => $created, 'updated_at' => $created,
                ]);
            }

            foreach (range(1, 12) as $number) {
                $status = ['pending','hearing','resolved','dismissed'][($number - 1) % 4];
                Blotter::updateOrCreate(['blotter_number' => sprintf('MOCK-B-%03d', $number)], [
                    'complainant_id' => $residents[($number - 1) % $residents->count()]->id,
                    'respondent_id' => $residents[$number % $residents->count()]->id,
                    'incident_type' => ['Noise Complaint','Property Dispute','Minor Altercation'][$number % 3],
                    'incident_date' => now()->subDays($number * 4)->toDateString(),
                    'incident_location' => 'Purok '.(($number % 4) + 1),
                    'details' => 'Mock blotter narrative for development and presentation testing only.', 'status' => $status,
                    'hearing_date' => in_array($status, ['pending','hearing']) ? now()->addDays($number % 4)->toDateString() : null,
                    'resolution' => in_array($status, ['resolved','dismissed']) ? 'Mock resolution recorded.' : null,
                    'resolved_by' => in_array($status, ['resolved','dismissed']) ? $creator->id : null, 'created_by' => $creator->id,
                ]);
            }
        });

        $this->command?->info('Mock data created/updated: 8 households, 30 residents, 18 document requests, and 12 blotters.');
    }

    private function guardEnvironment(): void
    {
        $database = (string) DB::connection()->getDatabaseName();
        $safeName = preg_match('/(test|testing|mock|demo|validation|phase\d+)/i', $database) === 1;
        if (!app()->environment('testing') && !$safeName && !filter_var(env('ALLOW_MOCK_DATA_SEEDING', false), FILTER_VALIDATE_BOOL)) {
            throw new RuntimeException("MockDataSeeder refused database '{$database}'. Use a disposable database or explicitly set ALLOW_MOCK_DATA_SEEDING=true.");
        }
    }

    private function guardSchema(): void
    {
        foreach (['document_templates','households','residents','document_types','document_requests','blotters'] as $table) {
            if (!Schema::hasTable($table)) throw new RuntimeException("MockDataSeeder requires migrated table '{$table}'. Run normal migrations on an approved target first.");
        }
    }
}
