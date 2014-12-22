<?php
class CustomAJAXChat extends AJAXChat {
	function startSession() {
		if(!session_id()) {
			echo "ERROR 100";
			exit;
		}
	}
	function logout($type=null) {
		if($this->getConfig('socketServerEnabled')) {
			$this->updateSocketAuthentication($this->getUserID());
		}
		if($this->isUserOnline()) {
			$this->chatViewLogout($type);
		}
		$this->login();
	}
	function initSession() {
		$this->startSession();
		if( !$this->isLoggedIn() ) {
			$this->login();
		}
		// Initialize the view:
		$this->initView();
		if($this->getView() == 'chat') {
			$this->initChatViewSession();
		} else if($this->getView() == 'logs') {
			$this->initLogsViewSession();
		}
		if(!$this->getRequestVar('ajax') && !headers_sent()) {
			// Set style cookie:
			$this->setStyle();
			// Set langCode cookie:
			$this->setLangCodeCookie();
		}
		$this->initCustomSession();
	}
	function login() {
		$userData = $this->getValidLoginUserData();
		if(!$userData) {
			$this->addInfoMessage('errorInvalidUser');
			return false;
		}
		// If the chat is closed, only the admin may login:
		if(!$this->isChatOpen() && $userData['userRole'] != AJAX_CHAT_ADMIN) {
			$this->addInfoMessage('errorChatClosed');
			return false;
		}
		// Check if userID or userName are already listed online:
		if($this->isUserOnline($userData['userID']) || $this->isUserNameInUse($userData['userName'])) {
			if($userData['userRole'] == AJAX_CHAT_USER || $userData['userRole'] == AJAX_CHAT_MODERATOR || $userData['userRole'] == AJAX_CHAT_ADMIN) {
				// Set the registered user inactive and remove the inactive users so the user can be logged in again:
				$this->setInactive($userData['userID'], $userData['userName']);
				$this->removeInactive();
			} else {
				$this->addInfoMessage('errorUserInUse');
				return false;
			}
		}
		// Check if user is banned:
		if($userData['userRole'] != AJAX_CHAT_ADMIN && $this->isUserBanned($userData['userName'], $userData['userID'], $_SERVER['REMOTE_ADDR'])) {
			$this->addInfoMessage('errorBanned');
			return false;
		}
		// Check if the max number of users is logged in (not affecting moderators or admins):
		if(!($userData['userRole'] == AJAX_CHAT_MODERATOR || $userData['userRole'] == AJAX_CHAT_ADMIN) && $this->isMaxUsersLoggedIn()) {
			$this->addInfoMessage('errorMaxUsersLoggedIn');
			return false;
		}
		// Log in:
		$this->setUserID($userData['userID']);
		$this->setUserName($userData['userName']);
		$this->setLoginUserName($userData['userName']);
		$this->setUserRole($userData['userRole']);
		$this->setLoggedIn(true);	
		$this->setLoginTimeStamp(time());
		// IP Security check variable:
		$this->setSessionIP($_SERVER['REMOTE_ADDR']);
		// The client authenticates to the socket server using a socketRegistrationID:
		if($this->getConfig('socketServerEnabled')) {
			$this->setSocketRegistrationID(
				md5(uniqid(rand(), true))
			);
		}
		// Add userID, userName and userRole to info messages:
		$this->addInfoMessage($this->getUserID(), 'userID');
		$this->addInfoMessage($this->getUserName(), 'userName');
		$this->addInfoMessage($this->getUserRole(), 'userRole');
		// Purge logs:
		if($this->getConfig('logsPurgeLogs')) {
			$this->purgeLogs();
		}
		return true;
	}
	function isLoggedIn() {
		if(!isset($_SESSION['authenticated_user_id'])){
			echo "ERROR 103";
			exit;
		}
		return (bool)$this->getSessionVar('LoggedIn');
	}
	
	// Returns an associative array containing userName, userID and userRole
	// Returns null if login is invalid
	function getValidLoginUserData() {
		if(!isset($_SESSION['authenticated_user_id'])){
			echo "ERROR 102";
			exit;
		}
		if(strpos($_SESSION['user_name'], '@')){
			$user_name = explode("@", $_SESSION['user_name']);
		}else{
			$user_name[0] = $_SESSION['user_name'];
		}
		$userData = array();
		$userData['userID'] = $_SESSION['authenticated_user_id'];
		$userData['userName'] = $user_name[0];
		$userData['userRole'] = AJAX_CHAT_USER;
		return $userData;
	}
	
