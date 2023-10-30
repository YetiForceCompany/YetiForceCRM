<?php
/**
 * ServiceContracts PolicyTemplatesAjax View class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ServiceContracts_PolicyTemplatesAjax_View extends Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	/** @var Vtiger_Record_Model Record model instance. */
	public $record;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('slaPolicyTemplate');
		$this->exposeMethod('slaPolicyCustom');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if (!$this->record->isViewable() || !$this->record->isPermitted('ServiceContractsSla') || !App\Privilege::isPermitted($request->getByType('targetModule', \App\Purifier::ALNUM))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function slaPolicyTemplate(App\Request $request)
	{
		$moduleName = $request->getModule();
		$sla = \App\Utils\ServiceContracts::getSlaPolicyForServiceContracts($request->getInteger('record'));
		$slaId = $sla[0]['sla_policy_id'] ?? 0;

		$viewer = $this->getViewer($request);
		$viewer->assign('TARGET_MODULE_ID', \App\Module::getModuleId($request->getByType('targetModule', \App\Purifier::ALNUM)));
		$viewer->assign('SELECTED_TEMPLATE', $slaId);

		return $viewer->view('SlaPolicyTemplate.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function slaPolicyCustom(App\Request $request)
	{
		$moduleName = $request->getModule();
		$index = $request->getInteger('index', time());
		$defaultEmptyData = [
			'id' => 0,
			'policy_type' => 2,
			'conditions' => '[]',
			'reaction_time' => '1:d',
			'idle_time' => '1:d',
			'resolve_time' => '1:d',
			'business_hours' => '',
		];

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $this->record);
		$viewer->assign('ALL_BUSINESS_HOURS', \App\Utils\ServiceContracts::getAllBusinessHours());
		$viewer->assign('SLA_POLICY_ROWS', [$index => $defaultEmptyData]);

		return $viewer->view('CustomConditions.tpl', $moduleName);
	}
}
