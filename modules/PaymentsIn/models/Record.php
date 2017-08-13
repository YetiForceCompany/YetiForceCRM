<?php

/**
 * PaymentsIn record model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class PaymentsIn_Record_Model extends Vtiger_Record_Model
{

	public function getSummary($type, $bank, $file)
	{
		$adres = vglobal('cache_dir');
		if ($bank == 'Default') {
			Vtiger_Loader::includeOnce('~~modules/PaymentsIn/helpers/' . $type . '.php');
			$records = new $type($adres . $file);
			return $records;
		}
		Vtiger_Loader::includeOnce('~~modules/PaymentsIn/helpers/subclass/' . $type . '_' . $bank . '.php');
		$class = $type . '_' . $bank;
		$records = new $class($adres . $file);

		return $records;
	}
}
