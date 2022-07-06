<?php
/**
 * Integration BIR1 - Baza Internetowa REGON 1 cron file.
 *
 * @see https://api.stat.gov.pl/Home/RegonApi
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Integration GUS BIR1 - Baza Internetowa REGON 1 cron class.
 */
class Vtiger_IntegrationPLGusRegon_Cron extends \App\CronHandler
{
	/** @var array Default configuration for data download */
	private $config = [
		'moduleName' => 'Leads',
		'fieldName' => 'registration_number_2',
		'defaultValues' => [],
	];

	/** {@inheritdoc} */
	public function process()
	{
		$this->loadConfig();
		$recordCollector = \App\RecordCollector::getInstance('App\RecordCollectors\Gus', $this->config['moduleName']);
		if (!$recordCollector->isActive()) {
			\App\Log::warning('GUS record collector is not active', __CLASS__);
			return;
		}
		if (!Vtiger_Module_Model::getInstance($this->config['moduleName'])->getFieldByName($this->config['fieldName'])->isActiveField()) {
			\App\Log::warning('The cron job was skipped because the field is not active', __CLASS__);
			return;
		}
		$i = 0;
		$client = \App\RecordCollectors\Helper\GusClient::getInstance();
		$response = $client->getData('DanePobierzRaportZbiorczy', ['pDataRaportu' => date('Y-m-d', strtotime('-1 day')), 'pNazwaRaportu' => 'BIR11NowePodmiotyPrawneOrazDzialalnosciOsFizycznych']);
		foreach ($response as $value) {
			$value = $value['regon'];
			$recordId = (new \App\QueryGenerator($this->config['moduleName']))->setFields(['id'])->addCondition($this->config['fieldName'], $value, 'e')->createQuery()->scalar();
			if (empty($recordId)) {
				$recordModel = \Vtiger_Record_Model::getCleanInstance($this->config['moduleName']);
				if (!empty($this->config['defaultValues'])) {
					$recordModel->setData(array_merge($recordModel->getData(), $this->config['defaultValues']));
				}
				$recordCollector->setRequest(new \App\Request(['module' => $this->config['moduleName'], 'taxNumber' => $value], false));
				$response = $recordCollector->search();
				arsort($response['dataCounter']);
				$key = array_key_first($response['dataCounter']);
				foreach ($response['fields'] as $fieldName => $values) {
					try {
						$recordModel->getField($fieldName)->getUITypeModel()->validate($values['data'][$key]['raw']);
						$recordModel->set($fieldName, $values['data'][$key]['raw']);
					} catch (\Throwable $th) {
						\App\Log::error("[taxNumber => $value]Error during data validation: \n{$th->__toString()}\n", __CLASS__);
					}
				}
				$recordModel->save();
				++$i;
			}
		}
		$client->endSession();
		$this->logs = $i;
	}

	/**
	 * Load cron configuration.
	 *
	 * @return void
	 */
	public function loadConfig(): void
	{
		$class = '\\Config\\Components\\RecordCollectors\\Gus';
		if (\class_exists($class)) {
			$this->config = array_merge($this->config, (new \ReflectionClass($class))->getStaticProperties());
		}
	}
}
