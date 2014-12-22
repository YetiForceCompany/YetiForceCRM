<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Class to handle HTML templates
class AJAXChatTemplate {

	var $ajaxChat;
	var $_regExpTemplateTags;
	var $_templateFile;
	var $_contentType;
	var $_content;
	var $_parsedContent;

	// Constructor:
	function AJAXChatTemplate(&$ajaxChat, $templateFile, $contentType=null) {
		$this->ajaxChat = $ajaxChat;
		$this->_regExpTemplateTags = '/\[(\w+?)(?:(?:\/)|(?:\](.+?)\[\/\1))\]/s';		
		$this->_templateFile = $templateFile;
		$this->_contentType = $contentType;
	}

	function getParsedContent() {
		if(!$this->_parsedContent) {
			$this->parseContent();
		}
		return $this->_parsedContent;
	}

	function getContent() {
		if(!$this->_content) {
			$this->_content = AJAXChatFileSystem::getFileContents($this->_templateFile);
		}
		return $this->_content;
	}

	function parseContent() {
		$this->_parsedContent = $this->getContent();
		
		// Remove the XML declaration if the content-type is not xml:		
		if($this->_contentType && (strpos($this->_contentType,'xml') === false)) {
			$doctypeStart = strpos($this->_parsedContent, '<!DOCTYPE ');
			if($doctypeStart !== false) {
				// Removing the XML declaration (in front of the document type) prevents IE<7 to go into "Quirks mode":
				$this->_parsedContent = substr($this->_parsedContent, $doctypeStart);	
			}		
		}

		// Replace template tags ([TAG/] and [TAG]content[/TAG]) and return parsed template content:
		$this->_parsedContent = preg_replace_callback($this->_regExpTemplateTags, array($this, 'replaceTemplateTags'), $this->_parsedContent);
	}

	function replaceTemplateTags($tagData) {
		switch($tagData[1]) {
			case 'AJAX_CHAT_URL':
				return $this->ajaxChat->htmlEncode($this->ajaxChat->getChatURL());

			case 'LANG':
				return $this->ajaxChat->htmlEncode($this->ajaxChat->getLang((isset($tagData[2]) ? $tagData[2] : null)));				
			case 'LANG_CODE':
				return $this->ajaxChat->getLangCode();

			case 'BASE_DIRECTION':
				return $this->getBaseDirectionAttribute();

			case 'CONTENT_ENCODING':
				return $this->ajaxChat->getConfig('contentEncoding');
					
			case 'CONTENT_TYPE':
				return $this->_contentType;
		
			case 'LOGIN_URL':
				return ($this->ajaxChat->getRequestVar('view') == 'logs') ? './?view=logs' : './';
				
			case 'USER_NAME_MAX_LENGTH':
				return $this->ajaxChat->getConfig('userNameMaxLength');
			case 'MESSAGE_TEXT_MAX_LENGTH':
				return $this->ajaxChat->getConfig('messageTextMaxLength');

			case 'LOGIN_CHANNEL_ID':
				return $this->ajaxChat->getValidRequestChannelID();
				
			case 'SESSION_NAME':
				return $this->ajaxChat->getConfig('sessionName');
				
			case 'COOKIE_EXPIRATION':
				return $this->ajaxChat->getConfig('sessionCookieLifeTime');
			case 'COOKIE_PATH':
				return $this->ajaxChat->getConfig('sessionCookiePath');
			case 'COOKIE_DOMAIN':
				return $this->ajaxChat->getConfig('sessionCookieDomain');
			case 'COOKIE_SECURE':
				return $this->ajaxChat->getConfig('sessionCookieSecure');
				
			case 'CHAT_BOT_NAME':
				return rawurlencode($this->ajaxChat->getConfig('chatBotName'));
			case 'CHAT_BOT_ID':
				return $this->ajaxChat->getConfig('chatBotID');

			case 'ALLOW_USER_MESSAGE_DELETE':
				if($this->ajaxChat->getConfig('allowUserMessageDelete'))
					return 1;
				else
					return 0;

			case 'INACTIVE_TIMEOUT':
				return $this->ajaxChat->getConfig('inactiveTimeout');

			case 'PRIVATE_CHANNEL_DIFF':
				return $this->ajaxChat->getConfig('privateChannelDiff');
			case 'PRIVATE_MESSAGE_DIFF':
				return $this->ajaxChat->getConfig('privateMessageDiff');

			case 'SHOW_CHANNEL_MESSAGES':
				if($this->ajaxChat->getConfig('showChannelMessages'))
					return 1;
				else
					return 0;

			case 'SOCKET_SERVER_ENABLED':
				if($this->ajaxChat->getConfig('socketServerEnabled'))
					return 1;
				else
					return 0;

			case 'SOCKET_SERVER_HOST':
				if($this->ajaxChat->getConfig('socketServerHost')) {
					$socketServerHost = $this->ajaxChat->getConfig('socketServerHost');
				} else {
					$socketServerHost = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
				}
				return rawurlencode($socketServerHost);

			case 'SOCKET_SERVER_PORT':
				return $this->ajaxChat->getConfig('socketServerPort');

			case 'SOCKET_SERVER_CHAT_ID':
				return $this->ajaxChat->getConfig('socketServerChatID');

			case 'STYLE_SHEETS':
				return $this->getStyleSheetLinkTags();
				
			case 'CHANNEL_OPTIONS':
				return $this->getChannelOptionTags();
			case 'STYLE_OPTIONS':
				return $this->getStyleOptionTags();
			case 'LANGUAGE_OPTIONS':
				return $this->getLanguageOptionTags();
			
			case 'ERROR_MESSAGES':
				return $this->getErrorMessageTags();

			case 'LOGS_CHANNEL_OPTIONS':
				return $this->getLogsChannelOptionTags();
			case 'LOGS_YEAR_OPTIONS':
				return $this->getLogsYearOptionTags();
			case 'LOGS_MONTH_OPTIONS':
				return $this->getLogsMonthOptionTags();
			case 'LOGS_DAY_OPTIONS':
				return $this->getLogsDayOptionTags();
			case 'LOGS_HOUR_OPTIONS':
				return $this->getLogsHourOptionTags();
			case 'CLASS_WRITEABLE':
				$userdata = $this->ajaxChat->getValidLoginUserData();
				$guestwrite = $this->ajaxChat->getConfig('allowGuestWrite');
				if ($userdata['userRole'] === AJAX_CHAT_GUEST && $guestwrite === false)
					return 'write_forbidden';
				else
					return 'write_allowed';
			
			default:
				return $this->ajaxChat->replaceCustomTemplateTags($tagData[1], (isset($tagData[2]) ? $tagData[2] : null));
		}
	}

