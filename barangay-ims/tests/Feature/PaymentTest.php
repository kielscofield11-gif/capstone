<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\User;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PayMongoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payments.default' => 'fake',
            'payments.paymongo.webhook_secret' => 'whsec_test_secret',
        ]);

        $this->app->forgetInstance(PaymentGateway::class);
    }

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

    private function payment(DocumentRequest $document, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'document_request_id' => $document->id,
            'reference_number' => 'PAY-'.now()->year.'-'.Str::upper(Str::random(8)),
            'provider' => Payment::PROVIDER_FAKE,
            'status' => Payment::STATUS_PENDING,
            'amount' => $document->fee_amount,
            'currency' => 'PHP',
            'return_token' => Str::random(64),
            'recorded_by' => $document->requested_by,
        ], $overrides));
    }

    private function paidCashPayment(DocumentRequest $document): Payment
    {
        return $this->payment($document, [
            'provider' => Payment::PROVIDER_CASH,
            'status' => Payment::STATUS_PAID,
            'payment_method' => 'cash',
            'paid_at' => now(),
        ]);
    }

    private function webhookPayload(Payment $payment): array
    {
        return [
            'data' => [
                'id' => 'evt_test_'.Str::lower(Str::random(8)),
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => $payment->provider_checkout_id ?: 'cs_test_'.Str::lower(Str::random(8)),
                        'type' => 'checkout_session',
                        'attributes' => [
                            'metadata' => [
                                'payment_id' => (string) $payment->id,
                                'reference_number' => $payment->reference_number,
                            ],
                            'payment_intent' => [
                                'id' => 'pi_test_'.Str::lower(Str::random(8)),
                                'attributes' => ['amount' => $payment->amountInCentavos()],
                            ],
                            'payments' => [[
                                'id' => 'pay_test_'.Str::lower(Str::random(8)),
                                'type' => 'payment',
                                'attributes' => [
                                    'amount' => $payment->amountInCentavos(),
                                    'status' => 'paid',
                                    'paid_at' => now()->timestamp,
                                    'source' => ['type' => 'gcash'],
                                ],
                            ]],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function sendWebhook(array $payload, ?string $secret = null, bool $plainSignature = false)
    {
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $secret ??= (string) config('payments.paymongo.webhook_secret');
        $timestamp = time();

        $header = $plainSignature
            ? hash_hmac('sha256', $body, $secret)
            : 't='.$timestamp.',te='.hash_hmac('sha256', $timestamp.'.'.$body, $secret);

        return $this->call('POST', '/webhooks/paymongo', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => $header,
        ], $body);
    }

    public function test_staff_can_generate_a_shareable_online_payment_link(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 150.00);

        $response = $this->actingAs($secretary)
            ->post(route('documents.payments.store', $document));

        $payment = Payment::firstOrFail();

        $response->assertRedirect(route('payments.show', $payment))
            ->assertSessionHas('success', 'Payment link is ready to share with the resident.');

        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame(Payment::PROVIDER_FAKE, $payment->provider);
        $this->assertSame('150.00', $payment->amount);
        $this->assertStringStartsWith('PAY-', $payment->reference_number);
        $this->assertNotNull($payment->checkout_url);
        $this->assertStringContainsString('/payments/'.$payment->id.'/demo/', $payment->checkout_url);
        $this->assertArrayNotHasKey('return_token', $payment->toArray());

        $this->actingAs($secretary)
            ->get(route('payments.show', $payment))
            ->assertOk()
            ->assertSee($payment->reference_number)
            ->assertSee('Share the payment link');

        $this->actingAs($secretary)
            ->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Awaiting payment');
    }

    public function test_repeated_link_requests_reuse_an_active_link_and_replace_an_expired_one(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 150.00);

        $this->actingAs($secretary)->post(route('documents.payments.store', $document));
        $first = Payment::firstOrFail();

        $this->actingAs($secretary)
            ->post(route('documents.payments.store', $document))
            ->assertRedirect(route('payments.show', $first));

        $this->assertSame(1, Payment::count());

        $first->forceFill(['created_at' => now()->subHours(24)])->save();

        $this->actingAs($secretary)->post(route('documents.payments.store', $document));

        $this->assertSame(2, Payment::count());
        $this->assertSame(Payment::STATUS_EXPIRED, $first->fresh()->status);

        $replacement = Payment::where('id', '!=', $first->id)->firstOrFail();
        $this->assertSame(Payment::STATUS_PENDING, $replacement->status);
        $this->assertNotNull($replacement->checkout_url);
    }

    public function test_a_signed_webhook_marks_the_payment_paid_and_is_idempotent(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 250.00);
        $payment = $this->payment($document, [
            'provider' => Payment::PROVIDER_PAYMONGO,
            'provider_checkout_id' => 'cs_test_checkout_123',
        ]);

        $payload = $this->webhookPayload($payment);

        $this->sendWebhook($payload)
            ->assertOk()
            ->assertJson(['received' => true]);

        $payment->refresh();
        $this->assertTrue($payment->isPaid());
        $this->assertSame('gcash', $payment->payment_method);
        $this->assertNotNull($payment->paid_at);
        $this->assertStringStartsWith('pay_test_', (string) $payment->provider_payment_id);
        $this->assertStringStartsWith('pi_test_', (string) $payment->provider_intent_id);
        $this->assertSame(1, AuditLog::where('action', 'payment_paid')->count());

        $this->sendWebhook($payload)
            ->assertOk()
            ->assertJson(['received' => true]);

        $this->assertSame(1, AuditLog::where('action', 'payment_paid')->count());
        $this->assertTrue($document->fresh()->isPaid());
    }

    public function test_a_webhook_with_an_invalid_signature_is_rejected(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 250.00);
        $payment = $this->payment($document, [
            'provider' => Payment::PROVIDER_PAYMONGO,
            'provider_checkout_id' => 'cs_test_checkout_456',
        ]);

        $this->sendWebhook($this->webhookPayload($payment), 'wrong_secret')
            ->assertStatus(401)
            ->assertJson(['message' => 'Invalid signature.']);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(0, AuditLog::where('action', 'payment_paid')->count());
    }

    public function test_a_webhook_accepts_the_plain_hmac_signature_format(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 250.00);
        $payment = $this->payment($document, [
            'provider' => Payment::PROVIDER_PAYMONGO,
            'provider_checkout_id' => 'cs_test_checkout_789',
        ]);

        $this->sendWebhook($this->webhookPayload($payment), null, true)
            ->assertOk()
            ->assertJson(['received' => true]);

        $this->assertTrue($payment->fresh()->isPaid());
    }

    public function test_paymongo_gateway_creates_a_v2_checkout_session(): void
    {
        config(['payments.paymongo.secret_key' => 'sk_test_123']);

        Http::fake([
            'https://api.paymongo.com/v2/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_test_gateway_1',
                    'type' => 'checkout_session',
                    'attributes' => [
                        'checkout_url' => 'https://checkout.paymongo.com/test-session',
                    ],
                ],
            ]),
        ]);

        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 250.00);
        $payment = $this->payment($document, [
            'provider' => Payment::PROVIDER_PAYMONGO,
            'reference_number' => 'PAY-'.now()->year.'-900',
        ]);

        $checkout = (new PayMongoGateway())->createCheckout($payment);

        $this->assertSame('cs_test_gateway_1', $checkout['id']);
        $this->assertSame('https://checkout.paymongo.com/test-session', $checkout['url']);
        $this->assertNull($checkout['payment_intent_id']);

        Http::assertSent(function ($request) use ($payment) {
            return $request->method() === 'POST'
                && $request->url() === 'https://api.paymongo.com/v2/checkout_sessions'
                && $request->hasHeader('Idempotency-Key', 'checkout-'.$payment->reference_number)
                && $request['data']['attributes']['reference_number'] === $payment->reference_number
                && $request['data']['attributes']['metadata']['payment_id'] === (string) $payment->id
                && $request['data']['attributes']['line_items'][0]['amount'] === $payment->amountInCentavos();
        });
    }

    public function test_demo_checkout_is_disabled_in_production(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 175.00);
        $payment = $this->payment($document);

        $url = route('payments.demo', [
            'payment' => $payment,
            'token' => $payment->return_token,
        ]);

        $this->app['env'] = 'production';

        $this->get($url)->assertNotFound();

        $this->post(route('payments.demo.complete', [
            'payment' => $payment,
            'token' => $payment->return_token,
        ]), ['action' => 'pay'])->assertNotFound();

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_demo_checkout_is_disabled_when_paymongo_is_active(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 175.00);
        $payment = $this->payment($document);

        config(['payments.default' => 'paymongo']);

        $this->get(route('payments.demo', [
            'payment' => $payment,
            'token' => $payment->return_token,
        ]))->assertNotFound();

        $this->post(route('payments.demo.complete', [
            'payment' => $payment,
            'token' => $payment->return_token,
        ]), ['action' => 'pay'])->assertNotFound();

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
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

    public function test_the_demo_checkout_can_complete_and_fail_a_payment(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 175.00);
        $payment = $this->payment($document);

        $returnUrl = route('payments.return', [
            'payment' => $payment,
            'token' => $payment->return_token,
        ]);
        $demoUrl = route('payments.demo', [
            'payment' => $payment,
            'token' => $payment->return_token,
        ]);

        $this->get($demoUrl)
            ->assertOk()
            ->assertSee('Demo mode');

        $this->post(route('payments.demo.complete', [
            'payment' => $payment,
            'token' => $payment->return_token,
        ]), ['action' => 'pay'])->assertRedirect($returnUrl);

        $payment->refresh();
        $this->assertTrue($payment->isPaid());
        $this->assertSame('demo', $payment->payment_method);

        $this->get($returnUrl)
            ->assertOk()
            ->assertSee('Payment received');

        $failedDocument = $this->documentRequest($secretary, 175.00);
        $failedPayment = $this->payment($failedDocument);

        $this->post(route('payments.demo.complete', [
            'payment' => $failedPayment,
            'token' => $failedPayment->return_token,
        ]), ['action' => 'fail'])->assertRedirect();

        $this->assertSame(Payment::STATUS_FAILED, $failedPayment->fresh()->status);
        $this->assertNotNull($failedPayment->fresh()->failure_reason);
    }

    public function test_public_payment_pages_require_the_correct_payment_token(): void
    {
        $secretary = $this->activeUser('secretary');
        $document = $this->documentRequest($secretary, 175.00);
        $payment = $this->payment($document);

        $wrongToken = Str::random(64);
        $correctToken = $payment->return_token;

        $this->get(route('payments.return', ['payment' => $payment, 'token' => $wrongToken]))
            ->assertNotFound();
        $this->get(route('payments.cancel', ['payment' => $payment, 'token' => $wrongToken]))
            ->assertNotFound();
        $this->get(route('payments.demo', ['payment' => $payment, 'token' => $wrongToken]))
            ->assertNotFound();

        $this->get(route('payments.return', ['payment' => $payment, 'token' => $correctToken]))
            ->assertOk()
            ->assertSee($payment->reference_number);
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
