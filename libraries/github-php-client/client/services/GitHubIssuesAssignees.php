<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubUser.php');
	

class GitHubIssuesAssignees extends GitHubService
{

	/**
	 * List assignees
	 * 
	 * @return array<GitHubUser>
	 */
	public function listAssignees($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/assignees", 'GET', $data, 200, 'GitHubUser', true);
	}
	
}

