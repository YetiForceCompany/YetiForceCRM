<?php

require_once(__DIR__ . '/../GitHubClient.php');
require_once(__DIR__ . '/../GitHubService.php');
require_once(__DIR__ . '/../objects/GitHubOauthAccess.php');
require_once(__DIR__ . '/../objects/GitHubOauthAccessWithUser.php');
	

class GitHubOauth extends GitHubService
{

	/**
	 * List your authorizations
	 * 
	 * @return array<GitHubOauthAccess>
	 */
	public function listYourAuthorizations()
	{
		$data = array();
		
		return $this->client->request("/authorizations", 'GET', $data, 200, 'GitHubOauthAccess', true);
	}
	
	/**
	 * Get a single authorization
	 * 
	 * @return GitHubOauthAccess
	 */
	public function getAingleAuthorization($id)
	{
		$data = array();
		
		return $this->client->request("/authorizations/$id", 'GET', $data, 200, 'GitHubOauthAccess');
	}
	
	/**
	 * Create a new authorization
	 * 
	 * @param $scopes array (Optional) - Replaces the authorization scopes with these.
	 * @param $note string (Optional) - A note to remind you what the OAuth token is for.
	 * @param $note_url string (Optional) - A URL to remind you what app the OAuth token is for.
	 * @param $client_id string (Optional) - The 20 character OAuth app client key for which to create the token.
	 * @param $client_secret string (Optional) - The 40 character OAuth app client secret for which to create the token.
	 * @param $fingerprint string (Optional) - A unique string to distinguish an authorization from others created for the same client ID and user.
	 * @return GitHubOauthAccess
	 */
	public function createNewAuthorization($scopes = null, $note = null, $note_url = null, $client_id = null, $client_secret = null, $fingerprint = null)
	{
		$data = array();
		if(!is_null($scopes))
			$data['scopes'] = $scopes;
		if(!is_null($note))
			$data['note'] = $note;
		if(!is_null($note_url))
			$data['note_url'] = $note_url;
		if(!is_null($client_id))
			$data['client_id'] = $client_id;
		if(!is_null($client_secret))
			$data['client_secret'] = $client_secret;
		if(!is_null($fingerprint))
			$data['fingerprint'] = $fingerprint;
		
		return $this->client->request("/authorizations", 'POST', $data, 201, 'GitHubOauthAccess');
	}
	
	/**
	 * Get-or-create an authorization for a specific app
	 * 
	 * @param $scopes array (Optional) - Replaces the authorization scopes with these.
	 * @param $note string (Optional) - A note to remind you what the OAuth token is for.
	 * @param $note_url string (Optional) - A URL to remind you what app the OAuth token is for.
	 * @param $client_id string (Optional) - The 20 character OAuth app client key for which to create the token.
	 * @param $client_secret string (Optional) - The 40 character OAuth app client secret for which to create the token.
	 * @param $fingerprint string (Optional) - A unique string to distinguish an authorization from others created for the same client ID and user.
	 * @return GitHubOauthAccess
	 */
	public function getOrCreateAuthorizationForApp($scopes = null, $note = null, $note_url = null, $client_id = null, $client_secret = null, $fingerprint = null)
	{
		$data = array();
		if(!is_null($scopes))
			$data['scopes'] = $scopes;
		if(!is_null($note))
			$data['note'] = $note;
		if(!is_null($note_url))
			$data['note_url'] = $note_url;
		if(!is_null($client_secret))
			$data['client_secret'] = $client_secret;
		if(!is_null($fingerprint))
			$data['fingerprint'] = $fingerprint;
		
		return $this->client->request("/authorizations/clients/$client_id", 'PUT', $data, array(200, 201), 'GitHubOauthAccess');
	}
	
