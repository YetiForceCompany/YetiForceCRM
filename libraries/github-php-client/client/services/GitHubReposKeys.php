<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubPublicKey.php');
	

class GitHubReposKeys extends GitHubService
{

	/**
	 * List
	 * 
	 * @return array<GitHubPublicKey>
	 */
	public function listReposKeys($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/keys", 'GET', $data, 200, 'GitHubPublicKey', true);
	}
	
	/**
	 * Get
	 * 
	 * @return GitHubPublicKey
	 */
	public function get($owner, $repo, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/keys/$id", 'GET', $data, 200, 'GitHubPublicKey');
	}
	
	/**
	 * Create
	 * 
	 * @return GitHubPublicKey
	 */
	public function create($owner, $repo, $id)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/keys/$id", 'PATCH', $data, 200, 'GitHubPublicKey');
	}
	
}

