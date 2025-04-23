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
$hostIpAddress = trim(shell_exec('ipconfig getifaddr en0'));

$cloudflare = new CloudflareRecord($apiToken, $zoneId);
var_dump($cloudflare->getRecord(123456, 'example.com'));