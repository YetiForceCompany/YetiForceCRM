<?php

/**
 * Settings TreesManager ReplaceTreeItem view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_ReplaceTreeItem_View extends \App\Controller\ModalSettings
{
	/**
	 * Qualified module name.
	 *
	 * @var string
	 */
	public $qualifiedModuleName = '';

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$this->qualifiedModuleName = $request->getModule(false);
		$this->pageTitle = '<span class="fas fa-exchange-alt mr-2"></span>' . \App\Language::translate('LBL_SELECT_REPLACE_TREE_ITEM', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $this->qualifiedModuleName);
		$viewer->view('ReplaceTreeItem.tpl', $this->qualifiedModuleName);
	}
}
