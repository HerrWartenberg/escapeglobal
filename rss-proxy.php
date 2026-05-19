<?php
$RSS_URL    = 'https://www.realschule-florastrasse.de/?feed=rss2&cat=34';
$CACHE_FILE = __DIR__ . '/rss-cache.json';
$CACHE_TTL  = 300;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// ?test=1 gibt Diagnose-Infos zurück
if (!empty($_GET['test'])) {
    $curl   = function_exists('curl_init');
    $fgc    = ini_get('allow_url_fopen');
    $write  = is_writable(__DIR__);

    $fetch  = 'nicht getestet';
    if ($curl) {
        $ch = curl_init($RSS_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (RSS-Dashboard)',
            CURLOPT_NOBODY         => true,
        ]);
        curl_exec($ch);
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err   = curl_error($ch);
        curl_close($ch);
        $fetch = $err ? "cURL-Fehler: $err" : "HTTP $code";
    } elseif ($fgc) {
        $ctx = stream_context_create(['http' => ['timeout' => 6]]);
        $r   = @file_get_contents($RSS_URL, false, $ctx);
        $fetch = $r !== false ? 'OK (file_get_contents)' : 'file_get_contents fehlgeschlagen';
    }

    echo json_encode([
        'php'              => PHP_VERSION,
        'curl'             => $curl ? 'verfügbar' : 'nicht verfügbar',
        'allow_url_fopen'  => $fgc  ? 'an'        : 'aus',
        'verzeichnis_schreibbar' => $write ? 'ja' : 'nein',
        'feed_erreichbar'  => $fetch,
        'cache_datei'      => $CACHE_FILE,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Frischen Cache zurückgeben
if (file_exists($CACHE_FILE) && (time() - filemtime($CACHE_FILE)) < $CACHE_TTL) {
    readfile($CACHE_FILE);
    exit;
}

// Feed holen
function fetchUrl(string $url): array {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (RSS-Dashboard)',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body !== false && $err === '') return ['ok' => true,  'body' => $body];
        return ['ok' => false, 'msg' => "cURL: $err (HTTP $code)"];
    }
    if (ini_get('allow_url_fopen')) {
        $ctx  = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'Mozilla/5.0']]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body !== false) return ['ok' => true, 'body' => $body];
        return ['ok' => false, 'msg' => 'file_get_contents fehlgeschlagen'];
    }
    return ['ok' => false, 'msg' => 'Weder cURL noch allow_url_fopen verfügbar'];
}

$result = fetchUrl($RSS_URL);
if (!$result['ok']) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => $result['msg']]);
    exit;
}

libxml_use_internal_errors(true);
$feed = simplexml_load_string($result['body']);
if (!$feed) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'XML konnte nicht geparst werden.']);
    exit;
}

$items = [];
foreach ($feed->channel->item as $item) {
    $ns    = $item->getNamespaces(true);
    $media = isset($ns['media'])   ? $item->children($ns['media'])   : null;
    $enc   = isset($ns['content']) ? $item->children($ns['content']) : null;

    $thumbnail = null;
    if ($media && isset($media->thumbnail)) {
        $a = $media->thumbnail->attributes();
        $thumbnail = (string)($a['url'] ?? '');
    }
    if (!$thumbnail && $enc && isset($enc->encoded)) {
        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string)$enc->encoded, $m);
        $thumbnail = $m[1] ?? null;
    }
    if (!$thumbnail) {
        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string)($item->description ?? ''), $m);
        $thumbnail = $m[1] ?? null;
    }

    $items[] = [
        'title'       => html_entity_decode((string)$item->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'link'        => (string)$item->link,
        'pubDate'     => (string)$item->pubDate,
        'description' => (string)$item->description,
        'thumbnail'   => $thumbnail ?: null,
    ];
}

$json = json_encode(['status' => 'ok', 'items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@file_put_contents($CACHE_FILE, $json);
echo $json;
