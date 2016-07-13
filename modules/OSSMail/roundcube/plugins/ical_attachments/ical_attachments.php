<?php

/**
 * Detect VCard attachments and show a button to add them to address book
 *
 * @version @package_version@
 * @license GNU GPLv3+
 * @author Thomas Bruederli, Aleksander Machniak
 */
class ical_attachments extends rcube_plugin
{

	public $task = 'mail';
	private $message;
	private $ics_parts = array();
	private $ics_bodies = array();

	function init()
	{
		$rcmail = rcmail::get_instance();
		if ($rcmail->action == 'show' || $rcmail->action == 'preview') {
			$this->add_hook('message_load', array($this, 'message_load'));
			$this->add_hook('template_object_messageattachments', array($this, 'html_output'));
		}
	}

	/**
	 * Check message bodies and attachments for ical
	 */
	function message_load($p)
	{
		$this->message = $p['object'];

		// handle attachments vcard attachments
		foreach ((array) $this->message->attachments as $attachment) {
			if ($this->is_ics($attachment)) {
				$this->ics_parts[] = array('part' => $attachment->mime_id, 'uid' => $this->message->uid);
			}
		}
		// the same with message bodies
		foreach ((array) $this->message->parts as $part) {
			if ($this->is_ics($part)) {
				$this->ics_parts[] = array('part' => $attachment->mime_id, 'uid' => $this->message->uid);
				$this->ics_bodies[] = $part->mime_id;
			}
		}
		if ($this->ics_parts)
			$this->add_texts('localization');
	}

	/**
	 * This callback function adds a box below the message content
	 * if there is a vcard attachment available
	 */
	function html_output($p)
	{
		$attach_script = false;

		foreach ($this->ics_parts as $part) {
			$icscontent = $this->message->get_part_content($part['part'], null, true);
			$file_name = $part['uid'];
			$file = '../../../cache/import/' . $file_name . '.ics';
			file_put_contents($file, $icscontent);

			// add box below message body
			$p['content'] .= html::p(array('class' => 'icalattachments'), html::a(array(
						'href' => "javascript:void",
						'onclick' => "return rcmail.command('yetiforce.importICS',$file_name,this,event)",
						'title' => $this->gettext('addicalinvitemsg'),
						), html::span(null, rcube::Q($this->gettext('addicalinvitemsg')))
					)
			);
			$attach_script = true;
		}
		if ($attach_script) {
			$this->include_stylesheet($this->local_skin_path() . '/style.css');
		}
		return $p;
	}

	function is_ics($part)
	{
		//return ( $part->mimetype == 'application/ics' || $part->mimetype == 'text/calendar' );
		return ( $part->mimetype == 'application/ics' );
	}
}
