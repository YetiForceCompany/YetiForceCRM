<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
$CONFIG = [
	// Individual batch limit - Specified number of records will be imported at one shot and the cycle will repeat till all records are imported
	'BATCH_LIMIT' => 250,
	// Threshold record limit for immediate import. If record count is more than this, then the import is scheduled through cron job
	'IMMEDIATE_IMPORT_LIMIT' => 1000,
	// Records for reference fields modules are created while importing, when record is not found.
	'CREATE_REFERENCE_RECORD' => false,
	// Save record including handlers
	'SAVE_BY_HANDLERS' => true,
	// Missing picklist values are added
	'ADD_PICKLIST_VALUE' => true,
];
