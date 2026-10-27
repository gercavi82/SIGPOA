<?php

namespace App\Integrations\Ibmi;

use PDO;
use Throwable;

class IbmiConnection
{
    private ?PDO $pdo = null;

    /**
     * Obtiene o crea la conexión PDO ODBC hacia IBM i / AS400.
     */
    public function getPdo(): ?PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $server = env('AS400_SERVER', env('DB_IBMI_HOST', '190.152.8.185'));
        $user = env('AS400_USER_ID', env('DB_IBMI_USERNAME', 'DGACCONEXI'));
        $password = env('AS400_PASSWORD', env('DB_IBMI_PASSWORD', 'DGACTIC20@'));
        $library = env('AS400_LIBRARY', env('DB_IBMI_SCHEMA', 'DGACDATPRO'));
        $driver = env('AS400_DRIVER', 'iSeries Access ODBC Driver');
        $timeout = (int) env('AS400_TIMEOUT', 10);

        $dsn = "DRIVER={{$driver}};SYSTEM={$server};DATABASE=S10a1a05;DBQ={$library};CMT=0;";

        try {
            $this->pdo = new PDO("odbc:$dsn", $user, $password, [
                PDO::ATTR_TIMEOUT => $timeout,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            return $this->pdo;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Ejecuta una consulta SELECT segura en IBM i convirtiendo strings a UTF-8 válido.
     *
     * @param  array<string|int, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $params = []): array
    {
        $pdo = $this->getPdo();
        if (! $pdo) {
            return [];
        }

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->cleanUtf8($rows);
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Limpia y convierte recursivamente valores binarios/ISO-8859-1 a UTF-8 válido para PostgreSQL.
     */
    public function cleanUtf8(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([$this, 'cleanUtf8'], $value);
        }

        if (is_string($value)) {
            if (! mb_check_encoding($value, 'UTF-8')) {
                $converted = @mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');

                return is_string($converted) ? $converted : mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
            }

            return $value;
        }

        return $value;
    }
}
