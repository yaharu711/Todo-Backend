<?php
declare(strict_types=1);

namespace Dns;

class CloudflareRecord implements DnsRecordInterface
{
    private const CF_API_ENDPOINT = 'https://api.cloudflare.com/client/v4/zones/';

    public function __construct(
        readonly private string $api_token,
        readonly private string $zone_id,
        readonly private string $record_id,
        readonly private string $record_name,
    ) {}

    public function getRecord(): ?string
    {
        // Simulate fetching a DNS record from Cloudflare
        $url = self::CF_API_ENDPOINT . $this->zone_id . '/dns_records/' . $this->record_id;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, // trueでないと標準出力されてしまう
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->api_token,
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

    public function updateRecord(string $content): void
    {
        $url = self::CF_API_ENDPOINT . $this->zone_id . '/dns_records/' . $this->record_id;
        $payload = json_encode([
            'type'    => 'A',
            'name'    => $this->record_name,
            'content' => $content,
        ]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, // trueでないと標準出力されてしまう
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->api_token,
                'Content-Type: application/json',
            ],
        ]);
        $res = curl_exec($ch);
        if (!$res) {
            error_log('DNSレコード更新エラー: ' . curl_error($ch));
            curl_close($ch);
            return;
        }
        curl_close($ch);
    
        $obj = json_decode($res, true);
        if (!($obj['success'] ?? false)) {
            error_log('DNSレコード更新に失敗: ' . json_encode($obj));
        } else {
            echo date('c') . " - DNSレコードを {$content} に更新しました\n";
        }
    }
}
