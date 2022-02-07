<?php
/**
 * ServiceContracts PolicyTemplatesAjax Action class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_PolicyTemplatesAjax_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$record = Vtiger_DetailView_Model::getInstance($request->getModule(), $request->getInteger('record'));
		if (!$record->getRecord()->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$sla = \App\Utils\ServiceContracts::getSlaPolicyForServiceContracts($request->getInteger('record'));
		$slaId = $sla ? $sla[0]['sla_policy_id'] : false;
		$rows = [];
		foreach (\App\Utils\ServiceContracts::getSlaPolicyByModule(\App\Module::getModuleId($request->getByType('targetModule', 'Alnum'))) as $row) {
			$moduleName = \App\Module::getModuleName($row['tabid']);
			$row['tabid'] = \App\Language::translate($moduleName, $moduleName);
			$row['operational_hours'] = $row['operational_hours'] ? \App\Language::translate('LBL_CALENDAR_HOURS', 'ServiceContracts') : \App\Language::translate('LBL_BUSINESS_HOURS', 'ServiceContracts');
			$row['business_hours'] = implode(', ', array_column(\App\Utils\ServiceContracts::getBusinessHoursByIds(explode(',', $row['business_hours'])), 'name'));
			$row['reaction_time'] = \App\Fields\TimePeriod::getLabel($row['reaction_time']);
			$row['idle_time'] = \App\Fields\TimePeriod::getLabel($row['idle_time']);
			$row['resolve_time'] = \App\Fields\TimePeriod::getLabel($row['resolve_time']);
			if ($row['id'] === $slaId) {
				$row['checked'] = true;
			} else {
				$row['checked'] = false;
			}
			$rows[] = $row;
		}
		$response = new Vtiger_Response();
		$response->setResult($rows);
		$response->emit();
	}
}
