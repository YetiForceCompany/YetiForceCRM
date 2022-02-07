<?php

/**
 * Settings TreesManager ReplaceTreeItem view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_ReplaceTreeItem_View extends \App\Controller\ModalSettings
{
	/**
	 * Qualified module name.
	 *
	 * @var string
	 */
	public $qualifiedModuleName = '';

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->qualifiedModuleName = $request->getModule(false);
		$this->modalIcon = 'fas fa-exchange-alt';
		$this->pageTitle = \App\Language::translate('LBL_SELECT_REPLACE_TREE_ITEM', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $this->qualifiedModuleName);
		$viewer->view('ReplaceTreeItem.tpl', $this->qualifiedModuleName);
	}
}
