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
$api_a_record_name = $_ENV['API_A_RECORD_NAME'];
$frontend_a_record_name = $_ENV['FRONTEND_A_RECORD_NAME'];
$api_a_record_id = $_ENV['API_A_RECORD_ID'];
$frontend_a_record_id = $_ENV['FRONTEND_A_RECORD_ID'];
$hostIpAddress = trim(shell_exec('ipconfig getifaddr en0'));

$cloudflare = new CloudflareRecord(
    $apiToken,
    $zoneId,
    $api_a_record_id,
    $api_a_record_name
);
var_dump($cloudflare->getRecord());
