<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubCommitComment.php');
	

class GitHubReposComments extends GitHubService
{

	/**
	 * List commit comments for a repository
	 * 
	 * @return array<GitHubCommitComment>
	 */
	public function listCommitCommentsForRepository($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/comments", 'GET', $data, 200, 'GitHubCommitComment', true);
	}
	
	/**
	 * List comments for a single commit
	 * 
	 * @return array<GitHubCommitComment>
	 */
	public function listCommentsForSingleCommit($owner, $repo, $sha)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/commits/$sha/comments", 'GET', $data, 200, 'GitHubCommitComment', true);
	}
	
	/**
	 * Create a commit comment
	 * 
	 * @return GitHubCommitComment
	 */
	public function createCommitComment($owner, $repo, $sha, $body, $path = null, $position = null)
	{
		$data = array(
			'body' => $body,
		);
		if (!is_null($path))
			$data['path'] = $path;
		if (!is_null($position))
			$data['position'] = $position;

		return $this->client->request("/repos/$owner/$repo/commits/$sha/comments", 'POST', json_encode($data), 201, 'GitHubCommitComment');
	}

	/**
	 * Get a single commit comment
	 *
	 * @return GitHubCommitComment
	 */
	public function getSingleCommitComment($owner, $repo, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/comments/$id", 'GET', $data, 200, 'GitHubCommitComment');
	}
	
	/**
	 * Update a commit comment
	 * 
	 * @return GitHubCommitComment
	 */
	public function updateCommitComment($owner, $repo, $id, $body)
	{
		$data = array(
			'body' => $body,
		);

		return $this->client->request("/repos/$owner/$repo/comments/$id", 'PATCH', json_encode($data), 200, 'GitHubCommitComment');
	}

	/**
	 * Delete a commit comment
	 *
	 */
	public function deleteCommitComment($owner, $repo, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/comments/$id", 'DELETE', $data, 204, '');
	}
	
}

