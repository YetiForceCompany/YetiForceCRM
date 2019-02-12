<?php

/**
 * Detail View for KnowledgeBase.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class KnowledgeBase_Detail_View extends Vtiger_Detail_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showPreview');
	}

	/**
	 * Shows preview.
	 *
	 * @param \App\Request $request
	 */
	public function showPreview(\App\Request $request)
	{
		$previewContent = new KnowledgeBase_PreviewContent_View();
		$previewContent->process($request);
	}
}
