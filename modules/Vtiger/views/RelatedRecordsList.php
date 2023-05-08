<?php
/**
 * Record list in related view file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
/**
 * Record list in related view class.
 */
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
		$moduleName = $request->getModule();
		return array_merge(
			$this->checkAndConvertJsScripts([
				'modules.Vtiger.resources.RecordsList',
				"modules.{$moduleName}.resources.RecordsList",
			]),
			parent::getModalScripts($request));
	}
}
