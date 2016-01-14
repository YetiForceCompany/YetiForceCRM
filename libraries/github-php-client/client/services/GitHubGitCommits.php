<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubGitCommit.php');
	

class GitHubGitCommits extends GitHubService
{

	/**
	 * Get a Commit
	 * 
	 * @return GitHubGitCommit
	 */
	public function getCommit($owner, $repo, $sha)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/git/commits/$sha", 'GET', $data, 200, 'GitHubGitCommit');
	}
	
}

