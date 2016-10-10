<?php

/**
 * @package YetiForce.actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_BackUp_Pagination_Action extends Settings_Vtiger_Basic_Action
{

	public function process(Vtiger_Request $request)
	{
		$adb = PearDatabase::getInstance();
		$limit = 10;

		$allBackups = Settings_BackUp_Module_Model::getBackupCount();

		if ($request->get('page') != '') {
			$page = $request->get('page');
			$offset = ($page - 1 ) * $limit;
			if ($request->get('page') == 1) {
				$prevPage = 0;
			} else {
				$prevPage = 1;
			}
		} else {
			$page = 1;
			$offset = 0;
			$prevPage = 0;
		}

		$nextPage = 1;
		$allPages = ceil($allBackups / $limit);
		if (($allPages == $page) || ($allBackups <= $limit))
			$nextPage = 0;

		$backups = Settings_BackUp_Module_Model::getBackupList($offset, $limit);
		$result = array(
			'prevPage' => $prevPage,
			'nextPage' => $nextPage,
			'offset' => $offset,
			'allPages' => $allPages,
			'page' => $page,
			'backups' => $backups
		);
		if ($request->get('ajaxCall') === '') {
			$json = json_encode($result);
			return $json;
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
