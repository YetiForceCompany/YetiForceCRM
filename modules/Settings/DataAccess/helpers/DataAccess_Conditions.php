<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class DataAccess_Conditions
{

	public static $tab = 'vtiger_dataaccess';
	public static $tab_cnd = 'vtiger_dataaccess_cnd';

	public function checkConditions($ID, $form, $recordModel = false)
	{
		$condition = $this->getListConditionsById($ID);
		if (empty($form)) {
			return array('test' => true, 'ID' => $ID, 'condition' => $condition[$ID][0]);
		}
		$responeListRequired = array();
		$responeListOptional = array();
		$responeListRequiredStatus = true;
		$responeListOptionalStatus = false;
		if ($recordModel) {
			$fieldNames = array_keys($form);
			foreach ($condition as $lisConditions) {
				foreach ($lisConditions as $cndKey => $singleCnd) {
					if (!in_array($singleCnd['fieldname'], $fieldNames)) {
						$form = Vtiger_Record_Model::getInstanceById($recordModel->getId())->getData();
						break;
					}
				}
			}
		}
		foreach ($condition as $lisConditions) {
			foreach ($lisConditions as $cndKey => $singleCnd) {
				if ('1' == $singleCnd['cnd_required']) {
					if (NULL != $singleCnd['comparator']) {
						$responeListRequired[] = $this->checkSingleCondition($form, $singleCnd);
					}
				} else {
					if (NULL != $singleCnd['comparator']) {
						$responeListOptional[] = $this->checkSingleCondition($form, $singleCnd);
					}
				}
			}
		}
		for ($i = 0; $i < count($responeListRequired); $i++) {
			if (TRUE != $responeListRequired[$i]) {
				$responeListRequiredStatus = false;
			}
		}
		if (count($responeListOptional)) {
			for ($i = 0; $i < count($responeListOptional); $i++) {
				if (TRUE == $responeListOptional[$i]) {
					$responeListOptionalStatus = true;
				}
			}
		} else {
			$responeListOptionalStatus = TRUE;
		}
		if ($responeListRequiredStatus && $responeListOptionalStatus) {
			return array('test' => true, 'ID' => $ID, 'condition' => $condition[$ID][0]);
		} else {
			return array('test' => false, 'ID' => $ID, 'condition' => $condition[$ID][0]);
		}
	}

	private function getListConditions($module)
	{
		$db = PearDatabase::getInstance();
		$sql = "SELECT dataaccessid, fieldname, comparator, field_type, val, required "
			. " FROM " . self::$tab
			. " LEFT JOIN " . self::$tab_cnd . " ON " . self::$tab_cnd . ".dataaccessid = dataaccessid"
			. " WHERE module_name = ?";
		$result = $db->pquery($sql, array($module), TRUE);
		$output = array();
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$id = $db->query_result_raw($result, $i, 'dataaccessid');
			$output[$id][$i]['fieldname'] = $db->query_result_raw($result, $i, 'fieldname');
			$output[$id][$i]['comparator'] = $db->query_result_raw($result, $i, 'comparator');
			$output[$id][$i]['field_type'] = $db->query_result_raw($result, $i, 'field_type');
			$output[$id][$i]['val'] = $db->query_result_raw($result, $i, 'val');
			$output[$id][$i]['cnd_required'] = $db->query_result_raw($result, $i, 'required');
		}
		return $output;
	}

	private function getListConditionsById($ID)
	{
		$db = PearDatabase::getInstance();

		$sql = sprintf('SELECT %s.dataaccessid, fieldname, comparator, field_type, val, required 
			 FROM  %s
			 LEFT JOIN %s ON %s.dataaccessid = %s.dataaccessid
			 WHERE %s.dataaccessid = ?', self::$tab, self::$tab, self::$tab_cnd, self::$tab_cnd, self::$tab, self::$tab);
		$result = $db->pquery($sql, [$ID], TRUE);
		$output = array();
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$id = $db->query_result_raw($result, $i, 'dataaccessid');
			$output[$id][$i]['fieldname'] = $db->query_result_raw($result, $i, 'fieldname');
			$output[$id][$i]['comparator'] = $db->query_result_raw($result, $i, 'comparator');
			$output[$id][$i]['field_type'] = $db->query_result_raw($result, $i, 'field_type');
			$output[$id][$i]['val'] = $db->query_result_raw($result, $i, 'val');
			$output[$id][$i]['cnd_required'] = $db->query_result_raw($result, $i, 'required');
		}
		return $output;
	}

	private function checkSingleCondition($form, $cndArray)
	{
		vimport('~~modules/Settings/DataAccess/helpers/DataAccess_ConditionsTest.php');
		$methodName = $this->createFunctionName($cndArray['comparator']);
		$class = new ReflectionClass('DataAccess_ConditionsTest');
		$methodList = $class->getMethods(ReflectionMethod::IS_STATIC);
		$exist = false;
		for ($i = 0; $i < count($methodList); $i++) {
			if ($methodList[$i]->name == $methodName) {
				$exist = true;
			}
		}

		if ($exist) {
			return DataAccess_ConditionsTest::$methodName($form, $cndArray);
		}
	}

	private function createFunctionName($condition)
	{
		$tabConditionName = explode(' ', $condition);
		for ($i = 0; $i < count($tabConditionName); $i++) {
			if (0 != $i) {
				$tabConditionName[$i] = ucfirst($tabConditionName[$i]);
			}
		}
		return implode('', $tabConditionName);
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////

	/* function getListValidDoc($moduleName, $record) {
	  $listDocAndConditions = $this->getListConditions($moduleName);
	  $output = array();

	  foreach ($listDocAndConditions as $key => $lisConditions) {
	  $responeListRequired = array();
	  $responeListOptional = array();

	  foreach ($lisConditions as $cndKey => $singleCnd) {

	  if ('1' == $singleCnd['cnd_required']) {
	  if (NULL != $singleCnd['comparator']) {
	  $responeListRequired[] = $this->checkSingleCondition($record, $singleCnd);
	  }
	  } else {
	  if (NULL != $singleCnd['comparator']) {
	  $responeListOptional[] = $this->checkSingleCondition($record, $singleCnd);
	  }
	  }
	  }

	  $responeListRequiredStatus = true;

	  for ($i = 0; $i < count($responeListRequired); $i++) {
	  if (TRUE != $responeListRequired[$i]) {
	  $responeListRequiredStatus = false;
	  }
	  }

	  $responeListOptionalStatus = false;

	  if (count($responeListOptional)) {
	  for ($i = 0; $i < count($responeListOptional); $i++) {
	  if (TRUE == $responeListOptional[$i]) {
	  $responeListOptionalStatus = true;
	  }
	  }
	  } else {
	  $responeListOptionalStatus = TRUE;
	  }

	  if ($responeListRequiredStatus && $responeListOptionalStatus) {
	  $singleDocInfo = array_shift(array_values($listDocAndConditions[$key]));

	  $folderModel = Documents_Folder_Model::getInstanceById($singleDocInfo['doc_folder']);
	  $singleDocInfo['folder'] = $folderModel->getName();
	  $output[] = $singleDocInfo;
	  }
	  }

	  return $output;
	  } */

	public function docIsAttachet($record, $folder, $docName)
	{
		$db = PearDatabase::getInstance();

		$getListDocumentRelSql = "SELECT * FROM vtiger_senotesrel WHERE crmid = ?";
		$getListDocumentRelResult = $db->pquery($getListDocumentRelSql, array($record), TRUE);

		for ($i = 0; $i < $db->num_rows($getListDocumentRelResult); $i++) {
			$ID = $db->query_result($getListDocumentRelResult, $i, 'notesid');

			if (isRecordExists($ID)) {
				$documentModel = Vtiger_Record_Model::getInstanceById($ID);

				if ($docName == $documentModel->get('notes_title') && $folder == $documentModel->get('folderid')) {
					return TRUE;
				}
			}
		}

		return false;
	}

	public function docStatus($record, $folder, $docName)
	{
		$db = PearDatabase::getInstance();

		$getListDocumentRelSql = "SELECT * FROM vtiger_senotesrel WHERE crmid = ?";
		$getListDocumentRelResult = $db->pquery($getListDocumentRelSql, array($record), TRUE);

		for ($i = 0; $i < $db->num_rows($getListDocumentRelResult); $i++) {
			$ID = $db->query_result($getListDocumentRelResult, $i, 'notesid');

			if (isRecordExists($ID)) {
				$documentModel = Vtiger_Record_Model::getInstanceById($ID);

				if ($docName == $documentModel->get('notes_title') && $folder == $documentModel->get('folderid')) {
					return $documentModel->get('ossdc_status');
				}
			}
		}

		return false;
	}
}
