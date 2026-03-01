<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use App\Models\Supplier;
use App\Models\PriceFile;

class FetchPrices extends Command
{
    /**
     * php artisan prices:fetch {--supplier=}
     */
    protected $signature = 'prices:fetch {--supplier=}';

    protected $description = 'Download latest price lists from suppliers';

    public function handle(): int
    {
        $query = Supplier::query();

        // php artisan prices:fetch --supplier=1
        if ($supplierId = $this->option('supplier')) {
            $query->where('id', $supplierId);
        }

        $suppliers = $query->get();

        if ($suppliers->isEmpty()) {
            $this->warn('No suppliers found.');
            return 0;
        }

        foreach ($suppliers as $supplier) {
            $this->info("Fetching price for [{$supplier->id}] {$supplier->name} ({$supplier->code})...");

            try {
                match ($supplier->type) {
                    'http'   => $this->fetchFromHttp($supplier),
                    'ftp'    => $this->fetchFromFtp($supplier),    // TODO
                    'gdrive' => $this->fetchFromGdrive($supplier), // TODO
                    default  => $this->warn("Unknown supplier type: {$supplier->type}"),
                };
            } catch (\Throwable $e) {
                $this->error("Error while fetching for {$supplier->code}: " . $e->getMessage());
            }
        }

        return 0;
    }

    /**
     * HTTP/HTTPS завантаження з правильним розширенням.
     */
    protected function fetchFromHttp(Supplier $supplier): void
    {
        if (!$supplier->source_url) {
            $this->warn("Supplier {$supplier->code} has no source_url.");
            return;
        }

        $config  = $supplier->config ?? [];
        $timeout = $config['http_timeout']    ?? 120;
        $verify  = $config['http_verify_ssl'] ?? false;

        $clientOptions = [
            'timeout' => $timeout,
            'verify'  => $verify,
        ];

        if (!empty($config['http_auth'])) {
            $clientOptions['auth'] = [
                $config['http_auth']['user'] ?? '',
                $config['http_auth']['password'] ?? '',
            ];
        }

        $client = new Client($clientOptions);

        $this->line("  GET {$supplier->source_url}");

        // 1) Створюємо запис у price_files зі статусом "downloading"
        $dateDir   = now()->format('Ymd_His');
        $basePath  = "prices/{$supplier->id}/{$dateDir}";
        $filename  = "price_{$supplier->id}_{$dateDir}";
        $tempPath  = "{$basePath}/{$filename}"; // тимчасово без розширення

        $priceFile = PriceFile::create([
            'supplier_id'    => $supplier->id,
            'filename'       => $tempPath,
            'original_name'  => null,
            'extension'      => null,
            'rows_count'     => 0,
            'price_date'     => null,
            'status'         => 'downloading',
            'progress'       => 0,
            'current_action' => 'Downloading',
            'error_message'  => null,
        ]);

        try {
            $response = $client->get($supplier->source_url);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException('HTTP status ' . $response->getStatusCode());
            }

            // 2) Визначаємо розширення
            $ext = $config['ext']
                ?? $this->guessExtensionFromHeaders($response->getHeaderLine('Content-Type'))
                ?? 'xlsx';

            $filenameWithExt = "price_{$supplier->id}_{$dateDir}.{$ext}";
            $relativePath    = "{$basePath}/{$filenameWithExt}";

            Storage::makeDirectory($basePath);
            Storage::put($relativePath, $response->getBody());

            // 3) Оновлюємо запис у БД – вже з розширенням
            $priceFile->update([
                'filename'       => $relativePath,
                'original_name'  => $this->guessFilenameFromHeaders($response->getHeaderLine('Content-Disposition')) ?? $filenameWithExt,
                'extension'      => $ext,
                'status'         => 'downloaded',
                'progress'       => 100,
                'current_action' => 'Downloaded',
                'error_message'  => null,
            ]);

            $this->info("  Saved to storage/app/{$relativePath}");
        } catch (\Throwable $e) {
            $priceFile->update([
                'status'         => 'failed',
                'current_action' => 'Download failed',
                'error_message'  => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function fetchFromFtp(Supplier $supplier): void
    {
        $this->warn("  [TODO] FTP fetching not implemented yet for {$supplier->code}.");
    }

    protected function fetchFromGdrive(Supplier $supplier): void
    {
        $this->warn("  [TODO] GDrive fetching not implemented yet for {$supplier->code}.");
    }

    protected function guessExtensionFromHeaders(?string $contentType): ?string
    {
        if (!$contentType) {
            return null;
        }

        $map = [
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/csv'   => 'csv',
            'text/plain' => 'csv',
        ];

        foreach ($map as $type => $ext) {
            if (str_contains($contentType, $type)) {
                return $ext;
            }
        }

        return null;
    }

    protected function guessFilenameFromHeaders(?string $contentDisposition): ?string
    {
        if (!$contentDisposition) {
            return null;
        }

        if (preg_match('/filename="([^"]+)"/', $contentDisposition, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
