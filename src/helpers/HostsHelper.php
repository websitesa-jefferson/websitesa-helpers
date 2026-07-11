<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helpers;

use app\models\Cors;
use app\models\Origin;
use Yii;
use yii\httpclient\Client;

class HostsHelper
{
    /**
     * Atualiza o /etc/hosts local e dispara a sincronização para PACS e RIS.
     */
    public static function syncAll(): void
    {
        $domains = self::getActiveDomains();

        // 1. Atualiza o /etc/hosts do próprio container Auth-API
        self::updateLocalHostsFile($domains);

        // 2. Dispara a sincronização para o PACS e RIS de forma assíncrona
        self::triggerRemoteSync('websitesa-pacs-api-yii2', $domains);
        self::triggerRemoteSync('websitesa-ris-api-yii2', $domains);
    }

    /**
     * Escreve a lista de domínios no arquivo /etc/hosts local.
     *
     * @param array<string> $domains
     */
    public static function updateLocalHostsFile(array $domains): bool
    {
        $hostsFile = '/etc/hosts';
        if (!is_writable($hostsFile)) {
            Yii::error("Arquivo {$hostsFile} não é gravável pelo PHP.", 'hosts-sync');
            return false;
        }

        // Obtém o IP do proxy reverso ou o gateway da rede docker
        $ip = gethostbyname('nginx-proxy');
        if ($ip === 'nginx-proxy') {
            $ip = '172.20.0.1'; // Gateway fallback
        }

        $content = @file_get_contents($hostsFile);
        if ($content === false) {
            $content = '';
        }

        // Remove bloco dinâmico anterior se existir
        $replaced = preg_replace('/# WEBSITESA DYNAMIC START[\s\S]*# WEBSITESA DYNAMIC END/', '', $content);
        $content = is_string($replaced) ? $replaced : $content;
        $content = rtrim($content) . "\n\n";

        // Adiciona novos domínios
        $block = "# WEBSITESA DYNAMIC START\n";
        foreach ($domains as $domain) {
            $block .= "{$ip} {$domain}\n";
        }
        $block .= "# WEBSITESA DYNAMIC END\n";

        return file_put_contents($hostsFile, $content . $block) !== false;
    }

    /**
     * Retorna a lista consolidada de domínios ativos cadastrados.
     *
     * @return array<string>
     */
    private static function getActiveDomains(): array
    {
        $domains = [];

        try {
            $origins = Origin::find()->select(['url'])->where(['status_id' => 1])->column();
            foreach ($origins as $url) {
                $host = parse_url($url, PHP_URL_HOST);
                if (is_string($host) && $host !== '') {
                    $domains[$host] = true;
                }
            }
        } catch (\Exception $e) {
            Yii::error("Erro ao buscar domínios de Origin: " . $e->getMessage(), 'hosts-sync');
        }

        try {
            $cors = Cors::find()->select(['url'])->where(['status_id' => 1])->column();
            foreach ($cors as $url) {
                $host = parse_url($url, PHP_URL_HOST);
                if (is_string($host) && $host !== '') {
                    $domains[$host] = true;
                }
            }
        } catch (\Exception $e) {
            Yii::error("Erro ao buscar domínios de Cors: " . $e->getMessage(), 'hosts-sync');
        }

        return array_keys($domains);
    }

    /**
     * Envia uma requisição HTTP POST interna para sincronizar o hosts do container alvo.
     *
     * @param array<string> $domains
     */
    private static function triggerRemoteSync(string $containerName, array $domains): void
    {
        try {
            $client = new Client();
            $envToken = getenv('INTERNAL_SYNC_TOKEN');
            $token = ($envToken !== false && $envToken !== '') ? $envToken : 'default_secret_token';

            $jsonContent = json_encode(['domains' => $domains]);
            if ($jsonContent === false) {
                $jsonContent = '{"domains":[]}';
            }

            $client->createRequest()
                ->setMethod('POST')
                ->setUrl("http://{$containerName}/v1/internal/sync-hosts")
                ->setHeaders([
                    'X-Internal-Token' => $token,
                    'Content-Type'     => 'application/json',
                ])
                ->setContent($jsonContent)
                ->setOptions(['timeout' => 2]) // Timeout de 2 segundos
                ->send(); // Chamada síncrona para garantir a entrega
        } catch (\Exception $e) {
            Yii::error("Erro ao sincronizar hosts no container {$containerName}: " . $e->getMessage(), 'hosts-sync');
        }
    }
}
