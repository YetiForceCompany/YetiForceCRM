<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
// <--------   YetiForce Sp. z o.o.   -------->
class yt_signature extends rcube_plugin {
	function init() {
		//$this->add_hook('message_compose_body', array($this, 'message_compose_body'));
		$this->add_hook('render_page', array($this, 'render_page'));
	}

	function message_compose_body($args) {
		$args['body'] = '';
		return $args;
	}
	
	function render_page($response) {
		global $OUTPUT;
		if($this->checkAddSignature()){
			return;
		}
		$gS = $this->getGlobalSignature();
		if($gS['html'] == ''){
			return;
		}
		$a_signatures = array();
		foreach ($OUTPUT->get_env('signatures') as $identity_id => $signature) {
			$a_signatures[$identity_id]['text'] = $signature['text'].PHP_EOL.$gS['text'];
			$a_signatures[$identity_id]['html'] = $signature['html'].'<div class="pre global">'.$gS['html'].'</div>';
		}
		$OUTPUT->set_env('signatures', $a_signatures);
	}
	function getGlobalSignature() {
		global $RCMAIL;
        $db = $RCMAIL->get_dbh();
		$result = [];
        $sql_result = $db->query( "SELECT * FROM yetiforce_mail_config WHERE `type` = 'signature' AND `name` = 'signature';");

        while ($sql_arr = $db->fetch_assoc($sql_result)) {
			$result['html'] = $sql_arr['value'];
            $result['text'] = $sql_arr['value'];
        }
		return $result;
	}
	function checkAddSignature() {
		global $RCMAIL;
        $db = $RCMAIL->get_dbh();
		$result = [];
        $sql_result = $db->query( "SELECT * FROM yetiforce_mail_config WHERE `type` = 'signature' AND `name` = 'addSignature';");

        while ($sql_arr = $db->fetch_assoc($sql_result)) {
			return $sql_arr['value']=='false'?true:false;
        }
		return true;
	}
}
