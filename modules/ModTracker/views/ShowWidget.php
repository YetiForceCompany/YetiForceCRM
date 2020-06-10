<?php
/**
 * Show widget data.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * ModTracker_ShowWidget_View class.
 */
class ModTracker_ShowWidget_View extends Vtiger_ShowWidget_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($request->getInteger('widgetid'), \App\User::getCurrentUserId());
		if (!$widget->get('active')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}
}
