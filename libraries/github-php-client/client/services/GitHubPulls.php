<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/GitHubPullsComments.php');
require_once(__DIR__ . '/../objects/GitHubPull.php');
require_once(__DIR__ . '/../objects/GitHubFullPull.php');
require_once(__DIR__ . '/../objects/GitHubCommit.php');
require_once(__DIR__ . '/../objects/GitHubFile.php');
	

class GitHubPulls extends GitHubService
{

	/**
	 * @var GitHubPullsComments
	 */
	public $comments;
	
	
	/**
	 * Initialize sub services
	 */
	public function __construct(GitHubClient $client)
	{
		parent::__construct($client);
		
		$this->comments = new GitHubPullsComments($client);
	}
	
	/**
	 * List pull requests
	 * 
	 * @param $state string (Optional) - `open`, `closed` or `all` to filter by state. Default
	 * 	is `open`.
	 * @param $head string (Optional) - Filter pulls by head user and branch name in the format
	 * 	of: `user:ref-name`. Example: `github:new-script-format`.
	 * @param $base string (Optional) - Filter pulls by base branch name. Example:
	 * 	`gh-pages`.
	 * @param $sort string (Optional) - What to sort results by. Can be either `created`,
	 *  `updated`, popularity (comment count) or long-running (age, filtering by pulls updated
	 *  in the last month). Default: `created`
	 * @param $direction string (Optional) - The direction of the sort. Can be either `asc` or
	 *  `desc`. Default: `desc` when sort is created or sort is not specified, otherwise `asc`.
	 * @return array<GitHubPull>
	 */
	public function listPullRequests($owner, $repo, $state = null, $head = null, $base = null, $sort = null, $direction = null)
	{
		$data = array();
		if(!is_null($state))
			$data['state'] = $state;
		if(!is_null($head))
			$data['head'] = $head;
		if(!is_null($base))
			$data['base'] = $base;
		if(!is_null($sort))
			$data['sort'] = $sort;
		if(!is_null($direction))
			$data['direction'] = $direction;
		
		return $this->client->request("/repos/$owner/$repo/pulls", 'GET', $data, 200, 'GitHubPull', true);
	}
	
	/* This method is left for backward compatibility */
	public function linkRelations($owner, $repo, $state = null, $head = null, $base = null)
	{
		return $this->listPullRequests( $owner, $repo, $state, $head, $base );
	}

	/**
	 * Get a single pull request
	 * 
	 * @return GitHubFullPull
	 */
	public function getSinglePullRequest($owner, $repo, $number)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/pulls/$number", 'GET', $data, 200, 'GitHubFullPull');
	}
	
	/**
	 * updatePullRequest
	 * 
	 * @param $state string - State of this Pull Request. Valid values are either `open` or `closed`.
	 * @param $title string - The title of the pull request.
	 * @param $body string - The contents of the pull request.
	 * @return GitHubFullPull
	 */
	public function updatePullRequest($owner, $repo, $number, $state = null, $title = null, $body = null)
	{
		$data = array();
		if(!is_null($state))
			$data['state'] = $state;
		
		return $this->client->request("/repos/$owner/$repo/pulls/$number", 'PATCH', $data, 200, 'GitHubPull');
	}
	
	/* This method is left for backward compatibility */
	public function mergability($owner, $repo, $number, $state = null)
	{
		return $this->updatePullRequest($owner, $repo, $number, $state);
	}

	/**
	 * List commits on a pull request
	 * 
	 * @return array<GitHubCommit>
	 */
	public function listCommitsOnPullRequest($owner, $repo, $number)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/pulls/$number/commits", 'GET', $data, 200, 'GitHubCommit', true);
	}
	
	/**
	 * List pull requests files
	 * 
	 * @return array<GitHubFile>
	 */
	public function listPullRequestsFiles($owner, $repo, $number)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/pulls/$number/files", 'GET', $data, 200, 'GitHubFile', true);
	}
	
	/**
	 * Get if a pull request has been merged
	 *
	 * @return boolean
	 */
	public function isPullRequestMerged($owner, $repo, $number)
	{
		$merged = false;

		try
		{
			$data = array();
			$this->client->request("/repos/$owner/$repo/pulls/$number/merge", 'GET', $data, 204, '');
			$merged = true;
		}
		catch ( GitHubClientException $e )
		{
		}

		return $merged;
	}
	
	

	/**
	 * Create a pull request
	 *
	 * @param string $owner
	 * @param string $repo
	 * @param string $title (Required) The title of the pull request.
	 * @param string $head (Required) The name of the branch where your changes are implemented.
	 *              For cross-repository pull requests in the same network,
	 *              namespace head with a user like this: username:branch.
	 * @param string $base (Required) The name of the branch you want your changes pulled into.
	 *              This should be an existing branch on the current repository.
	 *              You cannot submit a pull request to one repository
	 *              that requests a merge to a base of another repository.
	 * @param string $body (Optional) The contents of the pull request.
	 * @return GitHubFullPull
	 * @throws GitHubClientException
	 */
	public function createPullRequest($owner, $repo, $title, $head, $base, $body = false)
	{

		$data = array(
			'title' => $title,
			'head'  => $head,
			'base'  => $base
		);
		if ($body) {
			$data['body'] = $body;
		}

		$data = json_encode($data);

		return $this->client->request("/repos/$owner/$repo/pulls", 'POST', $data, 201, 'GitHubFullPull');
	}
}

