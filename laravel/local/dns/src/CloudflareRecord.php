<?php
declare(strict_types=1);

namespace Dns;

class CloudflareRecord implements DnsRecordInterface
{
    public function __construct(
        readonly private string $apiToken,
        readonly private string $zoneId
    ) {}

    public function getRecord(int $recordId, string $recordName): string
    {
        // Simulate fetching a DNS record from Cloudflare
        return $this->apiToken . ' ' . $this->zoneId;
    }
}
