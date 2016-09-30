<?php
/**
 * CardDAV Cron Class
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
\App\log::trace('Start cron CardDAV');

API_DAV_Model::runCronCardDav();

\App\log::trace('End cron CardDAV');
