<?php

use App\Models\ActivityLogs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

if (!function_exists('log_activity')) {
    function log_activity($action, $description = '')
    {
        ActivityLogs::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'description' => $description,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::header('User-Agent'),
            'activity_date' => date("Y-m-d H:i:s")
        ]);
    }
}

if (!function_exists('smart_title')) {
    /**
     * True title case: capitalize each word, but keep small connector words
     * (a, an, the, of, in, to, ...) lowercase unless they are the first word.
     * Source text is often stored ALL CAPS; this makes it clean and readable.
     */
    function smart_title(?string $string): string
    {
        $string = trim((string) $string);
        if ($string === '') {
            return '';
        }

        $small = [
            'a', 'an', 'and', 'as', 'at', 'but', 'by', 'for', 'from', 'in',
            'into', 'nor', 'of', 'off', 'on', 'onto', 'or', 'over', 'per',
            'the', 'to', 'up', 'via', 'vs', 'with',
        ];

        $tokens = preg_split('/(\s+)/u', mb_strtolower($string), -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        $wordIndex = 0;

        foreach ($tokens as $token) {
            if (trim($token) === '') {          // whitespace separators
                $result .= $token;
                continue;
            }

            $isSmall = in_array($token, $small, true);
            if ($wordIndex === 0 || ! $isSmall) {
                // Uppercase the first alphabetic character (handles leading "(" etc.)
                $token = preg_replace_callback('/\p{L}/u', fn ($m) => mb_strtoupper($m[0]), $token, 1);
            }

            $result .= $token;
            $wordIndex++;
        }

        return $result;
    }
}