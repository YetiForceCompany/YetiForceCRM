<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubDownload.php');
	

class GitHubReposDownloads extends GitHubService
{

	/**
	 * List downloads for a repository
	 * 
	 * @return array<GitHubDownload>
	 */
	public function listDownloadsForRepository($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/downloads", 'GET', $data, 200, 'GitHubDownload', true);
	}
	
	/**
	 * Get a single download
	 * 
	 * @return GitHubDownload
	 */
	public function getSingleDownload($owner, $repo, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/downloads/$id", 'GET', $data, 200, 'GitHubDownload');
	}
	
}