	// Function to display alternating table row colors:
	function alternateRow($rowOdd='rowOdd', $rowEven='rowEven') {
		static $i;
		$i += 1;
		if($i % 2 == 0) {
			return $rowEven;
		} else {
			return $rowOdd;
		}
	}

	function getBaseDirectionAttribute() {
		$langCodeParts = explode('-', $this->ajaxChat->getLangCode());
		switch($langCodeParts[0]) {
			case 'ar':
			case 'fa':
			case 'he':
				return 'rtl';
			default:
				return 'ltr';
		}
	}

	function getStyleSheetLinkTags() {
		$styleSheets = '';
		foreach($this->ajaxChat->getConfig('styleAvailable') as $style) {
			$alternate = ($style == $this->ajaxChat->getConfig('styleDefault')) ? '' : 'alternate ';
			$styleSheets .= '<link rel="'.$alternate.'stylesheet" type="text/css" href="css/'.rawurlencode($style).'.css" title="'.$this->ajaxChat->htmlEncode($style).'"/>';
		}
		return $styleSheets;
	}

	function getChannelOptionTags() {
		$channelOptions = '';
		$channelSelected = false;
		foreach($this->ajaxChat->getChannels() as $name=>$id) {
			if($this->ajaxChat->isLoggedIn() && $this->ajaxChat->getChannel()) {
				$selected = ($id == $this->ajaxChat->getChannel()) ? ' selected="selected"' : '';
			} else {
				$selected = ($id == $this->ajaxChat->getConfig('defaultChannelID')) ? ' selected="selected"' : '';
			}
			if($selected) {
				$channelSelected = true;
			}
			$channelOptions .= '<option value="'.$this->ajaxChat->htmlEncode($name).'"'.$selected.'>'.$this->ajaxChat->htmlEncode($name).'</option>';
		}
		if($this->ajaxChat->isLoggedIn() && $this->ajaxChat->isAllowedToCreatePrivateChannel()) {
			// Add the private channel of the user to the options list:
			if(!$channelSelected && $this->ajaxChat->getPrivateChannelID() == $this->ajaxChat->getChannel()) {
				$selected = ' selected="selected"';
				$channelSelected = true;
			} else {
				$selected = '';
			}
			$privateChannelName = $this->ajaxChat->getPrivateChannelName();
			$channelOptions .= '<option value="'.$this->ajaxChat->htmlEncode($privateChannelName).'"'.$selected.'>'.$this->ajaxChat->htmlEncode($privateChannelName).'</option>';
		}
		// If current channel is not in the list, try to retrieve the channelName:
		if(!$channelSelected) {
			$channelName = $this->ajaxChat->getChannelName();
			if($channelName !== null) {
				$channelOptions .= '<option value="'.$this->ajaxChat->htmlEncode($channelName).'" selected="selected">'.$this->ajaxChat->htmlEncode($channelName).'</option>';
			} else {
				// Show an empty selection:
				$channelOptions .= '<option value="" selected="selected">---</option>';
			}
		}
		return $channelOptions;
	}

