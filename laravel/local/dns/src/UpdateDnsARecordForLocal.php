<?php
namespace Dns;

class UpdateDnsARecordForLocal
{
    public function __construct(
        readonly private DnsRecordInterface $dnsRecord,
    ) {}

    public function run(): void
    {  
        // 現在のAレコードを取得
        $current_record_content = $this->dnsRecord->getRecord();
        // ホストマシンのIPアドレスを取得（一旦Mac上で動作させること前提にしている）
        $host_ip_address = trim(shell_exec('ipconfig getifaddr en0'));
        if ($current_record_content === $host_ip_address) {
            var_dump('Aレコードは変更されていません');
            return;
        }
        // PCに割り当てられるIPアドレスは通常はDHCPにより動的に取得され変わることがあるため
        // 変わっていたらAレコードを現在のホストマシンのIPアドレスに更新する
        $this->dnsRecord->updateRecord($host_ip_address);
        var_dump('Aレコードを更新しました');
    }
}