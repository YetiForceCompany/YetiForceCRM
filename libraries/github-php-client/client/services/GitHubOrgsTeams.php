<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubTeam.php');
require_once(__DIR__ . '/../objects/GitHubFullTeam.php');
require_once(__DIR__ . '/../objects/GitHubUser.php');
require_once(__DIR__ . '/../objects/GitHubRepo.php');
	

class GitHubOrgsTeams extends GitHubService
{

	/**
	 * List teams
	 * 
	 * @return array<GitHubTeam>
	 */
	public function listTeams($org)
	{
		$data = array();
		
		return $this->client->request("/orgs/$org/teams", 'GET', $data, 200, 'GitHubTeam', true);
	}
	
	/**
	 * Get team
	 * 
	 * @return array<GitHubFullTeam>
	 */
	public function getTeam($id)
	{
		$data = array();
		
		return $this->client->request("/teams/$id", 'GET', $data, 200, 'GitHubFullTeam', false);
	}
	
	/**
	 * Create team
	 * 
	 * @return array<GitHubFullTeam>
	 */
	public function createTeam($org, $name, $repo_names = null, $permission = null)
	{
		$data = array();
		$data['name'] = $name;
		
		if(!is_null($repo_names))
			$data['repo_names'] = $repo_names;

		if(!is_null($permission))
			$data['permission'] = $permission;

		$data = json_encode($data);
		
		return $this->client->request("/orgs/$org/teams", 'POST', $data, 201, 'GitHubFullTeam');
	}
	
	/**
	 * Delete team
	 * 
	 */
	public function deleteTeam($id)
	{
		$data = array();
		
		return $this->client->request("/teams/$id", 'DELETE', $data, 204, '');
	}
	
	/**
	 * List team members
	 * 
	 * @return array<GitHubUser>
	 */
	public function listTeamMembers($id)
	{
		$data = array();
		
		return $this->client->request("/teams/$id/members", 'GET', $data, 200, 'GitHubUser', true);
	}
	
	/**
	 * Get team member
	 * 
	 */
	public function getTeamMember($id, $user)
	{
		$data = array();
		
		return $this->client->request("/teams/$id/members/$user", 'GET', $data, 204, '');
	}

	/**
	 * Add member to team
	 */
	public function addTeamMember($id, $user) {
		$data = array();
		return $this->client->request("/teams/$id/members/$user", 'PUT', $data, 204, '');
	}

	/**
	 * Add repo to team
	 */
	public function addTeamRepo($id, $org, $repo) {
		$data = array();
		return $this->client->request("/teams/$id/repos/$org/$repo", 'PUT', $data, 204, '');
	}
	
	/**
	 * Remove team member
	 * 
	 */
	public function removeTeamMember($id, $user)
	{
		$data = array();
		
		return $this->client->request("/teams/$id/members/$user", 'DELETE', $data, 204, '');
	}
	
	/**
	 * List team repos
	 * 
	 * @return array<GitHubRepo>
	 */
	public function listTeamRepos($id)
	{
		$data = array();
		
		return $this->client->request("/teams/$id/repos", 'GET', $data, 200, 'GitHubRepo', true);
	}
	
	/**
	 * Get team repo
	 * 
	 */
	public function getTeamRepo($id, $org, $repo)
	{
		$data = array();
		
		return $this->client->request("/teams/$id/repos/$org/$repo", 'PUT', $data, 204, '');
	}
	
	/**
	 * Remove team repo
	 * 
	 */
	public function removeTeamRepo($id, $owner, $repo)
	{
		$data = array();
		
		return $this->client->request("/teams/$id/repos/$owner/$repo", 'DELETE', $data, 204, '');
	}
	
}

