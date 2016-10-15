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
	const url = 'https://api.github.com';
	const timeout = 240;

	private $clientId;
	private $clientToken;
	private $username;

	public function setUsername($name)
	{
		$this->username = $name;
	}

	public function setClientId($id)
	{
		$this->clientId = $id;
	}

	public function setToken($token)
	{
		$this->clientToken = $token;
	}

	public function getAllIssues($numPage, $state, $author = false)
	{
		$data['page'] = $numPage;
		$data['per_page'] = 20;
		$path = '/search/issues';
		$data['q'] = 'user:' . self::ownerRepository . ' repo:' . self::repository . " is:issue is:$state";
		if ($author) {
			$data['q'].=" author:$this->username";
		}
		$issues = $this->doRequest($path, 'GET', $data, '200');
		if ($issues === false) {
			return false;
		}
		$issuesModel = [];
		foreach ($issues->items as $issue) {
			$issuesModel[] = Settings_Github_Issues_Model::getInstanceFromArray($issue);
		}
		Settings_Github_Issues_Model::$totalCount = $issues->total_count;
		return $issuesModel;
	}

	public function createIssue($body, $title)
	{
		$path = '/repos/' . self::ownerRepository . '/' . self::repository . '/issues';
		$data['title'] = $title;
		$data['body'] = $body;
		$data = json_encode($data);
		return $this->doRequest($path, 'POST', $data, '201 OK');
	}

	public function isAuthorized()
	{
		if ((empty($this->clientId) || empty($this->clientToken))) {
			return false;
		}
		return true;
	}

	static function getInstance()
	{
		$instance = new self();
		$row = (new App\Db\Query())
			->select(['client_id', 'token', 'username'])
			->from('u_#__github')
			->createCommand()->queryOne();
		if (!empty($row)) {
			$instance->setClientId($row['client_id']);
			$instance->setToken(base64_decode($row['token']));
			$instance->setUsername($row['username']);
		}
		return $instance;
	}

	public function saveKeys()
	{
		$clientToken = base64_encode($this->clientToken);
		$params = ['client_id' => $this->clientId,
			'token' => $clientToken,
			'username' => $this->username];
		return App\Db::getInstance()->createCommand()->update('u_#__github', $params)->execute();
	}

	public function checkToken()
	{
		$data['access_token'] = $this->clientToken;
		$userInfo = $this->doRequest('/user', 'GET', $data, '200');
		if (!(empty($userInfo->login) || empty($this->username))) {
			if ($userInfo->login == $this->username) {
				return true;
			}
		}
		return false;
	}

	private function doRequest($url, $method, $data, $status)
	{
		$url = self::url . $url;
		$curl = curl_init();
		if ($this->isAuthorized()) {
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, "$this->clientId:$this->clientToken");
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, "YetiforceCRM");
		curl_setopt($curl, CURLOPT_TIMEOUT, self::timeout);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		switch ($method) {
			case 'GET':
				curl_setopt($curl, CURLOPT_HTTPGET, true);
				if (count($data))
					$url .= '?' . http_build_query($data);
				break;

			case 'POST':
				curl_setopt($curl, CURLOPT_POST, true);
				if (count($data))
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$content = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($code != $status) {
			return false;
		}
		$response = json_decode($content);
		return $response;
	}
}
