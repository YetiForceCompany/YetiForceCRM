<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/GitHubIssuesAssignees.php');
require_once(__DIR__ . '/GitHubIssuesComments.php');
require_once(__DIR__ . '/GitHubIssuesEvents.php');
require_once(__DIR__ . '/GitHubIssuesLabels.php');
require_once(__DIR__ . '/GitHubIssuesMilestones.php');
require_once(__DIR__ . '/../objects/GitHubIssue.php');
	

class GitHubIssues extends GitHubService
{

	/**
	 * @var GitHubIssuesAssignees
	 */
	public $assignees;
	
	/**
	 * @var GitHubIssuesComments
	 */
	public $comments;
	
	/**
	 * @var GitHubIssuesEvents
	 */
	public $events;
	
	/**
	 * @var GitHubIssuesLabels
	 */
	public $labels;
	
	/**
	 * @var GitHubIssuesMilestones
	 */
	public $milestones;
	
	
	/**
	 * Initialize sub services
	 */
	public function __construct(GitHubClient $client)
	{
		parent::__construct($client);
		
		$this->assignees = new GitHubIssuesAssignees($client);
		$this->comments = new GitHubIssuesComments($client);
		$this->events = new GitHubIssuesEvents($client);
		$this->labels = new GitHubIssuesLabels($client);
		$this->milestones = new GitHubIssuesMilestones($client);
	}
	
	/**
	 * List all issues
	 * @param $owner boolean|string true, for all my issues, false, for all issues or organization name all issues
	 * @param filter string	Indicates which sorts of issues to return. Can be one of:
	 * 					assigned: Issues assigned to you
	 * 					created: Issues created by you
	 * 					mentioned: Issues mentioning you
	 * 					subscribed: Issues you’re subscribed to updates for
	 * 					all: All issues the authenticated user can see, regardless of participation or creation
	 * 					Default: assigned
	 * @param state string	Indicates the state of the issues to return. Can be either open, closed, or all. Default: open
	 * @param labels string	A list of comma separated label names. Example: bug,ui,@high
	 * @param sort string	What to sort results by. Can be either created, updated, comments. Default: created
	 * @param direction string	The direction of the sort. Can be either asc or desc. Default: desc
	 * @param since string	Only issues updated at or after this time are returned. This is a timestamp in ISO 8601 format: YYYY-MM-DDTHH:MM:SSZ.

	 * @return array<GitHubIssue>
	 */
	public function listAllIssues($owner = false, $filter = null, $state = null, $labels = null, $sort = null, $direction = null, $since = null)
	{
		$data = array();
		if(!is_null($filter))
			$data['filter'] = $filter;
		if(!is_null($state))
			$data['state'] = $state;
		if(!is_null($labels))
			$data['labels'] = $labels;
		if(!is_null($sort))
			$data['sort'] = $sort;
		if(!is_null($direction))
			$data['direction'] = $direction;
		if(!is_null($since))
			$data['since'] = $since;
		
		$path = '/issues';
		if($owner === true)
			$path = '/user/issues';
		elseif(is_string($owner))
			$path = "/orgs/$owner/issues";
		
		return $this->client->request($path, 'GET', $data, 200, 'GitHubIssue', true);
	}
	
