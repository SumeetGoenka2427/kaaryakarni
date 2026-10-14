<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Receipts\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Member → My Payments & Receipts. Only the member's own payments. */
class PaymentHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::where('member_id', $request->user('member')->id)
            ->where('status', '!=', 'created')
            ->with(['receipt', 'payable'])
            ->latest('id')->paginate(20);

        return view('member.payments', compact('payments'));
    }

    public function receipt(Request $request, string $payment, ReceiptService $receipts): Response
    {
        $payment = Payment::where('member_id', $request->user('member')->id)->where('ulid', $payment)->with('receipt')->firstOrFail();
        abort_unless($payment->receipt, 404);

        return $receipts->download($payment->receipt);
    }
}
