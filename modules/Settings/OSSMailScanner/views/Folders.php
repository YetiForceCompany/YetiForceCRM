<?php

/**
 * Mail scanner action creating mail.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_OSSMailScanner_Folders_View extends Settings_Vtiger_BasicModal_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!$request->has('record')) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Get tree data.
	 *
	 * @param string $moduleName
	 * @param array  $folders
	 * @param array  $selectedFolders
	 *
	 * @return array
	 */
	private function getTreeData(string $moduleName, array $folders, array $selectedFolders): array
	{
		$tree = $tempArray = [];
		foreach (OSSMailScanner_Record_Model::$mainFolders as $mainFolder) {
			$treeCategory = [
				'id' => $mainFolder,
				'type' => 'category',
				'parent' => '#',
				'text' => \App\Language::translate($mainFolder, $moduleName)
			];
			$tree[] = $treeCategory;
			$tempArray[$mainFolder][] = $treeCategory['id'];
			foreach ($folders as $folder) {
				$foldersSplited = explode('/', $folder);
				$parentPath = $mainFolder;
				foreach ($foldersSplited as $i => $folderName) {
					$treeRecordId = 0 === $i ? "{$mainFolder}/{$folderName}" : "{$mainFolder}/{$foldersSplited[$i - 1]}/{$folderName}";
					if (!\in_array($treeRecordId, $tempArray[$mainFolder])) {
						$treeRecord = [
							'id' => $treeRecordId,
							'type' => 'category',
							'parent' => $parentPath,
							'text' => \App\Language::translate($folderName, $moduleName),
							'state' => ['selected' => \in_array($folder, (array) ($selectedFolders[$mainFolder] ?? []))]
						];
						if (end($foldersSplited) === $folderName) {
							$treeRecord['db_id'] = $folder;
							$treeRecord['db_type'] = $mainFolder;
							$treeRecord['type'] = 'record';
						}
						$tempArray[$mainFolder][] = $treeRecord['id'];
						$tree[] = $treeRecord;
					}
					$parentPath = $treeRecordId;
				}
			}
		}
		return $tree;
	}

	public function getSize(App\Request $request)
	{
		return 'modal-lg';
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->getInteger('record');
		$mailDetail = OSSMail_Record_Model::getMailAccountDetail($record);
		$missingFolders = $selectedFolders = $folders = [];
		if (\App\Module::getModuleId('OSSMail')) {
			$mailRecordModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
			$folders = $mailRecordModel->getFolders($record);
			$mailScannerRecordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
			$mailScannerFolders = $mailScannerRecordModel->getFolders($record);
			foreach ($mailScannerFolders as $folder) {
				if (!isset($folders[$folder['folder']])) {
					$missingFolders[] = $folder['folder'];
				}
				$selectedFolders[$folder['type']][] = $folder['folder'];
			}
		}
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $record);
		$viewer->assign('TREE', $this->getTreeData($moduleName, $folders, $selectedFolders));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ADDRESS_EMAIL', $mailDetail['username']);
		$viewer->assign('MISSING_FOLDERS', $missingFolders);
		$viewer->view('Folders.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}
