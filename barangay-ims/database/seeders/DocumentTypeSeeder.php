<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Barangay Clearance', 'description' => 'General clearance certificate for residents', 'fee_amount' => 50.00],
            ['name' => 'Certificate of Indigency', 'description' => 'Certificate proving indigent status', 'fee_amount' => 0.00],
            ['name' => 'Certificate of Residency', 'description' => 'Proof of residency in the barangay', 'fee_amount' => 30.00],
            ['name' => 'Business Clearance', 'description' => 'Clearance for business operations', 'fee_amount' => 100.00],
            ['name' => 'Certificate of Good Moral', 'description' => 'Certificate of good moral character', 'fee_amount' => 30.00],
            ['name' => 'Cedula (Community Tax Certificate)', 'description' => 'Community tax certificate', 'fee_amount' => 25.00],
            ['name' => 'Permit to Construct', 'description' => 'Construction permit within the barangay', 'fee_amount' => 150.00],
        ];

        foreach ($types as $type) {
            DocumentType::updateOrCreate(
                ['name' => $type['name']],
                $type,
            );
        }
    }
}
