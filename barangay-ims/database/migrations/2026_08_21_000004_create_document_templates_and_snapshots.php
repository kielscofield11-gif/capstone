<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title');
            $table->string('header_line_1')->nullable();
            $table->string('header_line_2')->nullable();
            $table->string('header_line_3')->nullable();
            $table->string('office_name')->nullable();
            $table->string('barangay_name')->nullable();
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->text('opening_phrase')->nullable();
            $table->longText('body');
            $table->text('closing_text')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_position')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('show_control_number')->default(true);
            $table->boolean('show_fee')->default(false);
            $table->boolean('show_issue_date')->default(true);
            $table->boolean('show_resident_photo')->default(false);
            $table->boolean('show_logo')->default(false);
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->foreignId('document_template_id')->nullable()->after('processing_days')
                ->constrained('document_templates')->nullOnDelete();
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->json('issued_document_snapshot')->nullable()->after('released_date');
            $table->timestamp('issued_at')->nullable()->after('issued_document_snapshot');
        });

        DB::table('document_templates')->insert([
            'name' => 'Temporary Generic Template',
            'title' => '{{document_type}}',
            'header_line_1' => 'Republic of the Philippines',
            'header_line_2' => 'Province of {{province}}',
            'header_line_3' => 'Municipality/City of {{municipality}}',
            'office_name' => 'BARANGAY {{barangay_name}} - OFFICE OF THE PUNONG BARANGAY',
            'opening_phrase' => 'TO WHOM IT MAY CONCERN:',
            'body' => 'This is to certify that {{resident_name}}, {{age}} years old, {{civil_status}}, and a resident of {{address}}, is a bona fide resident of Barangay {{barangay_name}}.\n\nThis certification is issued upon the request of the above-named person for {{purpose}} and for whatever lawful purpose it may serve.',
            'closing_text' => 'Issued this {{issue_date}} at Barangay {{barangay_name}}, {{municipality}}, {{province}}.',
            'signatory_position' => 'Punong Barangay',
            'footer_text' => null,
            'show_control_number' => true,
            'show_fee' => false,
            'show_issue_date' => true,
            'show_resident_photo' => false,
            'show_logo' => false,
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropColumn(['issued_document_snapshot', 'issued_at']);
        });
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('document_template_id');
        });
        Schema::dropIfExists('document_templates');
    }
};
