<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubUser.php');
	

class GitHubOrgsMembers extends GitHubService
{

	/**
	 * Members list
	 * 
	 * @return array<GitHubUser>
	 */
	public function membersList($org)
	{
		$data = array();
		
		return $this->client->request("/orgs/$org/members", 'GET', $data, 200, 'GitHubUser', true);
	}
	
	/**
	 * Response if requester is not an organization member
	 * 
	 */
	public function responseIfRequesterIsNotAnOrganizationMember($org, $user)
	{
		$data = array();
		
		return $this->client->request("/orgs/$org/members/$user", 'DELETE', $data, 204, '');
	}
	
	/**
	 * Public members list
	 * 
	 * @return array<GitHubUser>
	 */
	public function publicMembersList($org)
	{
		$data = array();
		
		return $this->client->request("/orgs/$org/public_members", 'GET', $data, 200, 'GitHubUser', true);
	}
	
	/**
	 * Check public membership
	 * 
	 */
	public function checkPublicMembership($org, $user)
	{
		$data = array();
		
		return $this->client->request("/orgs/$org/public_members/$user", 'PUT', $data, 204, '');
	}
	
}

