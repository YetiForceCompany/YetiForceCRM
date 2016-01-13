<?php

/**
 * Integration Plugin yetiforce and roundcube
 * @package YetiForce.rcubePlugin
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class yetiforce extends rcube_plugin
{

	private $rc;

	function init()
	{
		$this->rc = rcmail::get_instance();
		$this->add_hook('login_after', [$this, 'savePassword']);

		if ($this->rc->task == 'mail') {
			$this->register_action('plugin.yetiforce.addFilesToMail', [$this, 'addFilesToMail']);
			$this->rc->output->set_env('site_URL', $this->rc->config->get('site_URL'));
			
			if ($this->rc->action == 'compose') {
				$this->add_texts('localization/', false);
				$this->include_script('compose.js');

				$this->add_hook('message_compose_body', [$this, 'messageComposeBody']);
				$this->add_hook('message_compose', [$this, 'messageComposeHead']);
				$this->add_hook('render_page', [$this, 'loadSignature']);

				$id = rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC);
				if ($id && array_key_exists('module', $_SESSION['compose_data_' . $id]['param'])) {
					$this->rc->output->set_env('crmModule', $_SESSION['compose_data_' . $id]['param']['module']);
				}
				if ($id && array_key_exists('record', $_SESSION['compose_data_' . $id]['param'])) {
					$this->rc->output->set_env('crmRecord', $_SESSION['compose_data_' . $id]['param']['record']);
				}
				if ($id && array_key_exists('view', $_SESSION['compose_data_' . $id]['param'])) {
					$this->rc->output->set_env('crmView', $_SESSION['compose_data_' . $id]['param']['view']);
				}
			}
			if ($this->rc->action == 'preview' || $this->rc->action == 'show') {
				$this->include_script('preview.js');
				$this->include_stylesheet($this->rc->config->get('site_URL') . 'libraries/bootstrap3/css/glyphicon.css');
				$this->include_stylesheet($this->rc->config->get('site_URL') . 'layouts/basic/skins/icons/userIcons.css');
				$this->include_stylesheet('preview.css');
				$this->add_hook('message_load', [$this, 'messageLoad']);
			}
		}
	}

	public function messageLoad($args)
	{
		if (!isset($args['object'])) {
			return;
		}
		$this->rc->output->set_env('subject', $args['object']->headers->subject);
		$from = $args['object']->headers->from;
		$from = explode('<', rtrim($from, '>'),2);
		$fromName = '';
		if(count($from) > 1){
			$fromName = $from[0];
			$fromMail = $from[1];
		}else{
			$fromMail = $from[0];
		}
		$this->rc->output->set_env('fromName', $fromName);
		$this->rc->output->set_env('fromMail', $fromMail);
	}

	function messageComposeHead($args)
	{
		$this->rc = rcmail::get_instance();
		$db = $this->rc->get_dbh();
		global $COMPOSE_ID;

		$id = $COMPOSE_ID;
		$type = rcube_utils::get_input_value('type', rcube_utils::INPUT_GPC);
		$crmid = rcube_utils::get_input_value('crmid', rcube_utils::INPUT_GPC);
		$crmmodule = rcube_utils::get_input_value('crmmodule', rcube_utils::INPUT_GPC);
		$crmrecord = rcube_utils::get_input_value('crmrecord', rcube_utils::INPUT_GPC);
		$crmview = rcube_utils::get_input_value('crmview', rcube_utils::INPUT_GPC);
		if ($crmmodule) {
			$_SESSION['compose_data_' . $id]['param']['module'] = $crmmodule;
		}
		if ($crmrecord) {
			$_SESSION['compose_data_' . $id]['param']['record'] = $crmrecord;
		}
		if ($crmview) {
			$_SESSION['compose_data_' . $id]['param']['view'] = $crmview;
		}

		if (!$crmid) {
			return;
		}
		$crmid = filter_var($crmid, FILTER_SANITIZE_STRING);
		$result = $db->query("SELECT content,reply_to_email,date,from_email,to_email,cc_email,subject FROM vtiger_ossmailview WHERE ossmailviewid = '$crmid';");
		$row = $db->fetch_assoc($result);
		$_SESSION['compose_data_' . $id]['param']['type'] = $type;
		$_SESSION['compose_data_' . $id]['param']['mailData'] = $row;
		switch ($type) {
			case 'replyAll':
				$cc = $row['to_email'];
				$cc .= ',' . $row['cc_email'];
				$cc = str_replace($row['from_email'] . ',', '', $cc);
				$cc = trim($cc, ',');
			case 'reply':
				$to = $row['reply_to_email'];
				if (preg_match('/^re:/i', $row['subject']))
					$subject = $row['subject'];
				else
					$subject = 'Re: ' . $row['subject'];
				$subject = preg_replace('/\s*\([wW]as:[^\)]+\)\s*$/', '', $subject);
				break;
			case 'forward':
				if (preg_match('/^fwd:/i', $row['subject']))
					$subject = $row['subject'];
				else
					$subject = 'Fwd: ' . $row['subject'];
				break;
		}
		$args['param']['to'] = $to;
		$args['param']['cc'] = $cc;
		$args['param']['subject'] = $subject;
		return $args;
	}

	function messageComposeBody($args)
	{
		$this->rc = rcmail::get_instance();
		$db = $this->rc->get_dbh();

		$id = rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC);
		$row = $_SESSION['compose_data_' . $id]['param']['mailData'];
		$type = $_SESSION['compose_data_' . $id]['param']['type'];
		if (!$row) {
			return;
		}
		$bodyIsHtml = $args['html'];
		$date = $row['date'];
		$from = $row['from_email'];
		$to = $row['to_email'];
		$body = $row['content'];
		$subject = $row['subject'];
		$replyto = $row['reply_to_email'];

		$prefix = $suffix = '';
		if ($type == 'forward') {
			if (!$bodyIsHtml) {
				$prefix = "\n\n\n-------- " . $this->rc->gettext('originalmessage') . " --------\n";
				$prefix .= $this->rc->gettext('subject') . ': ' . $subject . "\n";
				$prefix .= $this->rc->gettext('date') . ': ' . $date . "\n";
				$prefix .= $this->rc->gettext('from') . ': ' . $from . "\n";
				$prefix .= $this->rc->gettext('to') . ': ' . $to . "\n";
				if ($cc = $row['cc_email']) {
					$prefix .= $this->rc->gettext('cc') . ': ' . $cc . "\n";
				}
				if ($replyto != $from) {
					$prefix .= $this->rc->gettext('replyto') . ': ' . $replyto . "\n";
				}
				$prefix .= "\n";
				global $LINE_LENGTH;
				$txt = new rcube_html2text($body, false, true, $LINE_LENGTH);
				$body = $txt->get_text();
				$body = preg_replace('/\r?\n/', "\n", $body);
				$body = trim($body, "\n");
			} else {
				$prefix = sprintf(
					"<p>-------- " . $this->rc->gettext('originalmessage') . " --------</p>" .
					"<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody>" .
					"<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>" .
					"<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>" .
					"<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>" .
					"<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>", $this->rc->gettext('subject'), rcube::Q($subject), $this->rc->gettext('date'), rcube::Q($date), $this->rc->gettext('from'), rcube::Q($from, 'replace'), $this->rc->gettext('to'), rcube::Q($to, 'replace'));
				if ($cc = $row['cc_email'])
					$prefix .= sprintf("<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>", $this->rc->gettext('cc'), rcube::Q($cc, 'replace'));
				if ($replyto != $from)
					$prefix .= sprintf("<tr><th align=\"right\" nowrap=\"nowrap\" valign=\"baseline\">%s: </th><td>%s</td></tr>", $this->rc->gettext('replyto'), rcube::Q($replyto, 'replace'));
				$prefix .= "</tbody></table><br>";
			}
			$body = $prefix . $body;
		}else {
			$prefix = $this->rc->gettext(array(
				'name' => 'mailreplyintro',
				'vars' => array(
					'date' => $this->rc->format_date($date, $this->rc->config->get('date_long')),
					'sender' => $from,
				)
			));
			if (!$bodyIsHtml) {
				global $LINE_LENGTH;
				$txt = new rcube_html2text($body, false, true, $LINE_LENGTH);
				$body = $txt->get_text();
				$body = preg_replace('/\r?\n/', "\n", $body);
				$body = trim($body, "\n");
				$body = rcmail_wrap_and_quote($body, $LINE_LENGTH);
				$prefix .= "\n";
				$body = $prefix . $body . $suffix;
			} else {
				$body = $prefix . '<blockquote>' . $body . '</blockquote>' . $suffix;
				$prefix = '<p>' . rcube::Q($prefix) . "</p>\n";
			}
		}
		$args['body'] = $body;
		return $args;
	}

	//	Password saving
	function savePassword($args)
	{
		$this->rc = rcmail::get_instance();
		$pass = rcube_utils::get_input_value('_pass', rcube_utils::INPUT_POST);
		$sql = "UPDATE " . $this->rc->db->table_name('users') . " SET password = ? WHERE user_id = ?";
		call_user_func_array(array($this->rc->db, 'query'), array_merge(array($sql), array($pass, $this->rc->get_user_id())));
		$this->rc->db->affected_rows();
		return $args;
	}

	//	Loading signature
	function loadSignature($response)
	{
		global $OUTPUT;
		if ($this->checkAddSignature()) {
			return;
		}
		$gS = $this->getGlobalSignature();
		if ($gS['html'] == '') {
			return;
		}
		$a_signatures = array();
		foreach ($OUTPUT->get_env('signatures') as $identity_id => $signature) {
			$a_signatures[$identity_id]['text'] = $signature['text'] . PHP_EOL . $gS['text'];
			$a_signatures[$identity_id]['html'] = $signature['html'] . '<div class="pre global">' . $gS['html'] . '</div>';
		}
		$OUTPUT->set_env('signatures', $a_signatures);
	}

	function getGlobalSignature()
	{
		global $RCMAIL;
		$db = $RCMAIL->get_dbh();
		$result = [];
		$sql_result = $db->query("SELECT * FROM yetiforce_mail_config WHERE `type` = 'signature' AND `name` = 'signature';");

		while ($sql_arr = $db->fetch_assoc($sql_result)) {
			$result['html'] = $sql_arr['value'];
			$result['text'] = $sql_arr['value'];
		}
		return $result;
	}

	function checkAddSignature()
	{
		global $RCMAIL;
		$db = $RCMAIL->get_dbh();
		$result = [];
		$sql_result = $db->query("SELECT * FROM yetiforce_mail_config WHERE `type` = 'signature' AND `name` = 'addSignature';");

		while ($sql_arr = $db->fetch_assoc($sql_result)) {
			return $sql_arr['value'] == 'false' ? true : false;
		}
		return true;
	}

	//	Adding attachments
	public function addFilesToMail()
	{
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
		$this->rc = rcmail::get_instance();
		$index = 0;

		$attachments = self::getFiles();
		foreach ($attachments as $attachment) {
			$index++;
			$attachment['group'] = $COMPOSE_ID;
			$userid = rcmail::get_instance()->user->ID;
			list($usec, $sec) = explode(' ', microtime());
			$id = preg_replace('/[^0-9]/', '', $userid . $sec . $usec) . $index;
			$attachment['id'] = $id;

			$_SESSION['plugins']['filesystem_attachments'][$COMPOSE_ID][$id] = $attachment['path'];
			$this->rc->session->append($SESSION_KEY . '.attachments', $id, $attachment);
			if (($icon = $COMPOSE['deleteicon']) && is_file($icon)) {
				$button = html::img(array(
						'src' => $icon,
						'alt' => $this->rc->gettext('delete')
				));
			} else if ($COMPOSE['textbuttons']) {
				$button = rcube::Q($this->rc->gettext('delete'));
			} else {
				$button = '';
			}

			$content = html::a(array(
					'href' => "#delete",
					'onclick' => sprintf("return %s.command('remove-attachment','rcmfile%s', this)", rcmail_output::JS_OBJECT_NAME, $id),
					'title' => $this->rc->gettext('delete'),
					'class' => 'delete',
					'aria-label' => $this->rc->gettext('delete') . ' ' . $attachment['name'],
					), $button
			);

			$content .= rcube::Q($attachment['name']);
			$htmlAttachments .= 'window.rcmail.add2attachment_list("rcmfile' . $id . '",{html:"<a href=\"#delete\" onclick=\"return rcmail.command(\'remove-attachment\',\'rcmfile' . $id . '\', this)\" title=\"' . $this->rc->gettext('delete') . '\" class=\"delete\" aria-label=\"' . $this->rc->gettext('delete') . ' ' . $attachment['name'] . '\"><\/a>' . $attachment['name'] . '",name:"' . $attachment['name'] . '",mimetype:"' . $attachment['mimetype'] . '",classname:"' . rcube_utils::file2class($attachment['mimetype'], $attachment['name']) . '",complete:true},"' . $uploadid . '");' . PHP_EOL;
		}
		$response = '<!DOCTYPE html>
<html lang="en">
<head><title></title><meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript">
if (window && window.rcmail) {
	window.rcmail.iframe_loaded("");
	' . $htmlAttachments . '
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

	public function getFiles()
	{
		$files = [];
		$files = array_merge($files, self::getAttachment());
		return $files;
	}

	public function getAttachment()
	{
		$attachments = [];
		$ids = rcube_utils::get_input_value('ids', rcube_utils::INPUT_GPC);
		if (!isset($ids)) {
			return $attachments;
		}
		$this->rc = rcmail::get_instance();
		$db = $this->rc->get_dbh();
		$ids = implode(',', $ids);
		$userid = $this->rc->user->ID;
		$index = 0;
		$sql_result = $db->query("SELECT vtiger_attachments.* FROM vtiger_attachments INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid=vtiger_attachments.attachmentsid WHERE vtiger_seattachmentsrel.crmid IN ($ids);");
		while ($row = $db->fetch_assoc($sql_result)) {
			$orgFile = $this->rc->config->get('root_directory') . $row['path'] . $row['attachmentsid'] . '_' . $row['name'];
			list($usec, $sec) = explode(' ', microtime());
			$filepath = $this->rc->config->get('root_directory') . 'modules/OSSMail/roundcube/temp/' . $sec . $userid . $row['attachmentsid'] . $index . '.tmp';
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

	function rcmail_wrap_and_quote($text, $length = 72)
	{
		// Rebuild the message body with a maximum of $max chars, while keeping quoted message.
		$max = max(75, $length + 8);
		$lines = preg_split('/\r?\n/', trim($text));
		$out = '';

		foreach ($lines as $line) {
			// don't wrap already quoted lines
			if ($line[0] == '>') {
				$line = '>' . rtrim($line);
			} else if (mb_strlen($line) > $max) {
				$newline = '';

				foreach (explode("\n", rcube_mime::wordwrap($line, $length - 2)) as $l) {
					if (strlen($l))
						$newline .= '> ' . $l . "\n";
					else
						$newline .= ">\n";
				}

				$line = rtrim($newline);
			}
			else {
				$line = '> ' . $line;
			}

			// Append the line
			$out .= $line . "\n";
		}

		return rtrim($out, "\n");
	}
}
