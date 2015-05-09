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
class yetiforce extends rcube_plugin {

	function init() {
		$rcmail = rcmail::get_instance();
		$this->add_hook('login_after', array($this, 'savePassword'));
		$this->register_action('plugin.yetiforce.addFilesToMail', array($this, 'addFilesToMail'));
		if ($rcmail->action == 'compose') {
			$rcmail->output->set_env('compose_commands', 'plugin.yetiforce');
			$this->include_script('yetiforce.js');
		}
		
		if ($rcmail->task == 'mail' && $rcmail->action == 'compose'){
			$this->add_hook('render_page', array($this, 'loadSignature'));
		}
		if ($rcmail->task == 'mail' && $rcmail->action == 'show'){
			$this->register_handler('plugin.getusername', array($this, 'getUserName'));
		}
	}

	//	Get user name
	function getUserName(){
		$rcmail = rcmail::get_instance();
		$user = $rcmail->user;
		return $user->data['username'];
	}
	
	//	Password saving
	function savePassword($args) {
		$rcmail = rcmail::get_instance();
		$pass = rcube_utils::get_input_value('_pass', rcube_utils::INPUT_POST);
		$sql = "UPDATE " . $rcmail->db->table_name('users') . " SET password = ? WHERE user_id = ?";
		call_user_func_array(array($rcmail->db, 'query'), array_merge(array($sql), array($pass, $rcmail->get_user_id())));
		$rcmail->db->affected_rows();
		return $args;
	}

	//	Loading signature
	function loadSignature($response) {
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
	
	//	Adding attachments
	public function addFilesToMail() {
		$COMPOSE_ID = rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC);
		$uploadid = rcube_utils::get_input_value('_uploadid', rcube_utils::INPUT_GPC);
		$COMPOSE = null;

		if ($COMPOSE_ID && $_SESSION['compose_data_' . $COMPOSE_ID]) {
			$SESSION_KEY = 'compose_data_' . $COMPOSE_ID;
			$COMPOSE = & $_SESSION[$SESSION_KEY];
		}
		if (!$COMPOSE) {
			die("Invalid session var!");
		}
		$rcmail = rcmail::get_instance();
		$index = 0;
		
		$attachments = self::getFiles();
		foreach ($attachments as $attachment) {
			$index++;
			$attachment['group'] = $COMPOSE_ID;
			$userid = rcmail::get_instance()->user->ID;
			list($usec, $sec) = explode(' ', microtime());
			$id = preg_replace('/[^0-9]/', '', $userid . $sec . $usec).$index;
			$attachment['id'] = $id;

			$_SESSION['plugins']['filesystem_attachments'][$COMPOSE_ID][$id] = $attachment['path'];
			$rcmail->session->append($SESSION_KEY . '.attachments', $id, $attachment);
			if (($icon = $COMPOSE['deleteicon']) && is_file($icon)) {
				$button = html::img(array(
					'src' => $icon,
					'alt' => $rcmail->gettext('delete')
				));
			} else if ($COMPOSE['textbuttons']) {
				$button = rcube::Q($rcmail->gettext('delete'));
			} else {
				$button = '';
			}

			$content = html::a(array(
				'href' => "#delete",
				'onclick' => sprintf("return %s.command('remove-attachment','rcmfile%s', this)", rcmail_output::JS_OBJECT_NAME, $id),
				'title' => $rcmail->gettext('delete'),
				'class' => 'delete',
				'aria-label' => $rcmail->gettext('delete') . ' ' . $attachment['name'],
				), $button
			);

			$content .= rcube::Q($attachment['name']);
			$htmlAttachments .= 'window.rcmail.add2attachment_list("rcmfile'.$id.'",{html:"<a href=\"#delete\" onclick=\"return rcmail.command(\'remove-attachment\',\'rcmfile'.$id.'\', this)\" title=\"'.$rcmail->gettext('delete').'\" class=\"delete\" aria-label=\"'.$rcmail->gettext('delete').' '.$attachment['name'].'\"><\/a>'.$attachment['name'].'",name:"'.$attachment['name'].'",mimetype:"'.$attachment['mimetype'].'",classname:"'.rcube_utils::file2class($attachment['mimetype'], $attachment['name']).'",complete:true},"'.$uploadid.'");'.PHP_EOL;
		}
$response = '<!DOCTYPE html>
<html lang="en">
<head><title></title><meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript">
if (window && window.rcmail) {
	window.rcmail.iframe_loaded("");
	'.$htmlAttachments.'
	window.rcmail.auto_save_start(false);
}
</script>
</head>
<body>
</body>
</html>';
		echo $response; 
		exit;
	}

	public function getFiles() {
		$files = [];
		$files = array_merge($files, self::getAttachment());
		return $files;
	}

	public function getAttachment() {
		$attachments = [];
		$ids = rcube_utils::get_input_value('ids', rcube_utils::INPUT_GPC);
		if (!isset($ids)) {
			return $attachments;
		}
		$rcmail = rcmail::get_instance();
		$db = $rcmail->get_dbh();
		$ids = implode(',', $ids);
		$userid = $rcmail->user->ID;
		$index = 0;
		$sql_result = $db->query("SELECT * FROM vtiger_attachments WHERE attachmentsid IN ($ids);");
		while ($row = $db->fetch_assoc($sql_result)) {
			$orgFile = $rcmail->config->get('root_directory') . $row['path'] . $row['attachmentsid'] . '_' . $row['name'];
			list($usec, $sec) = explode(' ', microtime());
			$filepath = $rcmail->config->get('root_directory') . 'modules/OSSMail/roundcube/temp/'.$sec.$userid.$row['attachmentsid'].$index.'.tmp';
			if (file_exists($orgFile)) {
				copy($orgFile, $filepath);
				$attachment = [
					'path' => $filepath,
					'size' => filesize($filepath),
					'name' => $row['name'],
					'mimetype' => rcube_mime::file_content_type($filepath, $row['name'], $row['type']),
				];
				$attachments[] = $attachment;
			}
			$index++;
		}
		return $attachments;
	}
}
