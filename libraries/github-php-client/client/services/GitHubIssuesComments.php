<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubPullComment.php');
require_once(__DIR__ . '/../objects/GitHubIssueComment.php');
	

class GitHubIssuesComments extends GitHubService
{

	/**
	 * List comments on an issue
	 * 
	 * @param $sort String (Optional) `created` or `updated`
	 * @param $direction String (Optional) `asc` or `desc`. Ignored without `sort` parameter.
	 * @param $since String (Optional) of a timestamp in ISO 8601 format: YYYY-MM-DDTHH:MM:SSZ
	 * @return array<GitHubPullComment>
	 */
	public function listCommentsOnAnIssue($owner, $repo, $number, $sort = null, $direction = null, $since = null)
	{
		$data = array();
		if(!is_null($sort))
			$data['sort'] = $sort;
		if(!is_null($direction))
			$data['direction'] = $direction;
		if(!is_null($since))
			$data['since'] = $since;
		
		return $this->client->request("/repos/$owner/$repo/issues/$number/comments", 'GET', $data, 200, 'GitHubPullComment', true);
	}
	
	/**
	 * List comments in a repository
	 * 
	 * @param $sort String (Optional) `created` or `updated`
	 * @param $direction String (Optional) `asc` or `desc`. Ignored without `sort` parameter.
	 * @param $since String (Optional) of a timestamp in ISO 8601 format: YYYY-MM-DDTHH:MM:SSZ
	 * @return array<GitHubPullComment>
	 */
	public function listCommentsOnRepository($owner, $repo, $number, $sort = null, $direction = null, $since = null)
	{
		$data = array();
		if(!is_null($sort))
			$data['sort'] = $sort;
		if(!is_null($direction))
			$data['direction'] = $direction;
		if(!is_null($since))
			$data['since'] = $since;
		
		return $this->client->request("/repos/$owner/$repo/issues/comments", 'GET', $data, 200, 'GitHubPullComment', true);
	}
	
	/**
	 * Get a single comment
	 * 
	 * @return GitHubIssueComment
	 */
	public function getSingleComment($owner, $repo, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/issues/comments/$id", 'GET', $data, 200, 'GitHubIssueComment');
	}
	
	/**
	 * Create a comment
	 * 
	 * @return GitHubIssueComment
	 */
	public function createComment($owner, $repo, $issue, $body)
	{
		$data = array();
		$data['body'] = $body;
		
		return $this->client->request("/repos/$owner/$repo/issues/$issue/comments", 'POST', json_encode($data), 201, 'GitHubIssueComment');
	}
	
	/**
	 * Edit a comment
	 *
	 * @return GitHubIssueComment
	 */
	public function editComment($owner, $repo, $id, $body)
	{
		$data = array();
		$data['body'] = $body;

		return $this->client->request("/repos/$owner/$repo/issues/comments/$id", 'PATCH', json_encode($data), 200, 'GitHubIssueComment');
	}

	/**
	 * Delete a comment
	 * 
	 */
	public function deleteComment($owner, $repo, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/issues/comments/$id", 'DELETE', $data, 204, '');
	}
	
}

