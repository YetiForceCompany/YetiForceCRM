<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
chdir (__DIR__ . '/../');

require_once 'config/api.php';
if(!in_array('webservice',$enabledServices)){
	die("{'status': 0,'error': 'YetiPortal - Service is not active'}");
}

echo "{status: 1, text: 'test'}";
