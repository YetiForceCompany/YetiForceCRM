<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/GitHubOrgsMembers.php');
require_once(__DIR__ . '/GitHubOrgsTeams.php');
require_once(__DIR__ . '/GitHubOrgsRepos.php');
require_once(__DIR__ . '/../objects/GitHubFullOrg.php');
	

class GitHubOrgs extends GitHubService
{

	/**
	 * @var GitHubOrgsMembers
	 */
	public $members;
	
	/**
	 * @var GitHubOrgsTeams
	 */
	public $teams;
	
	/**
	 * @var GitHubOrgsRepos
	 */
	public $repos;

	/**
	 * Initialize sub services
	 */
	public function __construct(GitHubClient $client)
	{
		parent::__construct($client);
		
		$this->members = new GitHubOrgsMembers($client);
		$this->teams = new GitHubOrgsTeams($client);
		$this->repos = new GitHubOrgsRepos($client);
	}
	
	/**
	 * List User Organizations
	 * 
	 * @return array<GitHubFullOrg>
	 */
	public function listUserOrganizations($org)
	{
		$data = array();
		
		return $this->client->request("/orgs/$org", 'GET', $data, 200, 'GitHubFullOrg', true);
	}
	
}

