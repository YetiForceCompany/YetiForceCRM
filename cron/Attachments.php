<?php
/**
 * MultiImages cron
 * @package YetiForce.Cron
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Michał Lorencik <m.lorencik.com>
 */
Vtiger_Files_Model::getRidOfTrash(false, AppConfig::performance('CRON_MAX_ATACHMENTS_DELETE'));
