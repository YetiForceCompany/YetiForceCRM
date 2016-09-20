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

class OSSDocumentControl_Detail_View extends Vtiger_Detail_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getListDocuments');
	}

	public function getListDocuments(Vtiger_Request $request)
	{
		require_once 'modules/OSSDocumentControl/helpers/Conditions.php';

		$relatedModuleName = $request->get('rel_module');
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$conditions = new Conditions();
		$documentList = $conditions->getListDocForModule($relatedModuleName);

		for ($i = 0; $i < count($documentList); $i++) {
			if (mb_strlen($documentList[$i]['doc_name']) > 20) {
				$documentList[$i]['doc_short_name'] = substr($documentList[$i]['doc_name'], 0, 15) . '...';
			} else {
				$documentList[$i]['doc_short_name'] = $documentList[$i]['doc_name'];
			}
		}

		for ($i = 0; $i < count($documentList); $i++) {
			$documentList[$i]['is_attach'] = $conditions->docIsAttachet($record, $documentList[$i]['doc_folder'], $documentList[$i]['doc_name']);
			$documentList[$i]['status'] = $conditions->docStatus($record, $documentList[$i]['doc_folder'], $documentList[$i]['doc_name']);
		}


		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('DOC_LIST', $documentList);

		return $viewer->view('GetListDocuments.tpl', $moduleName, 'true');
	}
}
