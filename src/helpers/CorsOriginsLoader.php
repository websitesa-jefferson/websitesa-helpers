<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helpers;

class CorsOriginsLoader
{
    /**
     * Carrega as origens permitidas a partir do cache ou do banco de dados.
     *
     * @param string $runtimePath Caminho absoluto para a pasta runtime.
     * @return string[]
     */
    public static function loadAllowedOrigins(string $runtimePath): array
    {
        $cacheFile = $runtimePath . '/tmp/cors_origins.json';
        $cacheLifetime = 300; // 5 minutos
        /** @var string[] $allowedOrigins */
        $allowedOrigins = [];

        // 1. Tenta carregar do cache local
        if (file_exists($cacheFile)) {
            $mtime = filemtime($cacheFile);
            if ($mtime !== false && (time() - $mtime) < $cacheLifetime) {
                $content = file_get_contents($cacheFile);
                if ($content !== false) {
                    $decoded = json_decode($content, true);
                    if (is_array($decoded)) {
                        $list = [];
                        foreach ($decoded as $item) {
                            if (is_string($item)) {
                                $list[] = $item;
                            }
                        }
                        $allowedOrigins = $list;
                    }
                }
            }
        }

        // 2. Se o cache expirou ou não existe, consulta o banco
        if ($allowedOrigins === []) {
            try {
                $hostsToTry = [];
                $dbHostEnv = getenv('DB_HOST');
                if ($dbHostEnv !== false && $dbHostEnv !== '') {
                    $hostsToTry[] = $dbHostEnv;
                }
                $hostsToTry[] = 'mysql80';

                $dbNameEnv = getenv('DB_NAME');
                $dbname = ($dbNameEnv !== false && $dbNameEnv !== '') ? $dbNameEnv : 'websitesa-auth-api';

                $dbUserEnv = getenv('DB_USER');
                $user = ($dbUserEnv !== false && $dbUserEnv !== '') ? $dbUserEnv : 'root';
                $dbPasswordEnv = getenv('DB_PASSWORD');
                $password = ($dbPasswordEnv !== false) ? $dbPasswordEnv : '';

                $pdo = null;
                $lastException = null;

                foreach (array_unique($hostsToTry) as $host) {
                    try {
                        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";
                        $pdo = new \PDO($dsn, $user, $password, [
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                            \PDO::ATTR_TIMEOUT => 2,
                        ]);
                        break;
                    } catch (\Throwable $e) {
                        $lastException = $e;
                    }
                }

                if ($pdo !== null) {
                    $stmt = $pdo->query('SELECT url FROM cors WHERE status_id = 1');
                    if ($stmt !== false) {
                        $origins = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                        $list = [];
                        foreach ($origins as $origin) {
                            if (is_string($origin)) {
                                $list[] = trim($origin);
                            }
                        }
                        $allowedOrigins = array_values(array_unique(array_filter($list, static fn (string $origin): bool => $origin !== '')));
                    }
                } elseif ($lastException !== null) {
                    throw $lastException;
                }
            } catch (\Throwable $e) {
                // Fallback de segurança se o banco estiver indisponível
                $allowedOrigins = [
                    'https://auth-app.websitesa.dev.br',
                    'https://pacs-app.websitesa.dev.br',
                    'https://ris-app.websitesa.dev.br',
                    'https://auth-app.websitesa.com.br',
                    'https://pacs-app.websitesa.com.br',
                    'https://ris-app.websitesa.com.br',
                ];
            }

            // Grava no cache
            if ($allowedOrigins !== []) {
                $dir = dirname($cacheFile);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                if (file_exists($cacheFile) && !is_writable($cacheFile)) {
                    @unlink($cacheFile);
                }

                file_put_contents($cacheFile, json_encode($allowedOrigins, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }
        }

        return $allowedOrigins;
    }
}
