<?php
/**
 * Record adds templates actions file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

use App\Request;

/**
 * Record adds templates actions class.
 */
class Vtiger_RecordAddsTemplates_Action extends \App\Controller\Action
{
	/** @var object Record adds instance. */
	public $recordAddsInstance;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Config::main('isActiveRecordTemplate')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$this->recordAddsInstance = \App\RecordAddsTemplates::getInstance($request->getByType('recordAddsType', 'ClassName'));
		$this->recordAddsInstance->checkPermission();
	}

	/** {@inheritdoc} */
	public function process(Request $request): void
	{
		$response = new Vtiger_Response();
		$response->setResult($this->recordAddsInstance->save($request));
		$response->emit();
	}
}
