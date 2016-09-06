<?php
/**
 * CardDAV Cron Class
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$log = &LoggerManager::getLogger('CardDAV');
$log->debug('Start cron CardDAV');

API_DAV_Model::runCronCardDav($log);

$log->debug('End cron CardDAV');
