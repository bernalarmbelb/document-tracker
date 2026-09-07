<?php

use App\Models\ActivityLogs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

if (!function_exists('upload_disk')) {
    /**
     * The shared filesystem disk uploaded documents live on (S3-compatible
     * object storage), so dev and live sites read/write the same files
     * instead of each server keeping its own local public/uploads_* copy.
     */
    function upload_disk()
    {
        return Storage::disk('s3');
    }
}

if (!function_exists('upload_url')) {
    /**
     * MEGA S4 (our S3-compatible disk) doesn't serve plain public GETs the way
     * AWS S3 buckets normally do, even with "public" access granted at the
     * bucket level - it 403s unsigned requests. Presigned URLs work reliably
     * regardless of that setting, so every link is time-limited by design.
     */
    function upload_url(string $folder, string $filename, int $expiryMinutes = 60): string
    {
        return upload_disk()->temporaryUrl(
            rtrim($folder, '/').'/'.$filename,
            now()->addMinutes($expiryMinutes)
        );
    }
}

if (!function_exists('upload_local_copy')) {
    /**
     * Downloads an uploaded object to a local temp file so it can be embedded
     * by mPDF, which requires a real filesystem path rather than a remote URL.
     * Caller is responsible for @unlink()-ing the returned path when done.
     */
    function upload_local_copy(string $folder, string $filename): ?string
    {
        $path = rtrim($folder, '/').'/'.$filename;

        if (!upload_disk()->exists($path)) {
            return null;
        }

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpPath = $tmpDir.'/'.Str::random(20).'-'.basename($filename);
        file_put_contents($tmpPath, upload_disk()->get($path));

        return $tmpPath;
    }
}