<?php

/**
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordAllocation_Module_Model extends Settings_Vtiger_Module_Model
{

	public static function saveRecordAllocation($data)
	{
		$content = '<?php' . PHP_EOL . '$recordAllocation = [';
		$map = [];
		if (!empty($data) && count($data)) {
			foreach ($data as $moduleId => $row) {
				$content .= "'" . $moduleId . "'=>[";
				foreach ($row as $type => $ids) {
					$content .= "'" . $type . "'=>['" . implode("','", $ids) . "'],";
				}
				$content .= '],';
			}
		}
		$content .= '];';
		$file = 'user_privileges/module_record_allocation.php';
		file_put_contents($file, $content);
	}

	public static function getRecordAllocation()
	{
		require('user_privileges/module_record_allocation.php');
		return $recordAllocation;
	}
}
