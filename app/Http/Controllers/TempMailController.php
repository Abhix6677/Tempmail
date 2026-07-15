<?php

namespace App\Http\Controllers;

use App\Models\TempEmail;
use App\Models\ReceivedEmail;
use App\Services\DotTempEmailService;
use App\Services\ImapTempMailService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class TempMailController extends Controller
{
    public function create(DotTempEmailService $generator): JsonResponse
    {
        $temp = $generator->generate();

        return response()->json([
            'id' => $temp->id,
            'email' => $temp->generated_address,
            'expires_at' => $temp->expires_at,
        ]);
    }

    public function inbox($id, ImapTempMailService $imapService): JsonResponse
    {
        $temp = TempEmail::where('id', $id)
            ->where('is_active', true)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$temp) {
            return response()->json([
                'message' => 'Temp email not found or expired.'
            ], 404);
        }

        // Poll latest messages before returning
        $imapService->poll();

        $emails = ReceivedEmail::where('temp_email_id', $temp->id)
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->orderByDesc('received_at')
            ->get();

        return response()->json([
            'id' => $temp->id,
            'email' => $temp->generated_address,
            'expires_at' => $temp->expires_at,
            'messages' => $emails,
        ]);
    }

    public function delete($id): JsonResponse
    {
        $temp = TempEmail::find($id);

        if (!$temp) {
            return response()->json([
                'message' => 'Temp email not found.'
            ], 404);
        }

        $temp->update([
            'is_active' => false,
            'expires_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Temp email expired successfully.'
        ]);
    }
}
