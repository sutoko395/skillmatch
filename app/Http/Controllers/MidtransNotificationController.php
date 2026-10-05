<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransGateway;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $r, MidtransGateway $gateway, PaymentService $service)
    {
        try {
            $reference = $gateway->verifyNotification($r->all());
            $order = Order::where('order_ref', $reference)->firstOrFail();
            // Signature alone does not authenticate status/currency fields: always fetch authoritative server status.
            $service->sync($order);

            return response()->json(['received' => true]);
        } catch (ValidationException) {
            return response()->json(['received' => false, 'message' => 'Status belum dapat diverifikasi; kirim ulang notifikasi.'], 503);
        }
    }
}
