<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HandleSignature extends Controller
{

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'signatureInput' => 'required',
            'marketplaceId' => 'required',
            'userAgent' => 'required',
        ]);

        if (!$validated) {
            // TODO: Don't say fuck
            dd('Fuck right off.');
        }

        // Use regular expression to extract the keyId value
        preg_match('/keyid=([^;]+)/', $request->signatureInput, $matches);

        // The keyId value will be stored in $matches[1]
        if (isset($matches[1])) {
            $keyId = $matches[1];
        } else {
            dd('No Key Present. Try again, por favor');
        }

        // Use regular expression to extract the keyId value
        preg_match('/created=([^;]+)/', $request->signatureInput, $matches);

        // The keyId value will be stored in $matches[1]
        if (isset($matches[1])) {
            $created = $matches[1];
        } else {
            dd('No Key Present. Try again, por favor');
        }

        // Use regular expression to extract the keyId value
        preg_match('/nonce=([^;]+)/', $request->signatureInput, $matches);

        // The keyId value will be stored in $matches[1]
        if (isset($matches[1])) {
            $nonce = $matches[1];
        } else {
            dd('No Key Present. Try again, por favor');
        }

        $data = [];
        $data['path'] = request()->getRequestUri();
        $data['marketPlaceId'] = request()->marketplaceId;
        $data['userAgent'] = request()->userAgent;
        $data['created'] = $created;
        $data['nonce'] = $nonce;
        $data['keyId'] = $keyId;

        $canonicalizedData = '';
        foreach ($data as $key => $value) {
            $canonicalizedData .= "{$key}={$value}\n";
        }

        $canonicalizedData = rtrim($canonicalizedData);  // Remove the trailing newline
        $algorithm = 'sha256';  // Replace with the appropriate algorithm if needed

        // Create the signature using HMAC
        $signatureHeader = base64_encode(hash_hmac($algorithm, $canonicalizedData, $keyId));

        // Output the signature header
        return response()->json([
            'Signature-Input' => $request->signatureInput,
            'Signature' => 'x-amzn-attest=:'.$signatureHeader.':'
        ]);
    }
}
