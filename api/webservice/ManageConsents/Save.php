<?php
/**
 * Save record.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\ManageConsents;

/**
 * Action to save record.
 */
class Save extends \Vtiger_Save_Action
{
	/**
	 * ID of application.
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
	 * Function sets the record data based on the request parameters.
	 *
	 * @param \App\Request $request
	 * @param bool         $userFormat
	 *
	 * @return $this
	 */
	public function setDataFromRequest(\App\Request $request)
	{
		$this->record = parent::getRecordModelFromRequest($request);
		return $this;
	}

	/**
	 * Set record model.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return $this
	 */
	public function setRecordModel(\Vtiger_Record_Model $recordModel)
	{
		$this->record = $recordModel;
		return $this;
	}
}
