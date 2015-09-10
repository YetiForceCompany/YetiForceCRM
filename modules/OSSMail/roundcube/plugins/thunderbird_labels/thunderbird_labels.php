<?php
/**
 * Thunderbird Labels Plugin for Roundcube Webmail
 *
 * Plugin to show the 5 Message Labels Thunderbird Email-Client provides for IMAP
 *
 * @version $Revision$
 * @author Michael Kefeder
 * @url http://code.google.com/p/rcmail-thunderbird-labels/
 */
class thunderbird_labels extends rcube_plugin
{
	public $task = 'mail|settings';
	private $rc;
	private $map;
	
	function init()
	{
		$this->rc = rcmail::get_instance();
		$this->load_config();
		$this->add_texts('localization/', false);
		
		$this->setCustomLabels();

		if ($this->rc->task == 'mail')
		{
			# -- disable plugin when printing message
			if ($this->rc->action == 'print')
				return;
			
			if (!$this->rc->config->get('tb_label_enable'))
			// disable plugin according to prefs
				return;
      
			// pass 'tb_label_enable_shortcuts' and 'tb_label_style' prefs to JS
			$this->rc->output->set_env('tb_label_enable_shortcuts', $this->rc->config->get('tb_label_enable_shortcuts'));
			$this->rc->output->set_env('tb_label_style', $this->rc->config->get('tb_label_style'));
			
			$this->include_script('tb_label.js');
			$this->add_hook('messages_list', array($this, 'read_flags'));
			$this->add_hook('message_load', array($this, 'read_single_flags'));
			$this->add_hook('template_object_messageheaders', array($this, 'color_headers'));
			$this->add_hook('render_page', array($this, 'tb_label_popup'));
			$this->include_stylesheet($this->local_skin_path() . '/tb_label.css');
			
			$this->name = get_class($this);
			# -- additional TB flags
			$this->add_tb_flags = array(
			'LABEL1' => '$Label1',
			'LABEL2' => '$Label2',
			'LABEL3' => '$Label3',
			'LABEL4' => '$Label4',
			'LABEL5' => '$Label5',
			);
			$this->message_tb_labels = array();
			
			$this->add_button(
				array(
					'command' => 'plugin.thunderbird_labels.rcm_tb_label_submenu',
					'id' => 'tb_label_popuplink',
					'title' => 'tb_label_button_title',
					'domain' => $this->ID,
					'type' => 'link',
					'content' => $this->gettext('tb_label_button_label'), 
					'class' => 'button buttonPas disabled',
					'classact' => 'button',
					),
				'toolbar'
			);
    
			// JS function "set_flags" => PHP function "set_flags"
			$this->register_action('plugin.thunderbird_labels.set_flags', array($this, 'set_flags'));
    
    
			if (method_exists($this, 'require_plugin')
				&& in_array('contextmenu', $this->rc->config->get('plugins'))
				&& $this->require_plugin('contextmenu')
				&& $this->rc->config->get('tb_label_enable_contextmenu'))
			{
				if ($this->rc->action == '')
					$this->add_hook('render_mailboxlist', array($this, 'show_tb_label_contextmenu'));
			}
		}
		elseif ($this->rc->task == 'settings')
		{
			$this->include_stylesheet($this->local_skin_path() . '/tb_label.css');
			$this->add_hook('preferences_list', array($this, 'prefs_list'));
			$this->add_hook('preferences_sections_list', array($this, 'prefs_section'));
			$this->add_hook('preferences_save', array($this, 'prefs_save'));
		}
	}

	private function setCustomLabels()
	{
		$c = $this->rc->config->get('tb_label_custom_labels');
		if (empty($c))
		{
			// if no user specific labels, use localized strings by default
			$this->rc->config->set('tb_label_custom_labels', array(
				0 => $this->getText('label0'),
				1 => $this->getText('label1'),
				2 => $this->getText('label2'),
				3 => $this->getText('label3'),
				4 => $this->getText('label4'),
				5 => $this->getText('label5')
			));
		}
		// pass label strings to JS
		$this->rc->output->set_env('tb_label_custom_labels', $this->rc->config->get('tb_label_custom_labels'));
	}

	// create a section for the tb-labels Settings
	public function prefs_section($args)
    {
        $args['list']['thunderbird_labels'] = array(
    	        'id' => 'thunderbird_labels',
	            'section' => Q($this->gettext('tb_label_options'))
		);

        return $args;
    }

