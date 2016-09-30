<?php
/**
 * CalDAV Cron Class
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
\App\log::trace('Start cron CalDAV');
API_DAV_Model::runCronCalDav();
\App\log::trace('End cron CalDAV');
