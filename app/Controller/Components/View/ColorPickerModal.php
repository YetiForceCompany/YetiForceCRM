<?php
/**
 * Color picker modal view class file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller\Components\View;

/**
 * Color picker modal view class.
 */
class ColorPickerModal extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-xl';

	/** {@inheritdoc} */
	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	/**
	 * Set modal title.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$this->modalIcon = 'yfi-calendar-labels-colors';
		$this->pageTitle = \App\Language::translate('LBL_EDIT_COLOR', $request->getModule());
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('COLOR', !$request->isEmpty('color') ? $request->getByType('color', \App\Purifier::ALNUM) : '');
		$viewer->view('ColorPickerModal.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getModalScripts(\App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~layouts/resources/libraries/ColorPicker/ColorPicker.vue.js',
		], parent::getModalScripts($request)));
	}
}
