<?php
/**
 *
 * Per identity smtp settings
 *
 * Description
 *
 * @version 0.1
 * @author elm@skweez.net, ritze@skweez.net, mks@skweez.net
 * @url skweez.net
 *
 * MIT License
 *
 **/

class identity_smtp extends rcube_plugin
{
	public $task = 'mail|settings';
	private $from_identity;

	function init()
	{
		$this->include_script('identity_smtp.js');
		$this->add_texts('localization/', true);

		$this->add_hook('message_outgoing_headers', array($this, 'messageOutgoingHeaders'));
		$this->add_hook('smtp_connect', array($this, 'smtpWillConnect'));
		$this->add_hook('identity_form', array($this, 'identityFormWillBeDisplayed'));
		$this->add_hook('identity_create', array($this, 'identityWasCreated'));
		$this->add_hook('identity_update', array($this, 'identityWasUpdated'));
		$this->add_hook('identity_delete', array($this, 'identityWasDeleted'));
		$this->add_hook('template_object_identityform', array($this, 'template_object_identityform'));
	}

	function smtpLog($message)
	{
		write_log("identity_smtp_plugin", $message);
	}

	function saveSmtpSettings($args)
	{
		$identities = rcmail::get_instance()->config->get('identity_smtp');
		$id = intval($args['id']);

		if (!isset($identities))
		{
			$identities = array();
		}	

		$smtp_standard = get_input_value('_smtp_standard', RCUBE_INPUT_POST);

		$password = get_input_value('_smtp_pass', RCUBE_INPUT_POST);
		
		if ($password != $identities[$id]['smtp_pass']) {
			$password = rcmail::get_instance()->encrypt($password);
		}

		$smtpSettingsRecord = array(
			'smtp_standard'		=> isset($smtp_standard),
			'smtp_server'		=> get_input_value('_smtp_server', RCUBE_INPUT_POST),
			'smtp_port'		=> get_input_value('_smtp_port', RCUBE_INPUT_POST),
			'smtp_user'		=> get_input_value('_smtp_user', RCUBE_INPUT_POST),
			'smtp_pass'		=> $password
		);
	
		unset($identities[$id]);
		$identities += array( $id => $smtpSettingsRecord );

		rcmail::get_instance()->user->save_prefs(array('identity_smtp' => $identities));
	}

	function loadSmtpSettings($args)
	{
		$smtpSettings = rcmail::get_instance()->config->get('identity_smtp');
		$id = intval($args['identity_id']);
		$smtpSettingsRecord = array(
			'smtp_standard'		=> $smtpSettings[$id]['smtp_standard'],
			'smtp_server'		=> $smtpSettings[$id]['smtp_server'],
			'smtp_port'		=> $smtpSettings[$id]['smtp_port'],
			'smtp_user'		=> $smtpSettings[$id]['smtp_user'],
			'smtp_pass'		=> $smtpSettings[$id]['smtp_pass']
		);

		if (is_null($smtpSettingsRecord['smtp_standard'])) {
			$smtpSettingsRecord['smtp_standard'] = true;
		}

		return $smtpSettingsRecord;
	}

	function identityFormWillBeDisplayed($args)
	{
		$form = $args['form'];
		$record = $args['record'];

		# Load the stored smtp settings
		$smtpSettingsRecord = $this->loadSmtpSettings($record);

		if (!isset($record['identity_id']))
		{
			# FIX ME
			$smtpSettingsForm = array('smtpSettings' => array(
				'name' => $this->gettext('smtp_settings_header'),
				'content' => array(
					'text' => array('label' => $this->gettext('smtp_settings_not_available'), 'value' => ' ')
					)
				));
		} else {
			$smtpSettingsForm = array('smtpSettings' => array(
				'name' => $this->gettext('smtp_settings_header'),
				'content' => array(
					'smtp_standard'		=> array('type' => 'checkbox', 
									'label' => $this->gettext('use_default_smtp_server'),
									'onclick' => 'identity_smtp_toggle_standard_server()'
								),
					'smtp_server'		=> array('type' => 'text',
									'label' => $this->gettext('smtp_server'), 
									'class' => 'identity_smtp_form'),
					'smtp_port'		=> array('type' => 'text',
									'label' => $this->gettext('smtp_port'),
									'class' => 'identity_smtp_form'),
					'smtp_user'		=> array('type' => 'text',
                                                                        'label' => $this->gettext('smtp_user'),
									'class' => 'identity_smtp_form'),
					'smtp_pass'		=> array('type' => 'password',
									'label' => $this->gettext('smtp_pass'),
									'class' => 'identity_smtp_form')
				)
			));
			if ($smtpSettingsRecord['smtp_standard'] || is_null($smtpSettingsRecord['smtp_standard'])) {
				foreach ($smtpSettingsForm['smtpSettings']['content'] as &$input) {
					if ($input['type'] != 'checkbox') {
						$input['disabled'] = 'disabled';
					}
				}
			}
		}

		$form = $form + $smtpSettingsForm;
		$record = $record + $smtpSettingsRecord;
		
		$OUTPUT = array('form' => $form,
			'record' => $record);
		return $OUTPUT;
	}

	# This function is called when a new identity is created. We want to use the default smtp server here
	function identityWasCreated($args)
	{
		$this->saveSmtpSettings($args);
		return $args;
	}

	# This function is called when the users saves a changed identity. It is responsible for saving the smtp settings
	function identityWasUpdated($args)
	{
		$this->saveSmtpSettings($args);
		return $args;
	}

	function identityWasDeleted($args)
	{
		$smtpSettings = rcmail::get_instance()->config->get('identity_smtp');
		$id = $args['id'];
		unset($smtpSettings[$id]);
		rcmail::get_instance()->user->save_prefs(array('identity_smtp' => $smtpSettings));

		# Return false to not abort the deletion of the identity
		return false;
	}

	function messageOutgoingHeaders($args)
	{
		$identities = rcmail::get_instance()->user->list_identities();
		foreach ($identities as $idx => $ident) {
			if ($identities[$idx]['email'] == $args['headers']['X-Sender']) {
				$this->from_identity = $identities[$idx]['identity_id'];
			}
		}

		return $args;
	}

	# This function is called when an email is sent and it should pull the correct smtp settings for the used identity and insert them
	function smtpWillConnect($args)
	{
		$smtpSettings = $this->loadSmtpSettings(array('identity_id' => $this->from_identity));
		if (!$smtpSettings['smtp_standard'] && !is_null($smtpSettings['smtp_standard'])) {
			$args['smtp_server'] = $smtpSettings['smtp_server'];
			$args['smtp_port'] = $smtpSettings['smtp_port'];
			$args['smtp_user'] = $smtpSettings['smtp_user'];
			$args['smtp_pass'] = rcmail::get_instance()->decrypt($smtpSettings['smtp_pass']);
		}
		return $args;
	}

	# Ugly hack to make the password field a password field...
	# FIX ME: Open a bug at trac.roundcube.net
	function template_object_identityform($args)
	{
		$args['content'] = preg_replace('#<input([a-zA-Z0-9=+/"_ ]*)type="text"([a-zA-Z0-9=+/"_ ]*)name="_smtp_pass"([a-zA-Z0-9=+/"_ ]*)/>#', '<input${1}type="password"${2}name="_smtp_pass"${3}/>' , $args['content']);
		return $args;
	}
}
?>
