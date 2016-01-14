<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubStatus.php');
	

class GitHubReposStatuses extends GitHubService
{

	/**
	 * Create status
	 *
	 * @param $sha string (Required)
	 * @param $state string (Required) - The state of the status. Can be one of pending, success, error, or failure.
	 * @param $target_url string - The target URL to associate with this status. This URL will be linked from the GitHub UI to allow users to easily see the ‘source’ of the Status.
	 *                              For example, if your Continuous Integration system is posting build status, you would want to provide the deep link for the build output for this specific SHA:
	 *                              http://ci.example.com/user/repo/build/sha.
	 * @param $description string - A short description of the status.
	 * @param $context string - A string label to differentiate this status from the status of other systems. Default: "default"
	 * @return GitHubStatus
	 */
	public function createStatus($owner, $repo, $sha, $state, $target_url = null, $description = null, $context  = null)
	{
		$data = array();
		$data['state'] = $state;
		if(!is_null($target_url))
			$data['target_url'] = $target_url;
		if(!is_null($description))
			$data['description'] = $description;
		if(!is_null($context))
			$data['context'] = $context;

		$data = json_encode($data);

		return $this->client->request("/repos/$owner/$repo/statuses/$sha", 'POST', $data, 201, 'GitHubStatus', false);
	}

	/**
	 * List Statuses for a specific Ref
	 * 
	 * @param $ref string (Required) - Ref to list the statuses from. It can be a SHA, a branch name, or a tag name.
	 * @return array<GitHubStatus>
	 */
	public function listStatusesForSpecificRef($owner, $repo, $ref)
	{
		$data = array();
		$data['ref'] = $ref;
		
		return $this->client->request("/repos/$owner/$repo/commits/$ref/statuses", 'GET', $data, 200, 'GitHubStatus', true);
	}

	/**
	 * Get the combined Status for a specific Ref
	 *
	 * @param $ref string (Required) - Ref to fetch the status for. It can be a SHA, a branch name, or a tag name.
	 * @return GitHubStatus
	 */
	public function getCombinedStatus($owner, $repo, $ref)
	{
		$data = array();

		return $this->client->request("/repos/$owner/$repo/commits/$ref/status", 'GET', $data, 200, 'GitHubStatus', false);
	}
	
}

