<?php
require __DIR__ . '/../vendor/autoload.php';

use KyPHP\KyPHP;

function fetchMenu(): array {
    $menu = [];
    try {
        $client = new KyPHP();
        $res = $client
            ->get('https://urbanglamhousekenya.com/api/menu')
            ->header('User-Agent', 'Mozilla/5.0')
            ->sendJson();

        if (isset($res['data']) && is_array($res['data'])) {
            $menu = $res['data'];
        }
    } catch (\Throwable $e) {
        // silent
    }

    return $menu;
}
