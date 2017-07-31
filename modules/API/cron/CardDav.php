<?php
/**
 * CardDAV Cron Class
 * @package YetiForce.Cron
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
\App\Log::trace('Start cron CardDAV');

API_DAV_Model::runCronCardDav();

\App\Log::trace('End cron CardDAV');
