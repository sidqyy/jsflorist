<?php

namespace Config;

use CodeIgniter\Events\Events;

Events::on('pre_system', function () {
    if (isset($_SERVER['HTTP_HOST'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $config = config('App');
        $config->baseURL = "{$protocol}://{$host}/";
    }
});
