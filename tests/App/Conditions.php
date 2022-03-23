<?php
/**
 * Conditions test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
				$this->assertTrue(class_exists($className));

				$instance = new $className();
				$classNameRecordFields = '\App\Conditions\RecordFields\\' . $fileName . 'Field';
				$classNameQueryFields = '\App\Conditions\QueryFields\\' . $fileName . 'Field';
				$this->assertTrue(class_exists($classNameRecordFields));
				$this->assertTrue(class_exists($classNameQueryFields));

				$methodsRecordFields = class_exists($classNameRecordFields) ? get_class_methods($classNameRecordFields) : [];
				$methodsQueryFields = class_exists($classNameQueryFields) ? get_class_methods($classNameQueryFields) : [];
				foreach ($instance->getQueryOperators() as $operator) {
					$fn = 'operator' . ucfirst($operator);
					if ($methodsQueryFields && !\in_array($fn, $methodsQueryFields) && isset(\App\Condition::DATE_OPERATORS[$operator])) {
						$fn = 'getStdOperator';
					}
					if ($methodsQueryFields && !\in_array($fn, $methodsQueryFields)) {
						$this->markTestSkipped("[QueryFields] No operator $operator (function $fn) in class $classNameQueryFields");
					}
					$fn = 'operator' . ucfirst($operator);
					if ($methodsRecordFields && !\in_array($fn, $methodsRecordFields) && isset(\App\Condition::DATE_OPERATORS[$operator])) {
						$fn = 'getStdOperator';
					}
					if ($methodsRecordFields && !\in_array($fn, $methodsRecordFields)) {
						$this->markTestSkipped("[RecordFields] No operator $operator (function $fn) in class $classNameRecordFields");
					}
				}
			}
		}
	}
}
