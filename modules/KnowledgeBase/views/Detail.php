<?php

/**
 * Detail View for KnowledgeBase
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class KnowledgeBase_Detail_View extends Vtiger_Detail_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showPreview');
	}

	public function showPreview($request)
	{
		$previewContent = new KnowledgeBase_PreviewContent_View();
		$previewContent->process($request);
	}
}
