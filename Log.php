<?php

namespace Games;

class Log
{
    public static function info($message, $context = [])
    {
        self::write('INFO', $message, $context);
    }

    public static function error($message, $context = [])
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning($message, $context = [])
    {
        self::write('WARNING', $message, $context);
    }

    public static function debug($message, $context = [])
    {
        self::write('DEBUG', $message, $context);
    }

    private static function write($level, $message, $context = [])
    {
        // Convert arrays/objects to string for readability
        if (!is_string($message)) {
            $message = json_encode($message);
        }

        // Append context if provided
        if (!empty($context)) {
            $message .= ' ' . json_encode($context);
        }

        // Format: [Timestamp] [LEVEL] Message
        $output = sprintf("[%s] [%s] %s" . PHP_EOL, date('Y-m-d H:i:s'), $level, $message);

        // Write to php://stderr so it shows in the console/logs 
        // BUT DOES NOT corrupt the JSON response sent to Node.js via stdout
        file_put_contents('php://stderr', $output);
    }

    // Catch-all for any other Laravel Log methods (alert, notice, etc.)
    public static function __callStatic($name, $arguments)
    {
        $message = $arguments[0] ?? '';
        $context = $arguments[1] ?? [];
        self::write(strtoupper($name), $message, $context);
    }
}
