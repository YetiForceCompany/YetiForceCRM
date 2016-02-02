<?php

class KnowledgeBase_ContentAJAX_View extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		if (!empty($recordId)) {
			$previewContent = new KnowledgeBase_PreviewContent_View();
			$previewContent->process($request);
		} else {
			
		}
	}
}
