<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => empty($result) ? (object)[] : $result,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            // Extract first error string if it's a MessageBag or nested array
            if ($errorMessages instanceof \Illuminate\Support\MessageBag) {
                $firstError = $errorMessages->first();
            } elseif (is_array($errorMessages)) {
                $firstError = collect($errorMessages)->flatten()->first();
            } else {
                $firstError = $errorMessages; // fallback, could be string
            }

            $response['data'] = [
                'error' => $firstError,
            ];
        } else {
            $response['data'] = [
                'error' => $error,
            ];
        }
        return response()->json($response, $code);
    }
}
