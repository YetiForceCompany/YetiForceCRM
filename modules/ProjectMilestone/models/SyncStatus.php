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
class ProjectMilestone_SyncStatus_Model extends \App\SyncStatus
{
	/**
	 * {@inheritdoc}
	 */
	protected $baseTable = 'vtiger_project';

	/**
	 * {@inheritdoc}
	 */
	protected $baseTableId = 'projectid';

	/**
	 * {@inheritdoc}
	 */
	protected $baseColumnStatus = 'projectstatus';

	/**
	 * {@inheritdoc}
	 */
	protected $baseModuleName = 'Project';

	/**
	 * {@inheritdoc}
	 */
	protected $baseColumnParentId = 'parentid';

	/**
	 * {@inheritdoc}
	 */
	protected $currentColumnStatus = 'projectmilestone_status';

	/**
	 * {@inheritdoc}
	 */
	protected $currentBaseTable = 'vtiger_projectmilestone';

	/**
	 * {@inheritdoc}
	 */
	protected $currentBaseTableId = 'projectmilestoneid';

	/**
	 * {@inheritdoc}
	 */
	protected $currentColumnParentId = 'parentid';

	/**
	 * {@inheritdoc}
	 */
	protected $subModuleName = 'ProjectTask';
}
