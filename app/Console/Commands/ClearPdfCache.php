<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearPdfCache extends Command
{
    protected $signature = 'cache:clear-pdfs
                            {--older-than=7 : Eliminar archivos con mas de N dias (default: 7)}
                            {--dry-run : Solo mostrar que se eliminaria, sin borrar}';

    protected $description = 'Limpia archivos PDF y JPG generados en public/cache/';

    public function handle(): int
    {
        $cachePath = public_path('cache');

        if (!is_dir($cachePath)) {
            $this->warn("El directorio $cachePath no existe.");
            return self::SUCCESS;
        }

        $days = (int) $this->option('older-than');
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $files = File::files($cachePath);
        $totalSize = 0;
        $deletedCount = 0;

        $this->info("Escaneando {$cachePath}...");
        $this->line("Archivos anteriores a: {$cutoff->toDateTimeString()}");

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileTime = (new \DateTime())->setTimestamp($file->getMTime());

            if ($fileTime < $cutoff) {
                $size = $file->getSize();
                $totalSize += $size;

                if ($dryRun) {
                    $this->line("  [DRY-RUN] Eliminaria: {$file->getFilename()} (" . $this->formatBytes($size) . ")");
                } else {
                    File::delete($filePath);
                    $this->line("  Eliminado: {$file->getFilename()} (" . $this->formatBytes($size) . ")");
                }
                $deletedCount++;
            }
        }

        if ($deletedCount === 0) {
            $this->info("No hay archivos antiguos que limpiar.");
        } else {
            $this->info("Procesados: {$deletedCount} archivos, {$this->formatBytes($totalSize)} liberados" . ($dryRun ? ' (simulacion)' : ''));
        }

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
