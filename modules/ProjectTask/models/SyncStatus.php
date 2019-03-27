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
class ProjectTask_SyncStatus_Model extends \App\SyncStatus
{
	/**
	 * {@inheritdoc}
	 */
	protected $baseTable = 'vtiger_projectmilestone';

	/**
	 * {@inheritdoc}
	 */
	protected $baseTableId = 'projectmilestoneid';

	/**
	 * {@inheritdoc}
	 */
	protected $baseColumnStatus = 'projectmilestone_status';

	/**
	 * {@inheritdoc}
	 */
	protected $baseModuleName = 'ProjectMilestone';

	/**
	 * {@inheritdoc}
	 */
	protected $baseColumnParentId = 'parentid';

	/**
	 * {@inheritdoc}
	 */
	protected $currentColumnStatus = 'projecttaskstatus';

	/**
	 * {@inheritdoc}
	 */
	protected $currentBaseTable = 'vtiger_projecttask';

	/**
	 * {@inheritdoc}
	 */
	protected $currentBaseTableId = 'projecttaskid';
}
