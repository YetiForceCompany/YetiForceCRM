<?php
/**
 * CalDAV Cron Class
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$log = &LoggerManager::getLogger('CalDAV');
$log->debug('Start cron CalDAV');
API_DAV_Model::runCronCalDav($log);
$log->debug('End cron CalDAV');
