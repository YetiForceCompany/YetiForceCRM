<?php
/**
 * MultiImages cron.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Lorencik <m.lorencik.com>
 */
Vtiger_Files_Model::getRidOfTrash(false, AppConfig::performance('CRON_MAX_ATACHMENTS_DELETE'));
