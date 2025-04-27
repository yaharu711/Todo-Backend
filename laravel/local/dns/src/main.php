<?php
declare(strict_types=1);

namespace Dns;

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// プロジェクトルートを渡して .env を読み込む
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiToken = $_ENV['API_TOKEN'];
$zoneId = $_ENV['ZONE_ID'];
$api_a_record_id = $_ENV['API_A_RECORD_ID'];
$api_a_record_name = $_ENV['API_A_RECORD_NAME'];
$frontend_a_record_name = $_ENV['FRONTEND_A_RECORD_NAME'];
$frontend_a_record_id = $_ENV['FRONTEND_A_RECORD_ID'];

# APIのAレコードを更新
$cloudflare_for_api = new CloudflareRecord(
    $apiToken,
    $zoneId,
    $api_a_record_id,
    $api_a_record_name
);
$update_dns_record_for_api = new UpdateDnsARecordForLocal($cloudflare_for_api);
$update_dns_record_for_api->run();
# フロントエンドのAレコードを更新
$cloudflare_for_frontend = new CloudflareRecord(
    $apiToken,
    $zoneId,
    $frontend_a_record_id,
    $frontend_a_record_name
);
$update_dns_record_for_frontend = new UpdateDnsARecordForLocal($cloudflare_for_frontend);
$update_dns_record_for_frontend->run();
