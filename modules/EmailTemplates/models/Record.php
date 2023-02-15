<?php

/**
 * Record model file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Record class for Email Templates.
 */
class EmailTemplates_Record_Model extends Vtiger_Record_Model
{
	/** {@inheritdoc} */
	public function privilegeToDelete(): bool
	{
		return $this->isEmpty('sys_name') && parent::privilegeToDelete();
	}

	/** {@inheritdoc} */
	public function privilegeToMoveToTrash(): bool
	{
		return $this->isEmpty('sys_name') && parent::privilegeToMoveToTrash();
	}

	/** {@inheritdoc} */
	public function privilegeToArchive(): bool
	{
		return $this->isEmpty('sys_name') && parent::privilegeToArchive();
	}
}
