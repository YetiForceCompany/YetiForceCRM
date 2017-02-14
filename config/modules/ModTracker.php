<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
$CONFIG = [
	// default view in History (Timeline/List)
	'DEFAULT_VIEW' => 'TimeLine',
	// Number of records on one page
	'NUMBER_RECORDS_ON_PAGE' => 50,
	// Enable sending notifications for all actions available in changes history.
	// Tracking requires enabling module or record tracking.
	'WATCHDOG' => true,
	// Displays the number of unreviewed changes in record.
	'UNREVIEWED_COUNT' => true,
	// Maximum length of text, only applies to text fields
	'TEASER_TEXT_LENGTH' => 400,
	// Max number to update records
	'REVIEW_CHANGES_LIMIT' => 50,
	// Max number to update records by cron
	'REVIEWED_SCHEDULE_LIMIT' => 1000,
	// Show timeline in listview [module name, ...]
	'SHOW_TIMELINE_IN_LISTVIEW' => [],
	// Limit of records displayed in timeline popup
	'TIMELINE_IN_LISTVIEW_LIMIT' => 5,
];
