<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubRepo.php');
	

class GitHubReposForks extends GitHubService
{

	/**
	 * List forks
	 * 
	 * @return array<GitHubRepo>
	 */
	public function listForks($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/forks", 'GET', $data, 200, 'GitHubRepo', true);
	}
	
}

