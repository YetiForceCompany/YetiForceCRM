<?php
/**
 * YetiForce status informations.
 *
 * @package   Cron
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$config = \AppConfig::module('YetiForce');
if (empty($config['statusUrl'])) {
	return;
}
$url = $config['statusUrl'];
unset($config['statusUrl']);
$status = new \App\YetiForce\Status();
$info = [];
foreach ($config as $name => $state) {
	if ($state) {
		$info[$name] = \App\Json::encode(call_user_func([$status, 'get' . ucfirst($name)]));
	}
}
try {
	(new \GuzzleHttp\Client())->post($url, \App\RequestHttp::getOptions() + [
			'timeout' => 5,
			'form_params' => $info]);
} catch (\Throwable $e) {
	\App\Log::warning('Not possible to connect to the server status' . PHP_EOL . $e->getMessage(), 'YetiForceStatus');
}
