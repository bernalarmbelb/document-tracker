<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateUploadsToS3 extends Command
{
    /**
     * php artisan uploads:migrate-to-s3
     * php artisan uploads:migrate-to-s3 --dry-run
     */
    protected $signature = 'uploads:migrate-to-s3 {--dry-run : List what would be uploaded without uploading}';

    protected $description = 'Upload existing public/uploads_* files to the shared S3-compatible bucket (safe to re-run; skips files already present)';

    private array $folders = [
        'uploads_communications',
        'uploads_minutes',
        'uploads_ordinances',
        'uploads_resolutions',
        'uploads_sangguniang',
        'uploads_signatures',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = upload_disk();

        $totalUploaded = 0;
        $totalSkipped = 0;

        foreach ($this->folders as $folder) {
            $localDir = public_path($folder);

            if (!is_dir($localDir)) {
                $this->line("Skipping {$folder} (no local directory).");
                continue;
            }

            $files = array_diff(scandir($localDir), ['.', '..']);
            $this->info("Processing {$folder}: ".count($files).' local file(s).');

            foreach ($files as $filename) {
                $localPath = $localDir.DIRECTORY_SEPARATOR.$filename;

                if (!is_file($localPath)) {
                    continue;
                }

                $remotePath = $folder.'/'.$filename;

                if ($disk->exists($remotePath)) {
                    $totalSkipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("  Would upload: {$remotePath}");
                    $totalUploaded++;
                    continue;
                }

                $disk->put($remotePath, file_get_contents($localPath));
                $this->line("  Uploaded: {$remotePath}");
                $totalUploaded++;
            }
        }

        $this->info("Done. Uploaded: {$totalUploaded}, already present (skipped): {$totalSkipped}.");

        return self::SUCCESS;
    }
}
