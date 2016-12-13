<?php
/**
 * Handler updater cron
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
$updaterLimit = 10;
$cronMaxTime = 60;
$interval = 2;
$endTime = time() + $time;
$eventHandler = new App\EventHandler();
$db = \App\Db::getInstance('admin');
do {
	try {
		$rows = (new \App\Db\Query())->from('s_#__handler_updater')->limit($updaterLimit)->all($db);
		foreach ($rows as &$row) {
			$recordModel = Vtiger_Record_Model::getInstanceById($row['crmid'], \App\Module::getModuleName($row['tabid']));
			$eventHandler->setRecordModel($recordModel);
			$eventHandler->setModuleName($recordModel->getModuleName());
			$eventHandler->setParams($row['params']);
			$eventHandler->setUser($row['params']);
			if (!empty($row['handler_name'])) {
				$eventHandler->trigger($row['handler_name']);
			} elseif (!empty($row['class'])) {
				$handlerInstance = new $row['class']();
				$handlerInstance->process($eventHandler);
			}
			$db->createCommand()->delete('s_#__handler_updater', ['id' => $row['id']])->execute();
		}
	} catch (Exception $e) {
		App\Log::error($e->getMessage(), 'CRON');
	}
	sleep($interval);
} while (time() < $endTime);
