<?php

require_once(__DIR__ . '/GitHubOauthAccess.php');

	

class GitHubOauthAccessWithUser extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
		));
	}
	
}

