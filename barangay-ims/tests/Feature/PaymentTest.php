<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function activeUser(string $role = 'secretary'): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'email' => $role.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function resident(): Resident
    {
        return Resident::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'birth_date' => now()->subYears(30)->toDateString(),
            'gender' => 'male',
            'civil_status' => 'single',
            'purok' => 'Purok 1',
            'street_address' => '123 Mabini Street',
        ]);
    }

    private function documentType(float $fee): DocumentType
    {
        return DocumentType::create([
            'name' => 'Barangay Clearance '.uniqid(),
            'description' => 'Document type used for payment tests.',
            'fee_amount' => $fee,
            'is_active' => true,
        ]);
    }

    private function documentRequest(User $user, float $fee = 150.00, array $overrides = []): DocumentRequest
    {
        return DocumentRequest::create(array_merge([
            'control_number' => 'DC-'.now()->year.'-'.Str::upper(Str::random(8)),
            'resident_id' => $this->resident()->id,
            'document_type_id' => $this->documentType($fee)->id,
            'purpose' => 'Employment requirement',
            'status' => 'pending',
            'fee_amount' => $fee,
            'requested_by' => $user->id,
        ], $overrides));
    }

    private function paidCashPayment(DocumentRequest $document): Payment
    {
        return Payment::create([
            'document_request_id' => $document->id,
            'reference_number' => 'PAY-'.now()->year.'-'.Str::upper(Str::random(8)),
            'provider' => Payment::PROVIDER_CASH,
            'status' => Payment::STATUS_PAID,
            'amount' => $document->fee_amount,
            'currency' => 'PHP',
            'payment_method' => 'cash',
            'paid_at' => now(),
            'recorded_by' => $document->requested_by,
        ]);
    }

    public function test_online_payment_routes_are_no_longer_registered(): void
    {
        $this->assertFalse(Route::has('documents.payments.store'));
        $this->assertFalse(Route::has('payments.return'));
        $this->assertFalse(Route::has('payments.cancel'));
        $this->assertFalse(Route::has('payments.demo'));
        $this->assertFalse(Route::has('payments.demo.complete'));
        $this->assertFalse(Route::has('webhooks.paymongo'));
    }

    public function test_a_document_cannot_be_released_until_its_fee_is_paid(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 200.00, [
            'status' => 'approved',
            'approved_by' => $secretary->id,
            'approved_date' => now(),
        ]);

        $this->actingAs($secretary)
            ->patch(route('documents.release', $document))
            ->assertRedirect()
            ->assertSessionHas('error', 'The document fee must be paid before this request can be released.');

        $this->assertSame('approved', $document->fresh()->status);

        $this->actingAs($secretary)
            ->post(route('documents.payments.cash', $document))
            ->assertRedirect();

        $payment = Payment::where('document_request_id', $document->id)->firstOrFail();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertSame(Payment::PROVIDER_CASH, $payment->provider);
        $this->assertSame('cash', $payment->payment_method);
        $this->assertNotNull($payment->paid_at);
        $this->assertTrue($document->fresh()->isPaid());

        $this->actingAs($secretary)
            ->patch(route('documents.release', $document))
            ->assertRedirect()
            ->assertSessionHas('success', 'Document marked as released.');

        $document->refresh();
        $this->assertSame('released', $document->status);
        $this->assertNotNull($document->released_date);
        $this->assertNotNull($document->issued_at);
        $this->assertIsArray($document->issued_document_snapshot);
    }

    public function test_a_cash_payment_cannot_be_recorded_when_the_document_is_already_paid(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 200.00);
        $this->paidCashPayment($document);

        $this->actingAs($secretary)
            ->post(route('documents.payments.cash', $document))
            ->assertRedirect()
            ->assertSessionHas('error', 'This document has already been paid.');

        $this->assertSame(1, Payment::count());
    }

    public function test_payments_index_and_detail_pages_render(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 300.00);
        $payment = $this->paidCashPayment($document);

        $this->actingAs($secretary)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee($payment->reference_number)
            ->assertSee($document->control_number)
            ->assertSee('Cash');

        $this->actingAs($secretary)
            ->get(route('payments.show', $payment))
            ->assertOk()
            ->assertSee($payment->reference_number)
            ->assertSee('Payment confirmed');
    }

    public function test_the_fee_and_document_are_locked_after_a_payment_exists(): void
    {
        $admin = $this->activeUser('admin');
        $document = $this->documentRequest($admin, 100.00);
        $this->paidCashPayment($document);

        $this->actingAs($admin)
            ->put(route('documents.update', $document), [
                'resident_id' => $document->resident_id,
                'document_type_id' => $document->document_type_id,
                'purpose' => 'Updated purpose',
                'fee_amount' => 250.00,
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'The fee cannot be changed after a payment has been created.');

        $this->assertSame('100.00', $document->fresh()->fee_amount);

        $this->actingAs($admin)
            ->delete(route('documents.destroy', $document))
            ->assertRedirect()
            ->assertSessionHas('error', 'This document has payment records and cannot be deleted.');

        $this->assertDatabaseHas('document_requests', ['id' => $document->id]);
    }
}
