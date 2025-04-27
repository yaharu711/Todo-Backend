<?php
declare(strict_types=1);

namespace Dns;

interface DnsRecordInterface
{
    public function getRecord(): ?string;
    public function updateRecord(string $content): void;
}
