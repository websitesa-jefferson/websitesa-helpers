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
                $dsn = 'mysql:host=mysql-8.0;dbname=websitesa-auth-api;charset=utf8';
                $dbUserEnv = getenv('DB_USER');
                $user = ($dbUserEnv !== false && $dbUserEnv !== '') ? $dbUserEnv : 'root';
                $dbPasswordEnv = getenv('DB_PASSWORD');
                $password = ($dbPasswordEnv !== false) ? $dbPasswordEnv : '';

                $pdo = new \PDO($dsn, $user, $password, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_TIMEOUT => 2,
                ]);

                $stmt = $pdo->query('SELECT url FROM cors WHERE status_id = 1');
                if ($stmt !== false) {
                    $origins = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                    $list = [];
                    foreach ($origins as $origin) {
                        if (is_string($origin)) {
                            $list[] = $origin;
                        }
                    }
                    $allowedOrigins = $list;
                }

                // Grava no cache
                $dir = dirname($cacheFile);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                if (file_exists($cacheFile) && !is_writable($cacheFile)) {
                    @unlink($cacheFile);
                }

                file_put_contents($cacheFile, json_encode($allowedOrigins));
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
        }

        return $allowedOrigins;
    }
}
