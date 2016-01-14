<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubCommit.php');
require_once(__DIR__ . '/../objects/GitHubFullCommit.php');
require_once(__DIR__ . '/../objects/GitHubCommitComparison.php');
	

class GitHubReposCommits extends GitHubService
{

	/**
	 * List commits on a repository
	 * 
	 * @param $sha string (Optional) - Sha or branch to start listing commits from.
	 * @param $path string (Optional) - Only commits containing this file path
	 * 	will be returned.
	 * @param $author string (Optional) - GitHub login, name, or email by which to filter by
	 * 	commit author
	 * @param $since ISO 8601 Date (Optional) - Only commits after this date will be returned
	 * @param $until ISO 8601 Date (Optional) - Only commits before this date will be returned
	 * @return array<GitHubCommit>
	 */
	public function listCommitsOnRepository($owner, $repo, $sha = null, $path = null, $author = null, $since = null, $until = null)
	{
		$data = array();
		if(!is_null($sha))
			$data['sha'] = $sha;
		if(!is_null($path))
			$data['path'] = $path;
		if(!is_null($author))
			$data['author'] = $author;
		if(!is_null($since))
			$data['since'] = $since;
		if(!is_null($until))
			$data['until'] = $until;
		
		return $this->client->request("/repos/$owner/$repo/commits", 'GET', $data, 200, 'GitHubCommit', true);
	}
	
	/**
	 * Get a single commit
	 * 
	 * @return GitHubFullCommit
	 */
	public function getSingleCommit($owner, $repo, $sha)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/commits/$sha", 'GET', $data, 200, 'GitHubFullCommit', true);
	}
	
	/**
	 * Compare two commits
	 * 
	 * @return GitHubCommitComparison
	 */
	public function compareTwoCommits($owner, $repo, $base, $head)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/compare/$base...$head", 'GET', $data, 200, 'GitHubCommitComparison');
	}
	
}