	// display thunderbird-labels prefs in Roundcube Settings
	public function prefs_list($args)
	{
		if ($args['section'] != 'thunderbird_labels')
			return $args;

		$this->load_config();
		$dont_override = (array) $this->rc->config->get('dont_override', array());
		
		$args['blocks']['tb_label'] = array();
		$args['blocks']['tb_label']['name'] = $this->gettext('tb_label_options');
		
		$key = 'tb_label_enable';
		if (!in_array($key, $dont_override))
		{
			$input = new html_checkbox(array(
				'name' => $key,
				'id' => $key,
				'value' => 1
			));
			$content = $input->show($this->rc->config->get($key));
			$args['blocks']['tb_label']['options'][$key] = array(
				'title' => $this->gettext('tb_label_enable_option'),
				'content' => $content
			);
		}
		
		$key = 'tb_label_enable_shortcuts';
		if (!in_array($key, $dont_override))
		{
			$input = new html_checkbox(array(
				'name' => $key,
				'id' => $key,
				'value' => 1
			));
			$content = $input->show($this->rc->config->get($key));
			$args['blocks']['tb_label']['options'][$key] = array(
				'title' => $this->gettext('tb_label_enable_shortcuts_option'),
				'content' => $content
			);
		}
		
		$key = 'tb_label_style';
		if (!in_array($key, $dont_override))
		{
			$select = new html_select(array(
				'name' => $key,
				'id' => $key
			));
			$select->add(array($this->gettext('thunderbird'), $this->gettext('bullets')), array('thunderbird', 'bullets'));
			$content = $select->show($this->rc->config->get($key));
			
			$args['blocks']['tb_label']['options'][$key] = array(
				'title' => $this->gettext('tb_label_style_option'),
				'content' => $content
			);    
		}
		
		$key = 'tb_label_custom_labels';
		if (!in_array($key, $dont_override)
			&& $this->rc->config->get('tb_label_modify_labels'))
		{
			$old = $this->rc->config->get($key);
			for($i=1; $i<=5; $i++)
			{
				$input = new html_inputfield(array(
					'name' => $key.$i,
					'id' => $key.$i,
					'type' => 'text',
					'autocomplete' => 'off',
					'value' => $old[$i]));
			
				$args['blocks']['tb_label']['options'][$key.$i] = array(
					'title' => $this->gettext('tb_label_label')." ".$i,
					'content' => $input->show()
					);
			}
		}
		
		return $args;
	}

	// save prefs after modified in UI  
	public function prefs_save($args)
	{
		if ($args['section'] != 'thunderbird_labels')
		  return $args;

		
		$this->load_config();
		$dont_override = (array) $this->rc->config->get('dont_override', array());
		
		if (!in_array('tb_label_enable', $dont_override))
			$args['prefs']['tb_label_enable'] = get_input_value('tb_label_enable', RCUBE_INPUT_POST) ? true : false;
		
		if (!in_array('tb_label_enable_shortcuts', $dont_override))
		  $args['prefs']['tb_label_enable_shortcuts'] = get_input_value('tb_label_enable_shortcuts', RCUBE_INPUT_POST) ? true : false;

		if (!in_array('tb_label_style', $dont_override))  
			$args['prefs']['tb_label_style'] = get_input_value('tb_label_style', RCUBE_INPUT_POST);
	
		if (!in_array('tb_label_custom_labels', $dont_override)
			&& $this->rc->config->get('tb_label_modify_labels'))
		{
			$args['prefs']['tb_label_custom_labels'] = array(
			0 => $this->gettext('label0'),
			1 => get_input_value('tb_label_custom_labels1', RCUBE_INPUT_POST),
			2 => get_input_value('tb_label_custom_labels2', RCUBE_INPUT_POST),
			3 => get_input_value('tb_label_custom_labels3', RCUBE_INPUT_POST),
			4 => get_input_value('tb_label_custom_labels4', RCUBE_INPUT_POST),
			5 => get_input_value('tb_label_custom_labels5', RCUBE_INPUT_POST)
			);
		}
	
		return $args;
	}
	
	public function show_tb_label_contextmenu($args)
	{
		#$this->api->output->add_label('copymessage.copyingmessage');

		$li = html::tag('li',
		  array('class' => 'submenu'),
		  '<span>'.Q($this->gettext('tb_label_contextmenu_title')).'</span>' . $this->_gen_label_submenu($args, 'tb_label_ctxm_submenu'));
		$out .= html::tag('ul', array('id' => 'tb_label_ctxm_mainmenu'), $li);
		$this->api->output->add_footer(html::div(array('style' => 'display: none;'), $out));
	}
	
