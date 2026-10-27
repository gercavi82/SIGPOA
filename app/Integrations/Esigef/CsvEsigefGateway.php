<?php

namespace App\Integrations\Esigef;

use App\Contracts\EsigefGatewayInterface;
use App\Enums\ImportBatchRowStatus;
use App\Enums\ImportBatchStatus;
use App\Models\BudgetCertification;
use App\Models\ImportBatch;
use App\Models\ImportBatchRow;
use App\Models\PoaActivity;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CsvEsigefGateway implements EsigefGatewayInterface
{
    /**
     * Parse and stage an eSIGEF CSV file into an ImportBatch with rows.
     *
     * @return array{batch: ImportBatch, rows_count: int, valid_count: int, invalid_count: int, unmatched_count: int}
     */
    public function importAccrualCsv(UploadedFile $file, int $year, ?int $month, User $user): array
    {
        $filePath = $file->getRealPath();
        if (! $filePath || ! file_exists($filePath)) {
            throw new InvalidArgumentException('El archivo CSV no pudo ser leído.');
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false || trim($fileContent) === '') {
            throw new InvalidArgumentException('El archivo CSV está vacío.');
        }

        $fileHash = hash('sha256', $fileContent);

        $existingDuplicate = ImportBatch::query()->where('file_hash', $fileHash)->first();
        if ($existingDuplicate) {
            throw new InvalidArgumentException("El archivo ya fue importado previamente en el lote {$existingDuplicate->batch_code}.");
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($fileContent));
        if (! $lines || count($lines) < 2) {
            throw new InvalidArgumentException('El archivo CSV debe contener una fila de encabezados y al menos un registro.');
        }

        $firstLine = array_shift($lines);
        $delimiter = str_contains($firstLine, ';') ? ';' : (str_contains($firstLine, "\t") ? "\t" : ',');
        $rawHeaders = str_getcsv($firstLine, $delimiter);
        $headers = array_map(fn (string $h) => Str::snake(trim(strtolower($h))), $rawHeaders);

        return DB::transaction(function () use ($file, $fileHash, $lines, $delimiter, $headers, $year, $month, $user) {
            $batchCode = 'LOTE-'.date('YmdHis').'-'.Str::upper(Str::random(4));

            $batch = ImportBatch::query()->create([
                'batch_code' => $batchCode,
                'original_filename' => $file->getClientOriginalName(),
                'file_hash' => $fileHash,
                'year' => $year,
                'month' => $month,
                'status' => ImportBatchStatus::PENDING,
                'imported_by' => $user->id,
            ]);

            $totalRows = 0;
            $validRows = 0;
            $invalidRows = 0;
            $unmatchedRows = 0;

            $cachedActivities = PoaActivity::query()->get()->keyBy(fn (PoaActivity $a) => (string) $a->number);
            $cachedCertifications = BudgetCertification::query()->where('year', $year)->get()->keyBy('certification_number');

            foreach ($lines as $index => $line) {
                if (trim($line) === '') {
                    continue;
                }

                $totalRows++;
                $rowNumber = $totalRows;
                $cols = str_getcsv($line, $delimiter);

                $rowMap = [];
                foreach ($headers as $colIndex => $headerName) {
                    $rowMap[$headerName] = $cols[$colIndex] ?? null;
                }

                $curNumber = trim((string) ($rowMap['cur_number'] ?? $rowMap['cur'] ?? $rowMap['numero_cur'] ?? ''));
                $curLine = (int) ($rowMap['cur_line'] ?? $rowMap['linea_cur'] ?? $rowMap['linea'] ?? 1);
                $certNumber = trim((string) ($rowMap['certification_number'] ?? $rowMap['certificacion'] ?? $rowMap['numero_certificacion'] ?? ''));
                $activityCode = trim((string) ($rowMap['activity_code'] ?? $rowMap['actividad'] ?? $rowMap['numero_actividad'] ?? $rowMap['codigo_actividad'] ?? ''));
                $rawDate = trim((string) ($rowMap['accrual_date'] ?? $rowMap['fecha'] ?? $rowMap['fecha_devengado'] ?? ''));
                $rawAmount = trim((string) ($rowMap['amount'] ?? $rowMap['monto'] ?? $rowMap['monto_devengado'] ?? '0'));
                $cleanAmount = (float) str_replace(['$', ' '], '', str_replace(',', '.', $rawAmount));

                $parsedDate = null;
                $errorMessage = null;
                $rowStatus = ImportBatchRowStatus::VALID;

                if ($curNumber === '') {
                    $rowStatus = ImportBatchRowStatus::INVALID;
                    $errorMessage = 'Número de CUR no especificado.';
                } elseif ($cleanAmount <= 0) {
                    $rowStatus = ImportBatchRowStatus::INVALID;
                    $errorMessage = 'El monto debe ser mayor a cero.';
                } else {
                    if ($rawDate !== '') {
                        try {
                            $parsedDate = Carbon::parse($rawDate)->toDateString();
                        } catch (\Throwable) {
                            $rowStatus = ImportBatchRowStatus::INVALID;
                            $errorMessage = 'Formato de fecha inválido.';
                        }
                    } else {
                        $parsedDate = now()->toDateString();
                    }
                }

                $matchedActivity = null;
                $matchedCertification = null;

                if ($rowStatus === ImportBatchRowStatus::VALID) {
                    if ($activityCode !== '') {
                        $matchedActivity = $cachedActivities->get($activityCode)
                            ?? PoaActivity::query()->where('number', $activityCode)->first()
                            ?? (is_numeric($activityCode) ? PoaActivity::query()->find((int) $activityCode) : null);
                    }

                    if ($certNumber !== '') {
                        $matchedCertification = $cachedCertifications->get($certNumber);
                    }

                    if (! $matchedActivity) {
                        $rowStatus = ImportBatchRowStatus::UNMATCHED;
                        $errorMessage = "Actividad POA con código '{$activityCode}' no encontrada.";
                    }
                }

                if ($rowStatus === ImportBatchRowStatus::VALID) {
                    $validRows++;
                } elseif ($rowStatus === ImportBatchRowStatus::UNMATCHED) {
                    $unmatchedRows++;
                } else {
                    $invalidRows++;
                }

                ImportBatchRow::query()->create([
                    'import_batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'cur_number' => $curNumber !== '' ? $curNumber : 'SIN_CUR',
                    'cur_line' => $curLine > 0 ? $curLine : 1,
                    'certification_number' => $certNumber !== '' ? $certNumber : null,
                    'activity_code' => $activityCode !== '' ? $activityCode : null,
                    'poa_activity_id' => $matchedActivity?->id,
                    'budget_certification_id' => $matchedCertification?->id,
                    'accrual_date' => $parsedDate,
                    'amount' => $cleanAmount,
                    'status' => $rowStatus,
                    'error_message' => $errorMessage,
                    'raw_payload' => $rowMap,
                ]);
            }

            $batchStatus = ($invalidRows === 0 && $unmatchedRows === 0)
                ? ImportBatchStatus::VALIDATED
                : ($validRows > 0 ? ImportBatchStatus::PARTIAL : ImportBatchStatus::FAILED);

            $batch->update([
                'status' => $batchStatus,
                'total_rows' => $totalRows,
                'valid_rows' => $validRows,
                'invalid_rows' => $invalidRows,
                'unmatched_rows' => $unmatchedRows,
            ]);

            return [
                'batch' => $batch,
                'rows_count' => $totalRows,
                'valid_count' => $validRows,
                'invalid_count' => $invalidRows,
                'unmatched_count' => $unmatchedRows,
            ];
        });
    }

    /**
     * Validate CSV row payload structure.
     *
     * @param  array<string, mixed>  $row
     * @return array{is_valid: bool, error: ?string, data: array<string, mixed>}
     */
    public function validateRow(array $row): array
    {
        $cur = trim((string) ($row['cur_number'] ?? $row['cur'] ?? ''));
        $amount = (float) ($row['amount'] ?? $row['monto'] ?? 0);

        if ($cur === '') {
            return ['is_valid' => false, 'error' => 'CUR requerido', 'data' => $row];
        }

        if ($amount <= 0) {
            return ['is_valid' => false, 'error' => 'Monto debe ser mayor a 0', 'data' => $row];
        }

        return ['is_valid' => true, 'error' => null, 'data' => $row];
    }
}
