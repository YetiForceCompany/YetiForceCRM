<?php
/**
 * Conditions test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 *  Configurator test class.
 */
class Conditions extends \Tests\Base
{
	/**
	 * Testing constructor method.
	 *
	 * @codeCoverageIgnore
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testIfAllOperatorsExist()
	{
		foreach ((new \DirectoryIterator(ROOT_DIRECTORY . '/modules/Vtiger/uitypes/')) as $item) {
			if ($item->isFile() && 'php' === $item->getExtension()) {
				$fileName = $item->getBasename('.php');
				$className = \Vtiger_Loader::getComponentClassName('UIType', $fileName, 'Vtiger', false);
				$this->assertTrue(class_exists($className), 'Class not found: ' . $className);

				$instance = new $className();

				$classNameQueryFields = '\App\Conditions\QueryFields\\' . $fileName . 'Field';
				$this->assertTrue(class_exists($classNameQueryFields), 'Class not found: ' . $classNameQueryFields);

				$methodsQueryFields = class_exists($classNameQueryFields) ? get_class_methods($classNameQueryFields) : [];
				foreach ($instance->getQueryOperators() as $operator) {
					$fn = 'operator' . ucfirst($operator);
					if ($methodsQueryFields && !\in_array($fn, $methodsQueryFields) && isset(\App\Condition::DATE_OPERATORS[$operator])) {
						$fn = 'getStdOperator';
					}
					if ($methodsQueryFields) {
						$this->assertTrue(\in_array($fn, $methodsQueryFields), "No query operator $operator (function $fn) in class $classNameQueryFields");
					}
				}

				$classNameRecordFields = '\App\Conditions\RecordFields\\' . $fileName . 'Field';
				$this->assertTrue(class_exists($classNameRecordFields), 'Class not found: ' . $classNameRecordFields);

				$methodsRecordFields = class_exists($classNameRecordFields) ? get_class_methods($classNameRecordFields) : [];
				foreach ($instance->getRecordOperators() as $operator) {
					$fn = 'operator' . ucfirst($operator);
					if ($methodsRecordFields && !\in_array($fn, $methodsRecordFields) && isset(\App\Condition::DATE_OPERATORS[$operator])) {
						$fn = 'getStdOperator';
					}
					if ($methodsRecordFields) {
						$this->assertTrue(\in_array($fn, $methodsRecordFields), "No record operator $operator (function $fn) in class $classNameRecordFields");
					}
				}
			}
		}
	}

	/**
	 * Testing check conditions.
	 */
	public function testCheckConditions()
	{
		$recordModel = \Tests\Base\C_RecordActions::createSQuotesRecord();
		$checkConditions = \App\Condition::checkConditions([
			'condition' => 'AND',
			'rules' => [
				[
					'fieldname' => 'createdtime:SQuotes',
					'operator' => 'bw',
					'value' => date('Y-m-d H:i:s', strtotime('last day')) . ',' . date('Y-m-d H:i:s', strtotime('next day')),
				],
				[
					'fieldname' => 'subject:SQuotes',
					'operator' => 'e',
					'value' => 'System CRM YetiForce',
				],
			],
		], $recordModel);
		$this->assertTrue($checkConditions);

		$checkConditions = \App\Condition::checkConditions([
			'condition' => 'AND',
			'rules' => [
				[
					'fieldname' => 'createdtime:SQuotes',
					'operator' => 'bw',
					'value' => date('Y-m-d H:i:s', strtotime('next day')) . ',' . date('Y-m-d H:i:s', strtotime('next year')),
				],
				[
					'fieldname' => 'subject:SQuotes',
					'operator' => 'e',
					'value' => 'System CRM YetiForce',
				],
			],
		], $recordModel);
		$this->assertFalse($checkConditions);
	}
}