	function getStyleOptionTags() {
		$styleOptions = '';
		foreach($this->ajaxChat->getConfig('styleAvailable') as $style) {
			$selected = ($style == $this->ajaxChat->getConfig('styleDefault')) ? ' selected="selected"' : '';
			$styleOptions .= '<option value="'.$this->ajaxChat->htmlEncode($style).'"'.$selected.'>'.$this->ajaxChat->htmlEncode($style).'</option>';
		}
		return $styleOptions;
	}

	function getLanguageOptionTags() {
		$languageOptions = '';
		$languageNames = $this->ajaxChat->getConfig('langNames');
		foreach($this->ajaxChat->getConfig('langAvailable') as $langCode) {
			$selected = ($langCode == $this->ajaxChat->getLangCode()) ? ' selected="selected"' : '';
			$languageOptions .= '<option value="'.$this->ajaxChat->htmlEncode($langCode).'"'.$selected.'>'.$languageNames[$langCode].'</option>';
		}
		return $languageOptions;
	}

	function getErrorMessageTags() {
		$errorMessages = '';
		foreach($this->ajaxChat->getInfoMessages('error') as $error) {
			$errorMessages .= '<div>'.$this->ajaxChat->htmlEncode($this->ajaxChat->getLang($error)).'</div>';
		}
		return $errorMessages;
	}

	function getLogsChannelOptionTags() {
		$channelOptions = '';
		$channelOptions .= '<option value="-3">------</option>';
		foreach($this->ajaxChat->getChannels() as $key=>$value) {
			if($this->ajaxChat->getUserRole() != AJAX_CHAT_ADMIN && $this->ajaxChat->getConfig('logsUserAccessChannelList') && !in_array($value, $this->ajaxChat->getConfig('logsUserAccessChannelList'))) {
				continue;
			}
			$channelOptions .= '<option value="'.$value.'">'.$this->ajaxChat->htmlEncode($key).'</option>';
		}
		$channelOptions .= '<option value="-1">'.$this->ajaxChat->htmlEncode($this->ajaxChat->getLang('logsPrivateChannels')).'</option>';
		$channelOptions .= '<option value="-2">'.$this->ajaxChat->htmlEncode($this->ajaxChat->getLang('logsPrivateMessages')).'</option>';
		return $channelOptions;
	}

	function getLogsYearOptionTags() {
		$yearOptions = '';
		$yearOptions .= '<option value="-1">----</option>';
		for($year=date('Y'); $year>=$this->ajaxChat->getConfig('logsFirstYear'); $year--) {
			$yearOptions .= '<option value="'.$year.'">'.$year.'</option>';
		}
		return $yearOptions;
	}
	
	function getLogsMonthOptionTags() {
		$monthOptions = '';
		$monthOptions .= '<option value="-1">--</option>';
		for($month=1; $month<=12; $month++) {
			$monthOptions .= '<option value="'.$month.'">'.sprintf("%02d", $month).'</option>';
		}
		return $monthOptions;
	}
	
	function getLogsDayOptionTags() {
		$dayOptions = '';
		$dayOptions .= '<option value="-1">--</option>';
		for($day=1; $day<=31; $day++) {
			$dayOptions .= '<option value="'.$day.'">'.sprintf("%02d", $day).'</option>';
		}
		return $dayOptions;
	}
	
	function getLogsHourOptionTags() {
		$hourOptions = '';
		$hourOptions .= '<option value="-1">-----</option>';
		for($hour=0; $hour<=23; $hour++) {
			$hourOptions .= '<option value="'.$hour.'">'.sprintf("%02d", $hour).':00</option>';
		}
		return $hourOptions;
	}

}
?>
