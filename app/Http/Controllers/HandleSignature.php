<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HandleSignature extends Controller
{
    public $signatureInput = 'x-amzn-attest=("@path" "x-amzn-marketplace-id" "user-agent")';

    public function __invoke(Request $request): string
    {
        $validated = $request->validate([
            'signatureInput' => 'required',
            'marketplaceId' => 'required',
            'userAgent' => 'required',
            'publicKeyId' => 'required'
        ]);

        if (!$validated) {
            // TODO: Don't say fuck
            dd('Fuck right off.');
        }

        if ($request->signatureInput != $this->signatureInput) {
            // TODO: Don't say fuck
            dd('You, too, can fuck right off.');
        }

        $created = now();

        $alg = 'apple-attest';

        $keyId = $request->publicKeyId;

        $path = request()->getRequestUri();

        $dataToSign = $path . ' ' . $request->marketplaceId . ' ' . $request->userAgent;
        $signature = hash_hmac('sha256', $dataToSign, $keyId);

        // Generate the final Signature header
        $signatureHeader = "v1=" . base64_encode($signature) . "; alg=\"{$alg}\"; created=\"{$created}\"; nonce=\"{$created}\"; keyid=\"{$keyId}\"";

        // Output the signature header
        return response()->json([
            'Signature-Input' => $this->signatureInput,
            'Signature' => $signatureHeader
        ]);
    }
}
