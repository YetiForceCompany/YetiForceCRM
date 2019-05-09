<?php
/**
 * Save record.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal;

/**
 * Action to save record.
 */
class Save extends \Vtiger_Save_Action
{
	/**
	 * Id od application.
	 *
	 * @var int
	 */
	protected $appId;

	/**
	 * Constructor.
	 *
	 * @param int $id
	 */
	public function __construct(int $id)
	{
		$this->appId = $id;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getRecordModelFromRequest(\App\Request $request)
	{
		$record = parent::getRecordModelFromRequest($request);
		$fieldInfo = \Api\Core\Module::getFieldPermission($request->getModule(), $this->appId);
		if ($fieldInfo) {
			$record->setDataForSave([$fieldInfo['tablename'] => [$fieldInfo['columnname'] => 1]]);
		}
		return $this->record;
	}
}
