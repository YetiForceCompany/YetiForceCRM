<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubFeeds.php');
	

class GitHubActivityFeeds extends GitHubService
{

	/**
	 * List Feeds
	 * 
	 * @return GitHubFeeds
	 */
	public function listFeeds()
	{
		$data = array();
		
		return $this->client->request("/feeds", 'GET', $data, 200, 'GitHubFeeds');
	}
	
}

