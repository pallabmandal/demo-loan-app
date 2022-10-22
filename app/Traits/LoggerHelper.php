<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LoggerHelper {

    private $modelObj;
    private $result;
    private $fieldObj;

    public static function createLog($message, $data = [], $trace = 1) {

        $codeIndex = self::getFile($trace);

        $code = env('APP_NAME') . "_COMMON";
        if (!empty(config('classcode')[$codeIndex])) {
            $code = config('classcode')[$codeIndex];
            if (!empty(self::getLine($trace))) {
                $code = $code . '-' . self::getLine($trace);
            }
        }

        $message = $code . ' - ' . $message;

        \Log::channel('customLog')->info($message, $data);

        return;
    }

    public static function createErrorLog($message, $data, $trace = 1) {
        $codeIndex = self::getFile($trace);
        $code      = env('APP_NAME') . "_COMMON";
        if (!empty(config('classcode')[$codeIndex])) {
            $code = config('classcode')[$codeIndex];
            if (!empty(self::getLine($trace))) {
                $code = $code . '-' . self::getLine($trace);
            }
        }
        $message = $code . ' - ' . $message;
        \Log::channel('customLog')->error($message, $data);
        return ['flag' => '0', 'data' => [], 'error' => $message];
    }



    public static function createWarningLog($message, $data=[], $trace = 1) {
        $codeIndex = self::getFile($trace);
        $code      = env('APP_NAME') . "_COMMON";

        if (!empty(config('classcode')[$codeIndex])) {
            $code = config('classcode')[$codeIndex];
            if (!empty(self::getLine($trace))) {
                $code = $code . '-' . self::getLine($trace);
            }
        }

        $message = $code . ' - ' . $message;
        \Log::channel('customLog')->warning($message, $data);
        return ['flag' => '0', 'data' => [], 'error' => $message];
    }

    public static function getFile($traceTrack = 1) {
        $fileName = "";
        $fileTrack = $traceTrack+1;
        $trace    = debug_backtrace(false, $fileTrack);

        if(!empty($trace)){
            if(!empty($trace[$traceTrack])){
                if (!empty($trace[$traceTrack]['file'])) {
                    $fileName = $trace[$traceTrack]['file'];
                    $fileName = str_replace(base_path(), '', $fileName);
                }
            }
        }

        return $fileName;
    }

    public static function getLine($traceTrack = 1) {
        $line  = "";
        $fileTrack = $traceTrack+1;
        $trace = debug_backtrace(false, $fileTrack);
        if (!empty($trace[$traceTrack]['line'])) {
            $line = $trace[$traceTrack]['line'];
        }
        return $line;
    }
}