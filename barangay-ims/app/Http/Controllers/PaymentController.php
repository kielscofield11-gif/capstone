<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Exceptions\PaymentGatewayException;
use App\Models\DocumentRequest;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);

        $request->validate([
            'status' => 'nullable|in:pending,paid,failed,expired,cancelled,refunded',
            'provider' => 'nullable|in:paymongo,fake,cash',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Payment::with(['documentRequest.resident', 'documentRequest.documentType', 'recordedBy']);

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('provider_payment_id', 'like', "%{$search}%")
                    ->orWhereHas('documentRequest', function ($document) use ($search) {
                        $document->where('control_number', 'like', "%{$search}%")
                            ->orWhereHas('resident', function ($resident) use ($search) {
                                $resident->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        return view('payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load(['documentRequest.resident', 'documentRequest.documentType', 'recordedBy']);

        return view('payments.show', compact('payment'));
    }

    public function store(Request $request, DocumentRequest $document, PaymentService $payments)
    {
        $this->authorize('pay', $document);

        try {
            $payment = $payments->startOnlinePayment($document, $request->user());
        } catch (PaymentException|PaymentGatewayException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Payment link is ready to share with the resident.');
    }

    public function recordCash(Request $request, DocumentRequest $document, PaymentService $payments)
    {
        $this->authorize('pay', $document);

        try {
            $payment = $payments->recordCashPayment($document, $request->user());
        } catch (PaymentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Cash payment recorded. The document can now be released.');
    }

    public function returnPage(Payment $payment, string $token)
    {
        $this->ensureToken($payment, $token);

        $payment->load(['documentRequest.resident', 'documentRequest.documentType']);

        return view('payments.return', [
            'payment' => $payment,
            'token' => $token,
            'cancelled' => false,
        ]);
    }

    public function cancelPage(Payment $payment, string $token)
    {
        $this->ensureToken($payment, $token);

        $payment->load(['documentRequest.resident', 'documentRequest.documentType']);

        return view('payments.return', [
            'payment' => $payment,
            'token' => $token,
            'cancelled' => true,
        ]);
    }

    public function demoCheckout(Payment $payment, string $token)
    {
        $this->ensureDemoMode($payment, $token);

        if ($payment->isPaid()) {
            return redirect()->route('payments.return', [
                'payment' => $payment,
                'token' => $token,
            ]);
        }

        $payment->load(['documentRequest.resident', 'documentRequest.documentType']);

        return view('payments.demo', compact('payment', 'token'));
    }

    public function demoComplete(Request $request, Payment $payment, string $token, PaymentService $payments)
    {
        $this->ensureDemoMode($payment, $token);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['pay', 'fail'])],
        ]);

        if ($validated['action'] === 'pay') {
            $payments->markPaid($payment, [
                'payment_method' => 'demo',
                'provider_payment_id' => 'pay_fake_'.Str::lower(Str::random(16)),
                'metadata' => ['gateway' => Payment::PROVIDER_FAKE],
            ]);

            return redirect()
                ->route('payments.return', ['payment' => $payment, 'token' => $token])
                ->with('success', 'Demo payment completed.');
        }

        $payments->markFailed($payment, 'Simulated payment failure.');

        return redirect()
            ->route('payments.return', ['payment' => $payment, 'token' => $token])
            ->with('error', 'Demo payment failed.');
    }

    private function ensureDemoMode(Payment $payment, string $token): void
    {
        $this->ensureToken($payment, $token);

        abort_if(app()->isProduction(), 404);
        abort_unless(config('payments.default') === 'fake', 404);
        abort_unless($payment->provider === Payment::PROVIDER_FAKE, 404);
    }

    private function ensureToken(Payment $payment, string $token): void
    {
        abort_unless(hash_equals((string) $payment->return_token, $token), 404);
    }
}
