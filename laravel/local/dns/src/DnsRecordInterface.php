<?php
declare(strict_types=1);

namespace Dns;

interface DnsRecordInterface
{
    public function getRecord(int $recordId, string $recordName): string;
}