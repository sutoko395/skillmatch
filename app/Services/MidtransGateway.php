<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class MidtransGateway
{
    public function assertConfigured(): void
    {
        $this->key();
    }

    private function key(): string
    {
        if (config('midtrans.is_production') || ! config('midtrans.server_key')) {
            throw ValidationException::withMessages(['payment' => 'Midtrans Sandbox belum dikonfigurasi.']);
        }

        return config('midtrans.server_key');
    }

    private function http()
    {
        return Http::withBasicAuth($this->key(), '')->acceptJson()->asJson()->connectTimeout(5)->timeout(20);
    }

    public function checkout(Order $order): string
    {
        try {
            $response = $this->http()->post('https://app.sandbox.midtrans.com/snap/v1/transactions', [
                'transaction_details' => ['order_id' => $order->order_ref, 'gross_amount' => $order->amount],
                'callbacks' => ['finish' => route('organizer.orders.show', $order)],
            ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['payment' => 'Gateway tidak terjangkau. Periksa status sebelum mencoba lagi.']);
        }
        $url = $response->json('redirect_url');
        if (! $response->successful() || ! is_string($url) || ! preg_match('~\Ahttps://app\.sandbox\.midtrans\.com/snap/[a-zA-Z0-9/\-]+\z~', $url)) {
            throw ValidationException::withMessages(['payment' => 'Checkout belum tersedia. Sinkronkan status atau coba kembali; jangan membuat pembayaran ganda.']);
        }

        return $url;
    }

    public function verifyNotification(array $input): string
    {
        $key = $this->key();
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $f) {
            if (! isset($input[$f]) || ! is_string($input[$f]) || strlen($input[$f]) > 200) {
                abort(400, 'Notifikasi tidak valid.');
            }
        }
        $expected = hash('sha512', $input['order_id'].$input['status_code'].$input['gross_amount'].$key);
        if (! hash_equals($expected, $input['signature_key'])) {
            abort(403, 'Signature tidak valid.');
        }

        return $input['order_id'];
    }

    public function status(Order $order): VerifiedGatewayResult
    {
        try {
            $response = $this->http()->get('https://api.sandbox.midtrans.com/v2/'.rawurlencode($order->order_ref).'/status');
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['payment' => 'Status gateway belum dapat diperiksa. Coba sinkronkan kembali.']);
        }
        if (! $response->successful() || (string) $response->json('status_code') === '404') {
            throw ValidationException::withMessages(['payment' => 'Status gateway belum tersedia; transaksi Snap mungkin belum memilih metode pembayaran.']);
        }
        $d = $response->json();
        if (! is_array($d) || ($d['order_id'] ?? null) !== $order->order_ref || ($d['currency'] ?? null) !== 'IDR' || ! is_string($d['transaction_id'] ?? null) || strlen($d['transaction_id']) > 100 || ! is_string($d['gross_amount'] ?? null) || ! preg_match('/\A(0|[1-9][0-9]{0,12})(?:\.00)?\z/', $d['gross_amount'], $m) || (int) $m[1] !== $order->amount) {
            throw ValidationException::withMessages(['payment' => 'Identitas, nominal atau mata uang gateway tidak sesuai order.']);
        }
        $status = $d['transaction_status'] ?? '';
        $fraud = $d['fraud_status'] ?? null;
        $mapped = match ($status) {
            'settlement' => ($fraud === null || $fraud === 'accept') ? 'paid' : 'review_required',
            'capture' => $fraud === 'accept' ? 'paid' : 'review_required',
            'pending' => 'pending', 'deny','failure' => 'failed', 'expire' => 'expired', 'cancel' => 'cancelled',
            default => 'review_required',
        };

        return new VerifiedGatewayResult($order->order_ref, $d['transaction_id'], $order->amount, 'IDR', substr((string) $status, 0, 40), is_string($fraud) ? $fraud : null, $mapped);
    }
}
