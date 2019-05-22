<?php

/**
 * Knowledge Base modal class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class KnowledgeBase_KnowledgeBaseModal_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('KnowledgeBaseModal.tpl', $request->getModule(false));
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

	public function getModalCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/@mdi/font/css/materialdesignicons.min.css',
			'~src/css/app.css'
		];
		return array_merge($headerCssInstances, $this->checkAndConvertCssStyles($cssFileNames));
	}
	/**
	 * {@inheritdoc}
	 */
	protected function preProcessTplName(\App\Request $request)
	{
		return 'KnowledgeBaseModalHeader.tpl';
	}
}
