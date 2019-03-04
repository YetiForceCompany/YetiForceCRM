<?php
/**
 * Quasar dev php variables.
 */
require_once 'include/ConfigUtils.php';

$config = [
	'baseURL' => \AppConfig::main('site_URL'),
	'publicDir' => '',
];
// Get auto-reload, hot module replacement index.html from webpack devServer and add php variables
$response = (new \GuzzleHttp\Client())->request('GET', 'localhost:8080/index.html');
$body = $response->getBody();
header('Access-Control-Allow-Origin: *');
header('access-control-allow-headers: *');
header('access-control-allow-methods: GET, POST, PUT, DELETE, OPTIONS');
echo str_replace('<script data-config></script>', '<script data-config-url="' . $config['baseURL'] . '">window.CONFIG=' . json_encode($config) . ';</script>', $body);
exit;
