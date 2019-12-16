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
	public $pageTitle = 'LBL_EDIT_COLOR';
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-xl';

	/**
	 * Tree in popup.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('Modals/ColorModal.tpl', $moduleName);
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
