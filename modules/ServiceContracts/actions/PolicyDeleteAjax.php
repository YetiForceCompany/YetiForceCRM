<?php
/**
 * ServiceContracts Policy DeleteAjax Action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_PolicyDeleteAjax_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$record = Vtiger_DetailView_Model::getInstance($request->getModule(), $request->getInteger('record'));
		if (!$record->getRecord()->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		\App\Utils\ServiceContracts::deleteSlaPolicy($request->getInteger('record'), \App\Module::getModuleId($request->getByType('targetModule', 'Alnum')));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
