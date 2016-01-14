<?php
/**
 * Client Model
 * @package YetiForce.Github
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Github_Client_Model
{
	const repository = 'YetiForceCRM';
	const ownerRepository = 'YetiForceCompany';
	
	private $clientId;
	private $clientToken;
	private $githubClient;
	
	static private function loadLibrary(){
		require_once($root_directory. 'libraries/github-php-client/client/GitHubClient.php');
	}
	public function setClientId($id){
		$this->clientId = $id;
	}
	public function setToken($token){
		$this->clientToken = $token;
	}
	public function getAllIssues(){
		$issues = $this->githubClient->issues->listIssues(self::ownerRepository, self::repository);
		return $issues;
	}
	public function createIssue($body, $title){
		return $this->githubClient->issues->createAnIssue(self::ownerRepository, self::repository, $title, $body);
	}
	public function isAuthorized(){
		//TO DO
		if((empty($this->clientId) || empty($this->clientToken))){
			return false; 
		}
		return true;
	}
	public function authorization(){
		$this->githubClient->setAuthType(GitHubClient::GITHUB_AUTH_TYPE_BASIC);
		$this->githubClient->setCredentials($this->clientId,$this->clientToken);
	}
	static function getInstance(){
		$instance = new self();
		self::loadLibrary();
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT client_id, token FROM u_yf_github");
		while ($row = $db->getRow($result)) {
			$instance->setClientId($row['client_id']);
			$instance->setToken(base64_decode($row['token']));
		}
		$instance->githubClient = new GitHubClient();
		return $instance;
	}
	public function saveKeys(){
		$db = PearDatabase::getInstance();
		$clientToken = base64_encode($this->clientToken);
		$params = ['client_id' => $this->clientId,
					'token' => $clientToken ];
		return $db->update('u_yf_github', $params);
	}
}
