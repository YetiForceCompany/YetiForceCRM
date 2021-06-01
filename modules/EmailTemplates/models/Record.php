<?php

/**
 * Record model file.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Record class for Email Templates.
 */
class EmailTemplates_Record_Model extends Vtiger_Record_Model
{
	/** {@inheritdoc} */
	public function privilegeToDelete()
	{
		return $this->isEmpty('sys_name') && parent::privilegeToDelete();
	}

	/** {@inheritdoc} */
	public function privilegeToMoveToTrash()
	{
		return $this->isEmpty('sys_name') && parent::privilegeToMoveToTrash();
	}

	/** {@inheritdoc} */
	public function privilegeToArchive()
	{
		return $this->isEmpty('sys_name') && parent::privilegeToArchive();
	}
}
