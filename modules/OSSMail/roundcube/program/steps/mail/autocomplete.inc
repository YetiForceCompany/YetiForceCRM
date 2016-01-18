<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
// <--------   YetiForce Sp. z o.o.   -------->
$search = rcube_utils::get_input_value('_search', rcube_utils::INPUT_GPC, true);
$sid = rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC);
if (strlen($search)) {
	$contacts = [];

	$addressBookFile = '../../../cache/addressBook/mails_' . $RCMAIL->user->data['crm_user_id'] . '.php';
	if (!file_exists($addressBookFile)) {
		exit;
	}
	include $addressBookFile;
	foreach ($bookMails as $row) {
		if (strpos($row, $search) !== false) {
			$contacts[] = $row;
		}
	}
}
$OUTPUT->command('ksearch_query_results', $contacts, $search, $sid);
$OUTPUT->send();
// <--------   YetiForce Sp. z o.o.   -------->
