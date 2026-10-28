<?php

namespace App\Http\Controllers;

use App\Models\ClientPayment;
use App\Models\Donation;
use App\Services\LencoPay;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Receives collection.* events from Lenco. Point the webhook in your Lenco
 * dashboard at https://pikozm.com/webhooks/lenco
 */
class LencoWebhookController extends Controller
{
    public function __invoke(Request $request, LencoPay $lenco): Response
    {
        if (! $lenco->hasValidSignature($request->getContent(), $request->header('X-Lenco-Signature'))) {
            Log::warning('Rejected Lenco webhook with invalid signature');

            return response('Invalid signature', 401);
        }

        $event = (string) $request->input('event');
        $data = (array) $request->input('data', []);
        $reference = $data['reference'] ?? null;

        if (! str_starts_with($event, 'collection.') || ! $reference) {
            return response('Ignored', 200);
        }

        $record = ClientPayment::where('reference', $reference)->first()
            ?? Donation::where('reference', $reference)->first();

        $record?->applyGatewayStatus((string) ($data['status'] ?? 'pending'), $data);

        return response('OK', 200);
    }
}
