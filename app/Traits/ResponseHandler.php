<?php

namespace App\Traits;

trait ResponseHandler
{
    public function buildSuccess($status, $data, $display_message, $code)
    {
        return response()->json([
            'result' => $status, 
            'error' => [], 
            'data' => $data, 
            'message' => $display_message,
            'status_code' => $code
        ]);
    }

    public function buildUnSuccessful($status, $data, $display_message, $code, $error=NULL)
	{
		return response()->json([
			'result' => $status,
			'error' => [$error],
			'data' => $data,
			'message' => $display_message,
			'status_code' => $code
		], $code);
	}

    public function buildFailValidation($data)
    {
        $this->createErrorLog(json_encode($data['error']), 2);
        return response()->json($data, 422);
    }

    public static function buildUnsuccessfulValidationResponse($validator_result, $errorTrack=2)
	{
        \App\Traits\LoggerHelper::createWarningLog('Validation Failed', $validator_result, $errorTrack);
		return response()->json($validator_result, 422); 
	}
}
