<?php
// RSS-Proxy mit cURL (Fallback: file_get_contents)
// Lege diese Datei neben dashboard.html auf deinem Webserver ab.

$RSS_URL    = 'https://www.realschule-florastrasse.de/?feed=rss2&cat=34';
$CACHE_FILE = __DIR__ . '/rss-cache.json';
$CACHE_TTL  = 300; // Sekunden (5 Minuten)

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Frischen Cache direkt zurückgeben
if (file_exists($CACHE_FILE) && (time() - filemtime($CACHE_FILE)) < $CACHE_TTL) {
    readfile($CACHE_FILE);
    exit;
}

// Feed holen – cURL bevorzugt, file_get_contents als Fallback
function fetchUrl(string $url): string|false {
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
        curl_close($ch);
        return ($body !== false && $err === '') ? $body : false;
    }
    // Fallback
    $ctx = stream_context_create(['http' => [
        'timeout'    => 8,
        'user_agent' => 'Mozilla/5.0 (RSS-Dashboard)',
    ]]);
    return @file_get_contents($url, false, $ctx);
}

$xml = fetchUrl($RSS_URL);

if ($xml === false) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Feed konnte nicht geladen werden.']);
    exit;
}

libxml_use_internal_errors(true);
$feed = simplexml_load_string($xml);
if (!$feed) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Feed konnte nicht geparst werden.']);
    exit;
}

$items = [];
foreach ($feed->channel->item as $item) {
    $ns    = $item->getNamespaces(true);
    $media = isset($ns['media'])   ? $item->children($ns['media'])   : null;
    $enc   = isset($ns['content']) ? $item->children($ns['content']) : null;

    // Bild ermitteln
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

$result = json_encode(['status' => 'ok', 'items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

@file_put_contents($CACHE_FILE, $result);

echo $result;
