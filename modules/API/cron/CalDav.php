<?php
/**
 * CalDAV Cron Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
\App\Log::trace('Start cron CalDAV');
API_DAV_Model::runCronCalDav();
\App\Log::trace('End cron CalDAV');
