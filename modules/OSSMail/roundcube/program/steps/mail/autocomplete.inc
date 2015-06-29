<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

$MAXNUM = (int) $RCMAIL->config->get('autocomplete_max', 15);
$single = (bool) $RCMAIL->config->get('autocomplete_single');
$search = rcube_utils::get_input_value('_search', rcube_utils::INPUT_GPC, true);
$source = rcube_utils::get_input_value('_source', rcube_utils::INPUT_GPC);
$sid    = rcube_utils::get_input_value('_reqid', rcube_utils::INPUT_GPC);

if(strlen($search)) {
    $contacts  = array();
    $sort_keys = array();
    //$search_lc = mb_strtolower($search);
	
	$addressBookFile = '../../../cache/addressBook/mails_'.$RCMAIL->user->data['crm_user_id'].'.php';
	if (file_exists($addressBookFile)) {
		include $addressBookFile;
		foreach($bookMails as $row){
			if (strpos($row, $search) !== false) {
				$contacts[] = $row;
			}
		}
	}
}
$OUTPUT->command('ksearch_query_results', $contacts, $search, $sid);
$OUTPUT->send();
