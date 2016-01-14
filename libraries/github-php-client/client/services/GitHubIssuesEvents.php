<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubFullIssueEvent.php');
	

class GitHubIssuesEvents extends GitHubService
{

	/**
	 * Attributes
	 * 
	 * @return GitHubFullIssueEvent
	 */
	public function attributes($owner, $repo, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/issues/events/$id", 'GET', $data, 200, 'GitHubFullIssueEvent');
	}
	
}

