<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubThread.php');
require_once(__DIR__ . '/../objects/GitHubSubscription.php');
	

class GitHubActivityNotifications extends GitHubService
{

	/**
	 * List your notifications
	 * 
	 * @param $all boolean (Optional) `true` to show notifications marked as read.
	 * @param $participating boolean (Optional) `true` to show only notifications in which the user is
	 * 	directly participating or mentioned.
	 * @param $since time (Optional) filters out any notifications updated before the given
	 * 	time.  The time should be passed in as UTC in the ISO 8601 format:
	 * 	`YYYY-MM-DDTHH:MM:SSZ`.  Example: "2012-10-09T23:39:01Z".
	 * @return array<GitHubThread>
	 */
	public function listYourNotifications($all = null, $participating = null, $since = null)
	{
		$data = array();
		if(!is_null($all))
			$data['all'] = $all;
		if(!is_null($participating))
			$data['participating'] = $participating;
		if(!is_null($since))
			$data['since'] = $since;
		
		return $this->client->request("/notifications", 'GET', $data, 200, 'GitHubThread', true);
	}
	
	/**
	 * List your notifications in a repository
	 * 
	 * @param $all boolean (Optional) `true` to show notifications marked as read.
	 * @param $participating boolean (Optional) `true` to show only notifications in which the user is
	 * 	directly participating or mentioned.
	 * @param $since time (Optional) filters out any notifications updated before the given
	 * 	time.  The time should be passed in as UTC in the ISO 8601 format:
	 * 	`YYYY-MM-DDTHH:MM:SSZ`.  Example: "2012-10-09T23:39:01Z".
	 * @return array<GitHubThread>
	 */
	public function listYourNotificationsInRepository($owner, $repo, $all = null, $participating = null, $since = null)
	{
		$data = array();
		if(!is_null($all))
			$data['all'] = $all;
		if(!is_null($participating))
			$data['participating'] = $participating;
		if(!is_null($since))
			$data['since'] = $since;
		
		return $this->client->request("/repos/$owner/$repo/notifications", 'GET', $data, 200, 'GitHubThread', true);
	}
	
	/**
	 * Mark as read
	 * 
	 * @param $last_read_at Time (Optional) Describes the last point that notifications were checked.  Anything
	 * 	updated since this time will not be updated.  Default: Now.  Expected in ISO
	 * 	8601 format: `YYYY-MM-DDTHH:MM:SSZ`.  Example: "2012-10-09T23:39:01Z".
	 */
	public function markAsRead($last_read_at = null)
	{
		$data = array();
		if(!is_null($last_read_at))
			$data['last_read_at'] = $last_read_at;
		
		return $this->client->request("/notifications", 'PUT', $data, 205, '');
	}
	
	/**
	 * Mark notifications as read in a repository
	 * 
	 * @param $last_read_at Time (Optional) Describes the last point that notifications were checked.  Anything
	 * 	updated since this time will not be updated.  Default: Now.  Expected in ISO
	 * 	8601 format: `YYYY-MM-DDTHH:MM:SSZ`.  Example: "2012-10-09T23:39:01Z".
	 */
	public function markNotificationsAsReadInRepository($owner, $repo, $last_read_at = null)
	{
		$data = array();
		if(!is_null($last_read_at))
			$data['last_read_at'] = $last_read_at;
		
		return $this->client->request("/repos/$owner/$repo/notifications", 'PUT', $data, 205, '');
	}
	
	/**
	 * View a single thread
	 * 
	 * @return array<GitHubThread>
	 */
	public function viewSingleThread($id)
	{
		$data = array();
		
		return $this->client->request("/notifications/threads/$id", 'GET', $data, 200, 'GitHubThread', true);
	}
	
	/**
	 * Mark a thread as read
	 * 
	 */
	public function markThreadAsRead($id)
	{
		$data = array();
		
		return $this->client->request("/notifications/threads/$id", 'PATCH', $data, 205, '');
	}
	
	/**
	 * Get a Thread Subscription
	 * 
	 * @return GitHubSubscription
	 */
	public function getThreadSubscription()
	{
		$data = array();
		
		return $this->client->request("/notifications/threads/1/subscription", 'GET', $data, 200, 'GitHubSubscription');
	}
	
	/**
	 * Set a Thread Subscription
	 * 
	 * @return GitHubSubscription
	 */
	public function setThreadSubscription()
	{
		$data = array();
		
		return $this->client->request("/notifications/threads/1/subscription", 'PUT', $data, 200, 'GitHubSubscription');
	}
	
	/**
	 * Delete a Thread Subscription
	 * 
	 */
	public function deleteThreadSubscription()
	{
		$data = array();
		
		return $this->client->request("/notifications/threads/1/subscription", 'DELETE', $data, 204, '');
	}
	
}

