<?php
declare(strict_types=1);

namespace App\Core;

class ExceptionHandler
{
    public function register()
    {
        // Convert all PHP errors to ErrorExceptions
        set_error_handler([$this, 'handleError']);

        // Handle uncaught exceptions
        set_exception_handler([$this, 'handleException']);
    }

    public function handleError($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting, so we ignore it.
            return;
        }

        // Convert the error into an ErrorException
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public function handleException(\Throwable $e)
    {
        // This method will now handle both exceptions and errors
        // that have been converted to ErrorExceptions.

        http_response_code(500);
        $appEnv = Config::get('app.env');

        if ($appEnv == 'dev') {
            echo 'Message: ' . $e->getMessage() . '<br>';
            echo 'File: '    . $e->getFile()    . '<br>';
            echo 'Line: '    . $e->getLine()    . '<br>';
        } else {
            echo 'An Error Occurred';
        }

        /* 
         * Optional: 
         * You can log the error message to a log file
         * or perform other log related actions
         */

        error_log($e->getMessage());
    }
}