	private function _gen_label_submenu($args, $id)
	{
		$out = '';
		$custom_labels = $this->rc->config->get('tb_label_custom_labels');
		for ($i = 0; $i < 6; $i++)
		{
			$separator = ($i == 0)? ' separator_below' :'';
			$out .= '<li class="label'.$i.$separator.
			  ' ctxm_tb_label"><a href="#ctxm_tb_label" class="active" onclick="rcmail_ctxm_label_set('.$i.')"><span>'.
			  $i.' '.$custom_labels[$i].
			  '</span></a></li>';
		}
		$out = html::tag('ul', array('class' => 'popupmenu toolbarmenu folders', 'id' => $id), $out);
		return $out;
	}
	
	public function read_single_flags($args)
	{
		#write_log($this->name, print_r(($args['object']), true));
		if (!isset($args['object'])) {
				return;
		}
		
		if (is_array($args['object']->headers->flags))
		{
			$this->message_tb_labels = array();
			foreach ($args['object']->headers->flags as $flagname => $flagvalue)
			{
				$flag = is_numeric("$flagvalue")? $flagname:$flagvalue;// for compatibility with < 0.5.4
				$flag = strtolower($flag);
				if (preg_match('/^\$?label/', $flag))
				{
					$flag_no = preg_replace('/^\$?label/', '', $flag);
					#write_log($this->name, "Single message Flag: ".$flag." Flag_no:".$flag_no);
					$this->message_tb_labels[] = (int)$flag_no;
				}
			}
		}
		# -- no return value for this hook
	}
	
	/**
	*	Writes labelnumbers for single message display
	*	Coloring of Message header table happens via Javascript
	*/
	public function color_headers($p)
	{
		#write_log($this->name, print_r($p, true));
		# -- always write array, even when empty
		if($p['class'] == 'headers-table'){
			$p['content'] .= '<script type="text/javascript">
			var tb_labels_for_message = ['.join(',', $this->message_tb_labels).'];
			</script>';
		}
		return $p;
	}
	
	public function read_flags($args)
	{
		#write_log($this->name, print_r($args, true));
		// add color information for all messages
		// dont loop over all messages if we dont have any highlights or no msgs
		if (!isset($args['messages']) or !is_array($args['messages'])) {
				return $args;
		}

		// loop over all messages and add $LabelX info to the extra_flags
		foreach($args['messages'] as $message)
		{
			#write_log($this->name, print_r($message->flags, true));
			$message->list_flags['extra_flags']['tb_labels'] = array(); # always set extra_flags, needed for javascript later!
			if (is_array($message->flags))
			foreach ($message->flags as $flagname => $flagvalue)
			{
				$flag = is_numeric("$flagvalue")? $flagname:$flagvalue;// for compatibility with < 0.5.4
				$flag = strtolower($flag);
				if (preg_match('/^\$?label/', $flag))
				{
					$flag_no = preg_replace('/^\$?label/', '', $flag);
					#write_log($this->name, "Flag:".$flag." Flag_no:".$flag_no);
					$message->list_flags['extra_flags']['tb_labels'][] = (int)$flag_no;
				}
			}
		}
		return($args);
	}
	
	// set flags in IMAP server
	function set_flags()
	{
		#write_log($this->name, print_r($_GET, true));

		$imap = $this->rc->imap;
		$cbox = get_input_value('_cur', RCUBE_INPUT_GET);
		$mbox = get_input_value('_mbox', RCUBE_INPUT_GET);
		$toggle_label = get_input_value('_toggle_label', RCUBE_INPUT_GET);
		$flag_uids = get_input_value('_flag_uids', RCUBE_INPUT_GET);
		$flag_uids = explode(',', $flag_uids);
		$unflag_uids = get_input_value('_unflag_uids', RCUBE_INPUT_GET);
		$unflag_uids = explode(',', $unflag_uids);
		
		$imap->conn->flags = array_merge($imap->conn->flags, $this->add_tb_flags);
		
		#write_log($this->name, print_r($flag_uids, true));
		#write_log($this->name, print_r($unflag_uids, true));

		if (!is_array($unflag_uids)
			|| !is_array($flag_uids))
			return false;

		$imap->set_flag($flag_uids, $toggle_label, $mbox);
		$imap->set_flag($unflag_uids, "UN$toggle_label", $mbox);

		$this->api->output->send();
	}
	
	function tb_label_popup()
	{
	  $custom_labels = $this->rc->config->get('tb_label_custom_labels');
		$out = '<div id="tb_label_popup" class="popupmenu">
			<ul class="toolbarmenu">';
		for ($i = 0; $i < 6; $i++)
		{
			$separator = ($i == 0)? ' separator_below' :'';
			$out .= '<li class="label'.$i.$separator.'"><a href="#" class="active">'.$i.' '.$custom_labels[$i].'</a></li>';
		}
		$out .= '</ul>
		</div>';
		$this->rc->output->add_gui_object('tb_label_popup_obj', 'tb_label_popup');
    	$this->rc->output->add_footer($out);
	}
}

