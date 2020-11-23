<?php
/**
 * Color picker modal view class file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

namespace App\Components;

/**
 * Color picker modal view class.
 */
class ColorPickerModal extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-xl';

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		if ($request->has('color')) {
			$viewer->assign('COLOR', $request->getByType('color', 'Alnum'));
		}
		$viewer->view('ColorPickerModal.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(\App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~layouts/resources/libraries/ColorPicker/ColorPicker.vue.js',
		], parent::getModalScripts($request)));
	}
}
