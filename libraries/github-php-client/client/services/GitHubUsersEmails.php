<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');

	

class GitHubUsersEmails extends GitHubService
{

	/**
	 * List email addresses for a user
	 * 
	 */
	public function listEmailAddressesForUser()
	{
		$data = array();
		
		return $this->client->request("/user/emails", 'DELETE', $data, 204, '');
	}
	
}

