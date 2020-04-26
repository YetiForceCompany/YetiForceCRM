<?php
/**
 * Script to send mails from queue by RabbitMQ.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
if (PHP_SAPI !== 'cli') {
	return;
}
$dir = __DIR__ . '/../../../../';
chdir($dir);
require_once $dir . 'include/main/WebUI.php';
$checkLibrary = true;
require_once $dir . 'include/RequirementsValidation.php';
\App\Process::$requestMode = 'Worker';
\App\Utils\ConfReport::$sapi = 'cron';
App\Session::init();
App\User::setCurrentUserId(Users::getActiveAdminId());
$engine = new App\Queues\RabbitMQ();
$channel = $engine->getChannel();
$exchangeEngine = new \App\Queues\Exchanges\Mail();
$exchangeEngine->setEngine($engine);
$exchangeEngine->getExchangeEngine()->declareQueue();
$worker = new App\Queues\Workers\Mail();
$callback = function ($msg) use ($worker) {
	try {
		$data = \App\Json::decode($msg->body);
		$data = (new App\Db\Query())->from('s_#__mail_queue')->where(['id' => $data['id']])->one();
		if ($data['status'] === 1) {
			$worker->setData($data);
			$worker->process();
		}
	} catch (\Throwable $ex) {
		App\Log::error($ex->getMessage());
		exit;
	}
};
$channel->basic_consume($exchangeEngine->getQueueName(), '', false, true, false, false, $callback);
while ($channel->is_consuming()) {
	$channel->wait();
}