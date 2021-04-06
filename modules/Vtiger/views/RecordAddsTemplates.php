<?php

/**
 * Vtiger RecordAddsTemplate edit view file.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * RecordAddsTemplate edit view class.
 */
class Vtiger_RecordAddsTemplates_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $showFooter = true;

	/** {@inheritdoc} */
	public $modalSize = 'c-modal-xxl';

	/** Record adds instance @var \App\RecordAddsTemplates\ */
	private $recordAddsInstance;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Config::main('isActiveRecordTemplate')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$this->recordAddsInstance = \App\RecordAddsTemplates::getInstance($request->getByType('recordAddsType', 'ClassName'));
		$this->recordAddsInstance->checkPermission();
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->modalIcon = $this->recordAddsInstance->icon;
		$this->pageTitle = $this->recordAddsInstance->label;
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('RECORD_STRUCTURE', $this->recordAddsInstance->getFields());
		$viewer->assign('MODULE_FORM', array_keys($this->recordAddsInstance->modulesFieldsMap));
		$viewer->assign('VIEW', 'recordTemplate');
		$viewer->assign('MODE', '');
		$viewer->assign('RECORD_TEMPLATE', $request->getByType('recordAddsType', 'ClassName'));
		$viewer->assign('BLOCK_LIST', $this->recordAddsInstance->getBlocks());
		$viewer->assign('RECORD', null);
		$viewer->assign('SCRIPTS', $this->getModalScripts($request));
		$viewer->view('RecordAddsTemplates.tpl');
	}
}
