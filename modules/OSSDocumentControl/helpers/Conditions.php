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

class Conditions
{
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

	public function checkConditionsForDoc($docId, $form)
	{

		$condition = $this->getListConditionsById($docId);

		$responeListRequired = array();
		$responeListOptional = array();

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

		$responeListRequiredStatus = true;

		for ($i = 0; $i < count($responeListRequired); $i++) {
			if (TRUE != $responeListRequired[$i]) {
				$responeListRequiredStatus = false;
			}
		}

		//var_dump($responeListRequiredStatus);

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
			return array('test' => true, 'docId' => $docId, 'doc_request' => $condition[$docId][0]['doc_request'], 'folderid' => $condition[$docId][0]['doc_folder'], 'name' => $condition[$docId][0]['doc_name']);
		} else {
			return array('test' => false, 'docId' => $docId, 'doc_request' => $condition[$docId][0]['doc_request'], 'folderid' => $condition[$docId][0]['doc_folder'], 'name' => $condition[$docId][0]['doc_name']);
		}
	}

	private function checkSingleCondition($form, $cndArray)
	{
		require_once 'modules/OSSDocumentControl/helpers/ConditionsTest.php';

		$methodName = $this->createFunctionName($cndArray['comparator']);

		$class = new ReflectionClass('ConditionsTest');
		$methodList = $class->getMethods(ReflectionMethod::IS_STATIC);

		$exist = false;

		for ($i = 0; $i < count($methodList); $i++) {
			if ($methodList[$i]->name == $methodName) {
				$exist = true;
			}
		}

		if ($exist) {
			return ConditionsTest::$methodName($form, $cndArray);
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

	private function getListConditions($module)
	{
		$db = PearDatabase::getInstance();

		$sql = "SELECT "
			. "vtiger_ossdocumentcontrol.ossdocumentcontrolid as id, "
			. "vtiger_ossdocumentcontrol.doc_name as doc_name, "
			. "vtiger_ossdocumentcontrol.doc_request as doc_request, "
			. "vtiger_ossdocumentcontrol.doc_folder as doc_folder, "
			. "vtiger_ossdocumentcontrol_cnd.fieldname as fieldname, "
			. "vtiger_ossdocumentcontrol_cnd.comparator as comparator, "
			. "vtiger_ossdocumentcontrol_cnd.field_type as field_type, "
			. "vtiger_ossdocumentcontrol_cnd.val as val, "
			. "vtiger_ossdocumentcontrol_cnd.required as required "
			. " FROM vtiger_ossdocumentcontrol "
			. "LEFT JOIN vtiger_ossdocumentcontrol_cnd ON vtiger_ossdocumentcontrol_cnd.ossdocumentcontrolid = vtiger_ossdocumentcontrol.ossdocumentcontrolid "
			. "WHERE module_name = ?";

		$result = $db->pquery($sql, array($module), TRUE);


		$output = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$id = $db->query_result($result, $i, 'id');
			$output[$id][$i]['doc_name'] = $db->query_result($result, $i, 'doc_name');
			$output[$id][$i]['doc_request'] = $db->query_result($result, $i, 'doc_request');
			$output[$id][$i]['doc_folder'] = $db->query_result($result, $i, 'doc_folder');
			$output[$id][$i]['fieldname'] = $db->query_result($result, $i, 'fieldname');
			$output[$id][$i]['comparator'] = $db->query_result($result, $i, 'comparator');
			$output[$id][$i]['field_type'] = $db->query_result($result, $i, 'field_type');
			$output[$id][$i]['val'] = $db->query_result($result, $i, 'val');
			$output[$id][$i]['cnd_required'] = $db->query_result($result, $i, 'required');
		}

		return $output;
	}

	private function getListConditionsById($docId)
	{
		$db = PearDatabase::getInstance();

		$sql = "SELECT "
			. "vtiger_ossdocumentcontrol.ossdocumentcontrolid as id, "
			. "vtiger_ossdocumentcontrol.doc_name as doc_name, "
			. "vtiger_ossdocumentcontrol.doc_request as doc_request, "
			. "vtiger_ossdocumentcontrol.doc_folder as doc_folder, "
			. "vtiger_ossdocumentcontrol_cnd.fieldname as fieldname, "
			. "vtiger_ossdocumentcontrol_cnd.comparator as comparator, "
			. "vtiger_ossdocumentcontrol_cnd.field_type as field_type, "
			. "vtiger_ossdocumentcontrol_cnd.val as val, "
			. "vtiger_ossdocumentcontrol_cnd.required as required "
			. " FROM vtiger_ossdocumentcontrol "
			. "LEFT JOIN vtiger_ossdocumentcontrol_cnd ON vtiger_ossdocumentcontrol_cnd.ossdocumentcontrolid = vtiger_ossdocumentcontrol.ossdocumentcontrolid "
			. "WHERE vtiger_ossdocumentcontrol.ossdocumentcontrolid = ?";

		$result = $db->pquery($sql, array($docId), TRUE);

		$output = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$id = $db->query_result($result, $i, 'id');
			$output[$id][$i]['doc_name'] = $db->query_result($result, $i, 'doc_name');
			$output[$id][$i]['doc_request'] = $db->query_result($result, $i, 'doc_request');
			$output[$id][$i]['doc_folder'] = $db->query_result($result, $i, 'doc_folder');
			$output[$id][$i]['fieldname'] = $db->query_result($result, $i, 'fieldname');
			$output[$id][$i]['comparator'] = $db->query_result($result, $i, 'comparator');
			$output[$id][$i]['field_type'] = $db->query_result($result, $i, 'field_type');
			$output[$id][$i]['val'] = $db->query_result($result, $i, 'val');
			$output[$id][$i]['cnd_required'] = $db->query_result($result, $i, 'required');
		}

		return $output;
	}

	public function getListDocForModule($moduleName, $forCheck = false)
	{
		$db = PearDatabase::getInstance();

		// $this->createFunctionName('is not');

		$sql = "SELECT "
			. "vtiger_ossdocumentcontrol.ossdocumentcontrolid as id, "
			. "vtiger_ossdocumentcontrol.doc_name as doc_name, "
			. "vtiger_ossdocumentcontrol.doc_request as doc_request, "
			. "vtiger_ossdocumentcontrol.doc_folder as doc_folder "
			. "FROM vtiger_ossdocumentcontrol "
			. "WHERE module_name = ? ";

		if ($forCheck) {
			$sql .= " && vtiger_ossdocumentcontrol.doc_request = '1'";
		}

		$sql .= " ORDER BY vtiger_ossdocumentcontrol.doc_order ASC";

		$result = $db->pquery($sql, array($moduleName), TRUE);


		$output = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$output[$i]['doc_id'] = $db->query_result($result, $i, 'id');
			$output[$i]['doc_name'] = $db->query_result($result, $i, 'doc_name');
			$output[$i]['doc_request'] = $db->query_result($result, $i, 'doc_request');

			$folderId = $db->query_result($result, $i, 'doc_folder');
			$folderModel = Documents_Folder_Model::getInstanceById($folderId);

			$output[$i]['doc_folder'] = $folderId;
			$output[$i]['folder_name'] = $folderModel->getName();
		}

		return $output;
	}

	public function docIsAttachet($record, $folder, $docName)
	{
		$db = PearDatabase::getInstance();

		$getListDocumentRelSql = "SELECT * FROM vtiger_senotesrel WHERE crmid = ?";
		$getListDocumentRelResult = $db->pquery($getListDocumentRelSql, array($record), TRUE);

		for ($i = 0; $i < $db->num_rows($getListDocumentRelResult); $i++) {
			$docId = $db->query_result($getListDocumentRelResult, $i, 'notesid');

			if (isRecordExists($docId)) {
				$documentModel = Vtiger_Record_Model::getInstanceById($docId);

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
			$docId = $db->query_result($getListDocumentRelResult, $i, 'notesid');

			if (isRecordExists($docId)) {
				$documentModel = Vtiger_Record_Model::getInstanceById($docId);

				if ($docName == $documentModel->get('notes_title') && $folder == $documentModel->get('folderid')) {
					return $documentModel->get('ossdc_status');
				}
			}
		}

		return false;
	}
}
