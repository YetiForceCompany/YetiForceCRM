<?php

/**
 * Detail
 * @package YetiForce.Models
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class KnowledgeBase_Detail_View extends Vtiger_Detail_View
{

	function showModuleSummaryView($request)
	{
		$previewContent = new KnowledgeBase_PreviewContent_View();
		$previewContent->process($request);
	}
}
