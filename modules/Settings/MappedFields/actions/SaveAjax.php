<?php

/**
 * SaveAjax Action Class for MappedFields Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
		$this->exposeMethod('import');
	}

	public function step1(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$params = $request->get('param');
		$recordId = $params['record'];
		$step = $params['step'];

		if ($recordId) {
			$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
		} else {
			$moduleInstance = Settings_MappedFields_Module_Model::getCleanInstance();
		}
		$stepFields = Settings_MappedFields_Module_Model::getFieldsByStep($step);
		foreach ($stepFields as $field) {
			$moduleInstance->getRecord()->set($field, $params[$field]);
			if ($field == 'conditions') {
				$moduleInstance->transformAdvanceFilterToWorkFlowFilter();
			}
		}
		if (!$recordId && $moduleInstance->importsAllowed() >= 1) {
			$message = 'LBL_TEMPATE_EXIST';
		} else {
			$moduleInstance->save();
		}

		$response = new Vtiger_Response();
		$response->setResult(['id' => $moduleInstance->getRecordId(), 'message' => vtranslate($message, $qualifiedModuleName)]);
		$response->emit();
	}

	public function step2(Vtiger_Request $request)
	{
		$params = $request->get('param');
		$recordId = $params['record'];

		$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
		$moduleInstance->getRecord()->set('params', $params['otherConditions']);
		$moduleInstance->setMapping($params['mapping']);
		$moduleInstance->save(true);

		$response = new Vtiger_Response();
		$response->setResult(['id' => $moduleInstance->getRecordId()]);
		$response->emit();
	}

	public function import(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$id = '';
		if (is_array($_FILES) && $_FILES['imported_xml']['name'] != '') {
			$xmlName = $_FILES['imported_xml']['name'];
			$uploadedXml = $_FILES['imported_xml']['tmp_name'];
			$xmlError = $_FILES['imported_xml']['error'];
			$extension = end(explode('.', $xmlName));
			$message = false;
			$moduleInstance = Settings_MappedFields_Module_Model::getCleanInstance();
			$mapping = [];
			$combine = ['tabid' => 'source', 'reltabid' => 'target'];
			if ($xmlError == UPLOAD_ERR_OK && $extension === 'xml') {
				$xml = simplexml_load_file($uploadedXml);
				$i = 0;
				$instances = [];
				foreach ($xml as $fieldsKey => $fieldsValue) {
					if (array_key_exists($fieldsKey, $combine)) {
						$value = (int) Vtiger_Functions::getModuleId((string) $fieldsValue);
						if (empty($value)) {
							break;
						}
						$instances[$combine[$fieldsKey]] = Vtiger_Module_Model::getInstance((string) $fieldsValue);
					} elseif ($fieldsKey == 'fields') {
						foreach ($fieldsValue as $fieldKey => $fieldValue) {
							foreach ($fieldValue as $columnKey => $columnValue) {
								settype($columnKey, 'string');
								settype($columnValue, 'string');
								if (in_array($columnKey, ['default', 'type'])) {
									$mapping[$i][$columnKey] = $columnValue;
									continue;
								}
								$fieldObject = Settings_MappedFields_Field_Model::getInstance($columnValue, $instances[$columnKey],$mapping[$i]['type']);
								if (!$fieldObject) {
									continue;
								}
								$mapping[$i][$columnKey] = $fieldObject->getId();
							}
							$i++;
						}
						continue;
					} else {
						$value = (string) $fieldsValue;
					}
					$moduleInstance->getRecord()->set($fieldsKey, $value);
				}
				$tabid = $moduleInstance->getRecord()->get('tabid');
				$reltabid = $moduleInstance->getRecord()->get('reltabid');
				if (empty($tabid) || empty($reltabid)) {
					$message = 'LBL_MODULE_NOT_EXIST';
				} elseif (!$moduleInstance->importsAllowed()) {
					
					$moduleInstance->setMapping($mapping);
					$moduleInstance->save(true);
					$message = 'LBL_IMPORT_OK';
					$id = $moduleInstance->getRecordId();
				} else {
					$message = 'LBL_NO_PERMISSION_TO_IMPORT';
				}
			} else {
				$message = 'LBL_UPLOAD_ERROR';
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(['id' => $id, 'message' => vtranslate($message, $qualifiedModuleName)]);
		$response->emit();
	}
}
