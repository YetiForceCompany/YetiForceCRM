<?php
/**
 * API config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
/* +********   active services   *********** */
//List of active services. To enable you must uncomment the appropriate line.
$enabledServices = [
    //'dav',
    //'webservices',
    //'webservice',
];
/* +***************   DAV   **************** */
$enableBrowser = false;
$enableCardDAV = false;
$enableCalDAV = false;
$enableWebDAV = false;

/* +*********   Webservice config  ********* */
$API_CONFIG = [
    'ENCRYPT_DATA_TRANSFER' => false,
    'AUTH_METHOD' => 'Basic',
    'PRIVATE_KEY' => 'config/private.key',
    'PUBLIC_KEY' => 'config/public.key',
];
