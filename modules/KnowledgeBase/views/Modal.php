<?php

/**
 * Knowledge Base modal class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class KnowledgeBase_Modal_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-fullscreen';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView')) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Modal.tpl', $request->getModule(false));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(\App\Request $request)
	{
		return \App\Language::translate('LBL_UPLOAD_LOGO', $request->getModule(false));
	}

	/**
	 * {@inheritdoc}
	 */
	public function initializeContent(\App\Request $request)
	{ }

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{ }
	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~layouts/basic/modules/KnowledgeBase/Tree.vue.js', parent::getModalScripts($request)
		]));
	}
}
