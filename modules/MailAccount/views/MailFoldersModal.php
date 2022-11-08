<?php

/**
 * Mail folders modal view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Mail folders modal view class.
 */
class MailAccount_MailFoldersModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtn = 'LBL_SELECT_OPTION';
	/**
	 * Field model.
	 *
	 * @var Vtiger_Field_Model
	 */
	public $fieldModel;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$recordModel = \Vtiger_Record_Model::getInstanceById($request->getInteger('template'));
		if (!$recordModel || !$recordModel->isEditable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$this->fieldModel = $recordModel->getField($request->getByType('fieldName', \App\Purifier::ALNUM));
		if (!$this->fieldModel || !$this->fieldModel->isViewable() || 'mailFolders' !== $this->fieldModel->getFieldDataType()) {
			throw new \App\Exceptions\NoPermitted('LBL_NO_PERMISSIONS_TO_FIELD');
		}
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELD_INSTANCE', $this->fieldModel);
		$this->pageTitle = $this->fieldModel->getFieldLabel();
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	protected function preProcessTplName(App\Request $request)
	{
		return 'Modals/TreeHeader.tpl';
	}

	/**
	 * Tree in popup.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('template');

		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
		$mailAccount = \App\Mail\Account::getInstanceById($recordId);
		$missingFolders = $selectedFolders = $folders = [];
		$error = '';
		try {
			$imap = $mailAccount->openImap();
			$folders = $imap->getFolders();
		} catch (\Throwable $th) {
			\App\Log::error($th->getMessage());
			$error = 'ERR_IMAP_CONNECTION'; // There was a problem accessing your e-mail account. Verify the correctness of the e-mail account settings.
		}

		$foldersFlat = array_unique(\App\Utils::flatten($folders));
		$mailScannerFolders = $recordModel->get('folders') ? \App\Json::decode($recordModel->get('folders')) : [];
		foreach ($mailScannerFolders as $folder) {
			if (!\in_array($folder, $foldersFlat)) {
				$missingFolders[] = $folder;
			} else {
				$selectedFolders[] = $folder;
			}
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('TREE', \App\Json::encode($this->getTreeData($folders, $selectedFolders)));
		$viewer->assign('IS_MULTIPLE', true);
		$viewer->assign('MISSING_FOLDERS', $missingFolders);
		$viewer->assign('ERROR', $error);
		$viewer->view('Modals/MailFoldersModal.tpl', $moduleName);
	}

	/**
	 * Get tree item.
	 *
	 * @param array       $folder
	 * @param array       $selectedFolders
	 * @param array       $tree
	 * @param string|null $parent
	 *
	 * @return array
	 */
	public function getTreeItem(array $folder, array $selectedFolders, array &$tree, ?string $parent = null): array
	{
		$treeRecord = [
			'id' => $folder['fullName'],
			'type' => 'category',
			'parent' => $parent ?? '#',
			'text' => \App\Purifier::encodeHtml($folder['name']),
			'state' => ['selected' => \in_array($folder['fullName'], (array) $selectedFolders), 'opened' => true]
		];
		$treeRecord['db_id'] = $folder['fullName'];
		$treeRecord['type'] = 'record';

		$tree[] = $treeRecord;
		if (!empty($folder['children'])) {
			foreach ($folder['children'] as $subfolder) {
				$this->getTreeItem($subfolder, $selectedFolders, $tree, $folder['fullName']);
			}
		}

		return $treeRecord;
	}

	/**
	 * Get tree data.
	 *
	 * @param array $folders
	 * @param array $selectedFolders
	 *
	 * @return array
	 */
	private function getTreeData(array $folders, array $selectedFolders): array
	{
		$tree = [];
		foreach ($folders as $folder) {
			$this->getTreeItem($folder, $selectedFolders, $tree, null);
		}

		return $tree;
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		$jsFileNames = ['~libraries/jstree/dist/jstree.js'];
		$jsFileNames[] = '~layouts/resources/libraries/jstree.category.js';
		$jsFileNames[] = '~layouts/resources/libraries/jstree.checkbox.js';

		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/** {@inheritdoc} */
	public function getModalCss(App\Request $request)
	{
		return array_merge(parent::getModalCss($request), $this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
		]));
	}
}