	function chatViewLogin() {
		$this->setChannel($this->getValidRequestChannelID());
		$this->addToOnlineList();
		
		// Add channelID and channelName to info messages:
		$this->addInfoMessage($this->getChannel(), 'channelID');
		$this->addInfoMessage($this->getChannelName(), 'channelName');
	}
	
	function chatViewLogout($type) {
		$this->removeFromOnlineList();
	}
	function ipToStorageFormat($ip) {
		return $ip;
	}
	function removeInactive() {
		$sql = 'SELECT userID, userName, channel FROM '.$this->getDataBaseTable('online').' WHERE NOW() > DATE_ADD(dateTime, interval '.$this->getConfig('inactiveTimeout').' MINUTE);';
		// Create a new SQL query:
		$result = $this->db->sqlQuery($sql);
		
		// Stop if an error occurs:
		if($result->error()) {
			echo $result->getError();
			die();
		}
		if($result->numRows() > 0) {
			$condition = '';
			while($row = $result->fetch()) {
				if(!empty($condition))
					$condition .= ' OR ';
				// Add userID to condition for removal:
				$condition .= 'userID='.$this->db->makeSafe($row['userID']);

				// Update the socket server authentication for the kicked user:
				if($this->getConfig('socketServerEnabled')) {
					$this->updateSocketAuthentication($row['userID']);
				}
				$this->removeUserFromOnlineUsersData($row['userID']);
			}
			$result->free();
			$sql = 'DELETE FROM '.$this->getDataBaseTable('online').' WHERE	'.$condition.';';
			// Create a new SQL query:
			$result = $this->db->sqlQuery($sql);
			// Stop if an error occurs:
			if($result->error()) {
				echo $result->getError();
				die();
			}
		}
	}
	/////////////////////////////////
	/////////////////////////////////
	
	// Store the channels the current user has access to
	// Make sure channel names don't contain any whitespace
	function &getChannels() {
		if($this->_channels === null) {
			$this->_channels = array();
			
			$customUsers = $this->getCustomUsers();
			
			// Get the channels, the user has access to:
			if($this->getUserRole() == AJAX_CHAT_GUEST) {
				$validChannels = $customUsers[0]['channels'];
			} else {
				$validChannels = $customUsers[$this->getUserID()]['channels'];
			}

			// Add the valid channels to the channel list (the defaultChannelID is always valid):
			foreach($this->getAllChannels() as $key=>$value) {
				if ($value == $this->getConfig('defaultChannelID')) {
					$this->_channels[$key] = $value;
					continue;
				}
				// Check if we have to limit the available channels:
				if($this->getConfig('limitChannelList') && !in_array($value, $this->getConfig('limitChannelList'))) {
					continue;
				}

				if($validChannels && in_array($value, $validChannels)) {
					$this->_channels[$key] = $value;
				}
			}
		}
		
		return $this->_channels;
	}

	// Store all existing channels
	// Make sure channel names don't contain any whitespace
	function &getAllChannels() {
		if($this->_allChannels === null) {
			// Get all existing channels:
			$customChannels = $this->getCustomChannels();
			
			$defaultChannelFound = false;
			
			foreach($customChannels as $name=>$id) {
				$this->_allChannels[$this->trimChannelName($name)] = $id;
				if($id == $this->getConfig('defaultChannelID')) {
					$defaultChannelFound = true;
				}
			}
			
			if(!$defaultChannelFound) {
				// Add the default channel as first array element to the channel list
				// First remove it in case it appeard under a different ID
				unset($this->_allChannels[$this->getConfig('defaultChannelName')]);
				$this->_allChannels = array_merge(
					array(
						$this->trimChannelName($this->getConfig('defaultChannelName'))=>$this->getConfig('defaultChannelID')
					),
					$this->_allChannels
				);
			}
		}
		return $this->_allChannels;
	}

	function &getCustomUsers() {
		// List containing the registered chat users:
		$users = null;
		require(AJAX_CHAT_PATH.'lib/data/users.php');
		return $users;
	}
	
	function getCustomChannels() {
		// List containing the custom channels:
		$channels = null;
		require(AJAX_CHAT_PATH.'lib/data/channels.php');
		// Channel array structure should be:
		// ChannelName => ChannelID
		return array_flip($channels);
	}

}