	/**
	 * Get-or-create an authorization for a specific app and fingerprint
	 * 
	 * @param $scopes array (Optional) - Replaces the authorization scopes with these.
	 * @param $note string (Optional) - A note to remind you what the OAuth token is for.
	 * @param $note_url string (Optional) - A URL to remind you what app the OAuth token is for.
	 * @param $client_id string (Optional) - The 20 character OAuth app client key for which to create the token.
	 * @param $client_secret string (Optional) - The 40 character OAuth app client secret for which to create the token.
	 * @param $fingerprint string (Optional) - A unique string to distinguish an authorization from others created for the same client ID and user.
	 * @return GitHubOauthAccess
	 */
	public function getOrCreateAuthorizationForAppAndFingerprint($scopes = null, $note = null, $note_url = null, $client_id = null, $client_secret = null, $fingerprint = null)
	{
		$data = array();
		if(!is_null($scopes))
			$data['scopes'] = $scopes;
		if(!is_null($note))
			$data['note'] = $note;
		if(!is_null($note_url))
			$data['note_url'] = $note_url;
		if(!is_null($client_secret))
			$data['client_secret'] = $client_secret;
		
		return $this->client->request("/authorizations/clients/$client_id/$fingerprint", 'PUT', $data, array(200, 201), 'GitHubOauthAccess');
	}
	
	/**
	 * Create a new authorization
	 * 
	 * @param $scopes array (Optional) - Replaces the authorization scopes with these.
	 * @param $add_scopes array (Optional) - A list of scopes to add to this authorization.
	 * @param $remove_scopes array (Optional) - A list of scopes to remove from this
	 * 	authorization.
	 * @param $note string (Optional) - A note to remind you what the OAuth token is for.
	 * @param $note_url string (Optional) - A URL to remind you what app the OAuth token is for.
	 * @param $fingerprint string (Optional) - A unique string to distinguish an authorization from others created for the same client ID and user.
	 * @return GitHubOauthAccess
	 */
	public function updateAuthorization($id, $scopes = null, $add_scopes = null, $remove_scopes = null, $note = null, $note_url = null, $fingerprint = null)
	{
		$data = array();
		if(!is_null($scopes))
			$data['scopes'] = $scopes;
		if(!is_null($add_scopes))
			$data['add_scopes'] = $add_scopes;
		if(!is_null($remove_scopes))
			$data['remove_scopes'] = $remove_scopes;
		if(!is_null($note))
			$data['note'] = $note;
		if(!is_null($note_url))
			$data['note_url'] = $note_url;
		if(!is_null($fingerprint))
			$data['fingerprint'] = $fingerprint;
		
		return $this->client->request("/authorizations/$id", 'PATCH', $data, 201, 'GitHubOauthAccess');
	}
	
	/**
	 * Reset an authorization
	 * 
	 * @return GitHubOauthAccess
	 */
	public function resetAuthorization($client_id, $access_token)
	{
		$data = array();
		
		return $this->client->request("/applications/$client_id/tokens/$access_token", 'POST', $data, 200, 'GitHubOauthAccess');
	}
	
	/**
	 * Delete an authorization
	 * 
	 */
	public function deleteAnAuthorization($id)
	{
		$data = array();
		
		return $this->client->request("/authorizations/$id", 'DELETE', $data, 204, '');
	}
	
	/**
	 * Revoke all authorizations for an application
	 * 
	 */
	public function revokeAllAppAuthorizations($client_id)
	{
		$data = array();
		
		return $this->client->request("/applications/$client_id/tokens", 'DELETE', $data, 204, '');
	}
	
	/**
	 * Revoke an authorization for an application
	 * 
	 */
	public function revokeAppAuthorization($client_id, $access_token)
	{
		$data = array();
		
		return $this->client->request("/applications/$client_id/tokens/$access_token", 'DELETE', $data, 204, '');
	}
	
	/**
	 * Check an authorization
	 * 
	 * @return GitHubOauthAccessWithUser
	 */
	public function checkAnAuthorization($client_id, $access_token)
	{
		$data = array();
		
		return $this->client->request("/applications/$client_id/tokens/$access_token", 'GET', $data, 200, 'GitHubOauthAccessWithUser', true);
	}
	
}

