<?php

/**
 * Class intended for status synchronization.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Project_SyncStatus_Model extends \App\SyncStatus
{
	/**
	 * {@inheritdoc}
	 */
	protected $currentColumnStatus = 'projectstatus';

	/**
	 * {@inheritdoc}
	 */
	protected $currentBaseTable = 'vtiger_project';

	/**
	 * {@inheritdoc}
	 */
	protected $currentBaseTableId = 'projectid';

	/**
	 * {@inheritdoc}
	 */
	protected $currentColumnParentId = 'parentid';

	/**
	 * {@inheritdoc}
	 */
	protected $subModuleName = 'ProjectMilestone';
}
