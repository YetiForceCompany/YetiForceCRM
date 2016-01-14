<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubTemplates.php');
require_once(__DIR__ . '/../objects/GitHubTemplate.php');
	

class GitHubGitignore extends GitHubService
{

	/**
	 * Listing available templates
	 * 
	 * @return array<GitHubTemplates>
	 */
	public function listingAvailableTemplates()
	{
		$data = array();
		
		return $this->client->request("/gitignore/templates", 'GET', $data, 200, 'GitHubTemplates', true);
	}
	
	/**
	 * Get a single template
	 * 
	 * @return array<GitHubTemplate>
	 */
	public function getSingleTemplate()
	{
		$data = array();
		
		return $this->client->request("/gitignore/templates/C", 'GET', $data, 200, 'GitHubTemplate', true);
	}
	
}

