<?php

class Vtiger_RelatedRecordsList_View extends Vtiger_RecordsList_View
{
	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		parent::process($request);
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.RecordsList',
			"modules.{$request->getModule()}.resources.RecordsList",
		]));
	}
}
