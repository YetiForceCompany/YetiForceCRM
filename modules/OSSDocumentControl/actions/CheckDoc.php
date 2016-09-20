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

class OSSDocumentControl_CheckDoc_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		return;
	}

	public function process(Vtiger_Request $request)
	{

		require_once 'modules/OSSDocumentControl/helpers/Conditions.php';
		$moduleName = $request->getModule();
		$relModuleName = $request->get('rel_module');
		$record = $request->get('record');
		$form = $request->get('form');

		$conditions = new Conditions();
		$listDoc = $conditions->getListDocForModule($relModuleName, true);

		$notAttachDoc = array();

		$conditionCheckTab = array();

		for ($i = 0; $i < count($listDoc); $i++) {
			$conditionCheckTab[] = $conditions->checkConditionsForDoc($listDoc[$i]['doc_id'], $form);
		}

		$passCondition = true;

		for ($i = 0; $i < count($conditionCheckTab); $i++) {
			if (false == $conditionCheckTab[$i]['test']) {
				$passCondition = false;
			}
		}

		$passAttach = true;

		for ($i = 0; $i < count($conditionCheckTab); $i++) {
			if ($conditionCheckTab[$i]['test'] && '1' == $conditionCheckTab[$i]['doc_request']) {

				$isAttach = $conditions->docIsAttachet($record, $conditionCheckTab[$i]['folderid'], $conditionCheckTab[$i]['name']);

				if (!$isAttach) {
					$passAttach = false;
					$notAttachDoc[] = $conditionCheckTab[$i]['name'];
				}
			}
		}

		//var_dump($notAttachDoc, $pasCondition);

		$output = array(
			'condition_allow' => $passAttach && $passCondition,
			'not_attach_doc_list' => $notAttachDoc,
			'tr' => array(
				vtranslate('NOT_ALLOW_TO_SAVE', $moduleName),
				vtranslate('LIST_REQ_DOC', $moduleName),
			),
			'passAttach' => $passAttach,
			'passCondition' => $passCondition,
		);

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
