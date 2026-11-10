<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Models\DocumentRequest;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);

        $request->validate([
            'status' => 'nullable|in:pending,paid,failed,expired,cancelled,refunded',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Payment::with(['documentRequest.resident', 'documentRequest.documentType', 'recordedBy']);

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
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
}
