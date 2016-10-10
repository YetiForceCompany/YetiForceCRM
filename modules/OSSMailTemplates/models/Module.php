<?php

/**
 *
 * @package YetiForce.models
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailTemplates_Module_Model extends Vtiger_Module_Model
{

	public function getListFiledOfModule($moduleName, $relID = false)
	{
		$db = PearDatabase::getInstance();
		$tabid = \includes\Modules::getModuleId($moduleName);
		$sql = "select `fieldid`, `fieldlabel`, `uitype`, `block` from vtiger_field where tabid = ? && presence <> ? && typeofdata <> ? && `block` NOT IN (?)";
		$result = $db->pquery($sql, array($tabid, 1, 'P~M', 0));
		$output = array();
		$block = ['blockId' => '', 'blockLabel' => ''];
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$blockid = $db->query_result($result, $i, 'block');
			if ($block['blockId'] != $blockid) {
				$block['blockId'] = $blockid;
				$block['blockLabel'] = getBlockName($blockid);
			}
			if ($relID) {
				$id = $db->query_result($result, $i, 'fieldid') . '||' . $relID;
			} else {
				$id = $db->query_result($result, $i, 'fieldid');
			}
			$output[$blockid][$i]['label'] = vtranslate($db->query_result($result, $i, 'fieldlabel'), $moduleName);
			$output[$blockid][$i]['id'] = $id;
			$output[$blockid][$i]['uitype'] = $db->query_result($result, $i, 'uitype');
			$output[$blockid]['blockLabel'] = vtranslate($block['blockLabel'], $moduleName);
		}
		return $output;
	}

	public function getListFiledOfRelatedModule($moduleName)
	{
		$db = PearDatabase::getInstance();
		$tabid = \includes\Modules::getModuleId($moduleName);
		$sourceModule = $moduleName;
		$params = $referenceUitype = [10, 59, 53, 51, 66, 67, 68];
		$params[] = $tabid;
		$sql = sprintf('SELECT vtiger_field.fieldid, fieldlabel, uitype, vtiger_fieldmodulerel.relmodule FROM vtiger_field 
				LEFT JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid 
				WHERE uitype IN (%s) && tabid = ? ', $db->generateQuestionMarks($referenceUitype));

		$resultModuleList = $db->pquery($sql, $params);
		$moduleList = [];
		$modulesNameList = [];
		while ($row = $db->getRow($resultModuleList)) {
			switch ($row['uitype']) {
				case 10:
					$modulesName = [$row['relmodule']];
					break;
				case 51:
					$modulesName = ['Accounts'];
					break;
				case 59:
					$modulesName = ['Products'];
					break;
				case 53:
					$modulesName = ['Users'];
					break;
				default:
					$fieldInstance = Vtiger_Field_Model::getInstanceFromFieldId($row['fieldid']);
					$modulesName = $fieldInstance->getUITypeModel()->getReferenceList();
					break;
			}
			foreach ($modulesName as $moduleName) {
				$moduleTrLabal = vtranslate($moduleName, $moduleName);
				if (!in_array($moduleName, $modulesNameList)) {
					$modulesNameList[] = $moduleName;
					$moduleList[][$moduleTrLabal] = $this->getListFiledOfModule($moduleName, $row['fieldid']);
				}
			}
		}
		return $moduleList;
	}

	public function getListSpecialFunction($path, $module)
	{
		$specialFunctionList = array();
		$numFile = 0;

		$dir = new DirectoryIterator($path);

		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot()) {
				$tmp = explode('.', $fileinfo->getFilename());
				$fullPath = $path . DIRECTORY_SEPARATOR . $tmp[0] . '.php';
				if (file_exists($fullPath)) {
					require_once $fullPath;

					$funObj = new $tmp[0];

					if (in_array($module, $funObj->getListAllowedModule()) || in_array('all', $funObj->getListAllowedModule())) {
						$specialFunctionList[$numFile]['filename'] = $tmp[0];
						$specialFunctionList[$numFile]['label'] = vtranslate($tmp[0], $this->getName());
						$numFile++;
					}
				}
			}
		}
		return $specialFunctionList;
	}

	public function getTemplates()
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_ossmailtemplates AS mail INNER JOIN vtiger_crmentity AS crm ON crm.crmid = mail.ossmailtemplatesid WHERE `deleted` = 0 && ossmailtemplates_type = ?';
		$result = $db->pquery($sql, ['PLL_MAIL']);
		$output = [];
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$moduleName = $db->query_result_raw($result, $i, 'oss_module_list');
			$id = $db->query_result_raw($result, $i, 'ossmailtemplatesid');
			$name = $db->query_result_raw($result, $i, 'name');
			$type = $db->query_result_raw($result, $i, 'ossmailtemplates_type');
			$output[$i]['id'] = $id;
			$output[$i]['name'] = $name;
			$output[$i]['module'] = $moduleName;
			$output[$i]['moduleName'] = vtranslate($moduleName, $moduleName);
			$output[$i]['type'] = $type;
		}
		return $output;
	}
}
