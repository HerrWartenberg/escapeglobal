<?php
// RSS-Proxy: holt den Feed serverseitig, gibt JSON zurück
// Kein CORS-Problem, keine Drittanbieter nötig.

$RSS_URL = 'https://www.realschule-florastrasse.de/?feed=rss2&cat=34';
$CACHE_FILE = __DIR__ . '/rss-cache.json';
$CACHE_TTL  = 300; // Sekunden (5 Minuten)

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Cache zurückgeben wenn frisch genug
if (file_exists($CACHE_FILE) && (time() - filemtime($CACHE_FILE)) < $CACHE_TTL) {
    readfile($CACHE_FILE);
    exit;
}

// Feed holen
$ctx = stream_context_create(['http' => [
    'timeout'        => 8,
    'user_agent'     => 'Mozilla/5.0 (RSS-Dashboard)',
    'ignore_errors'  => true,
]]);
$xml = @file_get_contents($RSS_URL, false, $ctx);

if ($xml === false) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Feed konnte nicht geladen werden.']);
    exit;
}

// XML parsen
libxml_use_internal_errors(true);
$feed = simplexml_load_string($xml);
if (!$feed) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Feed konnte nicht geparst werden.']);
    exit;
}

$items = [];
foreach ($feed->channel->item as $item) {
    $ns      = $item->getNamespaces(true);
    $media   = isset($ns['media']) ? $item->children($ns['media']) : null;
    $content = isset($ns['content']) ? $item->children($ns['content']) : null;

    // Bild aus verschiedenen möglichen Quellen
    $thumbnail = null;
    if ($media && isset($media->thumbnail)) {
        $attr = $media->thumbnail->attributes();
        $thumbnail = (string)($attr['url'] ?? '');
    }
    if (!$thumbnail && $content && isset($content->encoded)) {
        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string)$content->encoded, $m);
        $thumbnail = $m[1] ?? null;
    }
    if (!$thumbnail) {
        $desc = (string)($item->description ?? '');
        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $desc, $m);
        $thumbnail = $m[1] ?? null;
    }

    $items[] = [
        'title'       => (string)$item->title,
        'link'        => (string)$item->link,
        'pubDate'     => (string)$item->pubDate,
        'description' => (string)$item->description,
        'thumbnail'   => $thumbnail,
    ];
}

$result = json_encode(['status' => 'ok', 'items' => $items], JSON_UNESCAPED_UNICODE);

// Cache schreiben (ignoriere Fehler falls kein Schreibrecht)
@file_put_contents($CACHE_FILE, $result);

echo $result;
