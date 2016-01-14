<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubUser.php');
	
class GitHubReposCollaborators extends GitHubService
{

	/**
	 * List
	 * 
	 * @return array<GitHubUser>
	 */
	public function listReposCollaborators($owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/collaborators", 'GET', $data, 200, 'GitHubUser', true);
	}
	
	/**
	 * PUT
	 * 
	 */
	public function put($owner, $repo, $user)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/collaborators/$user", 'PUT', $data, 204, '');
	}
	
	/**
	 * Get
	 * 
	 */
	public function get($owner, $repo, $user)
	{
		$data = array();
		return $this->client->request("/repos/$owner/$repo/collaborators/$user", 'GET', $data, 204, '');
	}

	/**
	 * DELETE
	 * 
	 */
	public function delete($owner, $repo, $user)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/collaborators/$user", 'DELETE', $data, 204, '');
	}
}