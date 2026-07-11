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

        // Obtém o IP do host gateway (Docker) ou o proxy reverso de forma dinâmica
        $ip = self::getDockerIp();

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

    /**
     * Tenta obter o IP do gateway do Docker (Host) ou do Proxy de forma dinâmica.
     */
    private static function getDockerIp(): string
    {
        // 1. Tenta resolver host.docker.internal (mapeado via extra_hosts)
        $ip = gethostbyname('host.docker.internal');
        if ($ip !== 'host.docker.internal') {
            return $ip;
        }

        // 2. Tenta ler o gateway padrão do Linux (/proc/net/route)
        if (is_readable('/proc/net/route')) {
            $routes = file('/proc/net/route');
            if ($routes !== false) {
                foreach ($routes as $line) {
                    $parts = preg_split('/\s+/', trim($line));
                    if (isset($parts[1], $parts[2]) && $parts[1] === '00000000') {
                        $hexIp = $parts[2];
                        if (strlen($hexIp) === 8) {
                            $octets = str_split($hexIp, 2);
                            return hexdec($octets[3]) . '.' . hexdec($octets[2]) . '.' . hexdec($octets[1]) . '.' . hexdec($octets[0]);
                        }
                    }
                }
            }
        }

        // 3. Tenta resolver o proxy reverso pelo nome do container
        $ip = gethostbyname('nginx-proxy');
        if ($ip !== 'nginx-proxy') {
            return $ip;
        }

        // 4. Fallback padrão da rede docker bridge
        return '172.20.0.1';
    }
}
