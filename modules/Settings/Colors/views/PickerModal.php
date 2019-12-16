<?php

/**
 * Base color picker modal view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class Settings_Colors_PickerModal_View extends \App\Controller\ModalSettings
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-xl';

	/**
	 * Set modal title.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessAjax(App\Request $request)
	{
		$this->qualifiedModuleName = $request->getModule(false);
		$this->modalIcon = 'yfi-calendar-labels-colors';
		$this->pageTitle = \App\Language::translate('LBL_EDIT_COLOR', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * Tree in popup.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('COLOR', $request->getRaw('color'));
		$viewer->view('ColorModal.tpl', $this->qualifiedModuleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~layouts/resources/libraries/ColorPicker/ColorPicker.vue.js',
		], parent::getModalScripts($request)));
	}
}
