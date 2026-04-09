<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupTempFiles extends Command
{
    protected $signature = 'tmp:cleanup';
    protected $description = 'Menghapus file di folder temporary yang lebih dari 1 jam';

    public function handle()
    {
        $files = Storage::listSplFiles('public/tmp');
        $now = Carbon::now();
        $count = 0;

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(Storage::lastModified($file));
            
            if ($now->diffInMinutes($lastModified) > 60) {
                Storage::delete($file);
                $count++;
            }
        }

        $this->info("Berhasil menghapus $count file sampah di folder temporary.");
    }
}