	/**
	 * List issues
	 * 
	 * @param $milestone number (Optional) - Milestone to associate this issue with.
	 * @param state string	Indicates the state of the issues to return. Can be either open, closed, or all. Default: open
	 * @param $assignee string (Optional) - Login for the user that this issue should be assigned to.
	 * @param $creator string (Optional) - Login for the user that created this issue.
	 * @param $mentioned string (Optional) - Login for a user mentioned in this issue.
	 * @param labels string	A list of comma separated label names. Example: bug,ui,@high
	 * @param sort string	What to sort results by. Can be either created, updated, comments. Default: created
	 * @param direction string	The direction of the sort. Can be either asc or desc. Default: desc
	 * @param since string	Only issues updated at or after this time are returned. This is a timestamp in ISO 8601 format: YYYY-MM-DDTHH:MM:SSZ.
	 * 
	 * @return array<GitHubIssue>
	 */
	public function listIssues($owner, $repo, $milestone = null, $state = null, $assignee = null, $creator = null, $mentioned = null, $labels = null, $sort = null, $direction = null, $since = null)
	{
		$data = array();
		if(!is_null($milestone))
			$data['milestone'] = $milestone;
		if(!is_null($state))
			$data['state'] = $state;
		if(!is_null($assignee))
			$data['assignee'] = $assignee;
		if(!is_null($creator))
			$data['creator'] = $creator;
		if(!is_null($mentioned))
			$data['mentioned'] = $mentioned;
		if(!is_null($labels))
			$data['labels'] = $labels;
		if(!is_null($sort))
			$data['sort'] = $sort;
		if(!is_null($direction))
			$data['direction'] = $direction;
		if(!is_null($since))
			$data['since'] = $since;

		return $this->client->request("/repos/$owner/$repo/issues", 'GET', $data, 200, 'GitHubIssue', true);
	}
	
	/**
	 * List issues
	 * 
	 * @return GitHubIssue
	 */
	public function getIssue($owner, $repo, $number)
	{
		$data = array();
		
		return $this->client->request("/repos/$owner/$repo/issues/$number", 'GET', $data, 200, 'GitHubIssue');
	}
	
	/**
	 * Create an issue
	 * 
	 * @param $title string (Required) - The title of the issue.
	 * @param $body string (Optional) - The contents of the issue.
	 * @param $assignee string (Optional) - Login for the user that this issue should be assigned to.
	 * @param $milestone number (Optional) - Milestone to associate this issue with.
	 * @param $labels array (Optional) of strings - Labels to associate with this issue. 
	 * 	Pass one or more Labels to _replace_ the set of Labels on this Issue. 
	 * 	Send an empty array (`[]`) to clear all Labels from the Issue.
	 * @return GitHubIssue
	 */
	public function createAnIssue($owner, $repo, $title, $body = null, $assignee = null, $milestone = null, $labels = null)
	{
		$data = array();
		$data['title'] = $title;
		if(!is_null($body))
			$data['body'] = $body;
		if(!is_null($assignee))
			$data['assignee'] = $assignee;
		if(!is_null($milestone))
			$data['milestone'] = $milestone;
		if(!is_null($labels))
			$data['labels'] = $labels;
		
		$data = json_encode($data);
		
		return $this->client->request("/repos/$owner/$repo/issues", 'POST', $data, 201, 'GitHubIssue');
	}

	/**
	 * Edit an issue
	 * 
	 * @param $body string (Optional) - The contents of the issue.
	 * @param $assignee string (Optional) - Login for the user that this issue should be
	 * 	assigned to.
	 * @param $state string (Optional) - State of the issue: `open` or `closed`.
	 * @param $milestone number (Optional) - Milestone to associate this issue with.
	 * @param $labels array (Optional) of **strings** - Labels to associate with this
	 * 	issue. Pass one or more Labels to _replace_ the set of Labels on this
	 * 	Issue. Send an empty array (`[]`) to clear all Labels from the Issue.
	 * @return GitHubIssue
	 */
	public function editAnIssue($owner, $repo, $title, $number, $body = null, $assignee = null, $state = null, $milestone = null, $labels = null)
	{
		$data = array();
		$data['title'] = $title;
		if(!is_null($body))
			$data['body'] = $body;
		if(!is_null($assignee))
			$data['assignee'] = $assignee;
		if(!is_null($state))
			$data['state'] = $state;
		if(!is_null($milestone))
			$data['milestone'] = $milestone;
		if(!is_null($labels))
			$data['labels'] = $labels;
		
		return $this->client->request("/repos/$owner/$repo/issues/$number", 'PATCH', json_encode($data), 200, 'GitHubIssue');
	}		
	
}

