<?php
declare(strict_types=1);

namespace Dns;

class CloudflareRecord implements DnsRecordInterface
{
    private const CF_API_ENDPOINT = 'https://api.cloudflare.com/client/v4/zones/';

    public function __construct(
        readonly private string $apiToken,
        readonly private string $zoneId,
        readonly private string $recordId,
        readonly private string $recordName,
    ) {}

    public function getRecord(): ?string
    {
        // Simulate fetching a DNS record from Cloudflare
        $url = self::CF_API_ENDPOINT . $this->zoneId . '/dns_records/' . $this->recordId;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiToken,
                'Content-Type: application/json',
            ],
        ]);
        $res = curl_exec($ch);

        if (!$res) {
            error_log('DNSレコード取得エラー: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $obj = json_decode($res, true);
        if (!($obj['success'] ?? false)) {
            error_log('DNSレコード取得に失敗: ' . json_encode($obj));
            return null;
        }

        return $obj['result']['content'] ?? null;
    }
}
