<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VirusScanner
{
	/**
	 * Scan a file path for viruses. Returns an array with keys:
	 * - clean: bool
	 * - message: string
	 *
	 * Uses ClamAV if available and CHAT_VIRUS_SCAN=true. Falls back to a no-op (clean).
	 */
	public function scan(string $absolutePath): array
	{
		$shouldScan = filter_var(env('CHAT_VIRUS_SCAN', false), FILTER_VALIDATE_BOOL);
		if (!$shouldScan) {
			return ['clean' => true, 'message' => 'Scanning disabled'];
		}

		// Basic guard
		if (!is_file($absolutePath) || !is_readable($absolutePath)) {
			return ['clean' => false, 'message' => 'File not readable'];
		}

		// Try clamscan if present
		$clam = $this->findClamBinary();
		if ($clam) {
			try {
				$cmd = escapeshellcmd($clam) . ' --no-summary ' . escapeshellarg($absolutePath);
				$output = [];
				$exitCode = 0;
				@exec($cmd, $output, $exitCode);
				// clamscan exits with 0 if OK, 1 if virus found, 2 if error
				if ($exitCode === 0) {
					return ['clean' => true, 'message' => 'OK'];
				}
				if ($exitCode === 1) {
					return ['clean' => false, 'message' => 'Infected'];
				}
				Log::warning('ClamAV scan error', ['path' => $absolutePath, 'output' => $output]);
				return ['clean' => false, 'message' => 'Scan error'];
			} catch (\Throwable $e) {
				Log::warning('ClamAV scan exception: ' . $e->getMessage());
				return ['clean' => false, 'message' => 'Scan exception'];
			}
		}

		// No scanner available; mark clean but log that scanning is unavailable
		Log::info('Virus scanning enabled but ClamAV not found; skipping', ['path' => $absolutePath]);
		return ['clean' => true, 'message' => 'Scanner not available'];
	}

	protected function findClamBinary(): ?string
	{
		$candidates = [
			'clamscan',
			'C:\\Program Files\\ClamAV\\clamscan.exe',
			'C:\\ClamAV\\clamscan.exe',
		];
		foreach ($candidates as $bin) {
			$which = $this->which($bin);
			if ($which) return $which;
			if (is_executable($bin)) return $bin;
		}
		return null;
	}

	protected function which(string $bin): ?string
	{
		$out = [];
		$code = 0;
		@exec((stripos(PHP_OS, 'WIN') === 0 ? 'where ' : 'which ') . escapeshellarg($bin), $out, $code);
		if ($code === 0 && !empty($out[0])) {
			return $out[0];
		}
		return null;
	}
}
