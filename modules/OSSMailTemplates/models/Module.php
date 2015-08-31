<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSMailTemplates_Module_Model extends Vtiger_Module_Model
{

	function getListFiledOfModule($moduleName, $relID = false)
	{
		$db = PearDatabase::getInstance();
		$tabid = getTabid($moduleName);
		$sql = "select `fieldid`, `fieldlabel`, `uitype`, `block` from vtiger_field where tabid = ? AND presence <> ? AND typeofdata <> ? AND `block` NOT IN (?)";
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

	function getListFiledOfRelatedModule($moduleName)
	{
		$db = PearDatabase::getInstance();
		$tabid = getTabid($moduleName);
		$sourceModule = $moduleName;
		$sql = "select vtiger_field.fieldid, fieldlabel, uitype, vtiger_fieldmodulerel.relmodule from vtiger_field 
				left JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid where tabid = ? AND (uitype = '10' OR uitype = '59' OR uitype = '53' OR uitype = '51')";

		$resultModuleList = $db->pquery($sql, array($tabid), true);
		$moduleList = array();
		for ($i = 0; $i < $db->num_rows($resultModuleList); $i++) {
			$uitype = $db->query_result($resultModuleList, $i, 'uitype');
			$fieldid = $db->query_result($resultModuleList, $i, 'fieldid');
			$fieldlabel = $db->query_result($resultModuleList, $i, 'fieldlabel');
			if ($uitype == 10) {
				$moduleList[] = array(Vtiger_Functions::getModuleId($db->query_result($resultModuleList, $i, 'relmodule')), $fieldlabel, $fieldid);
			} elseif ($uitype == 51) {
				$moduleList[] = array(Vtiger_Functions::getModuleId('Accounts'), $fieldlabel, $fieldid);
			} elseif ($uitype == 59) {
				$moduleList[] = array(Vtiger_Functions::getModuleId('Products'), $fieldlabel, $fieldid);
			} elseif ($uitype == 53) {
				$moduleList[] = array(Vtiger_Functions::getModuleId('Users'), $fieldlabel, $fieldid);
			}
		}
		$output = array();
		for ($i = 0; $i < count($moduleList); $i++) {
			$moduleInfoSql = "SELECT * FROM vtiger_tab WHERE tabid = ?";
			$moduleInfoResult = $db->pquery($moduleInfoSql, array($moduleList[$i][0]), true);
			$moduleName = $db->query_result($moduleInfoResult, 0, 'name');
			$moduleTrLabal = vtranslate($moduleList[$i][1], $sourceModule);
			$output[$moduleTrLabal] = array();
			$output[$moduleTrLabal] = $this->getListFiledOfModule($moduleName, $moduleList[$i][2]);
		}
		return $output;
	}

	function getListSpecialFunction($path, $module)
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

	function getTemplates()
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_ossmailtemplates AS mail INNER JOIN vtiger_crmentity AS crm ON crm.crmid = mail.ossmailtemplatesid WHERE `deleted` = 0 AND ossmailtemplates_type = ?';
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
