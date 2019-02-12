<?php
/**
 * Client Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Class Settings_Github_Client_Model.
 */
class Settings_Github_Client_Model
{
	/**
	 * Repository name.
	 */
	const REPOSITORY = 'YetiForceCRM';

	/**
	 * Owner repository.
	 */
	const OWNER_REPOSITORY = 'YetiForceCompany';

	/**
	 * Address url to api.
	 */
	const URL = 'https://api.github.com';

	/**
	 * Token.
	 *
	 * @var string
	 */
	private $clientToken;

	/**
	 * Username.
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Function to set username.
	 *
	 * @param string $name
	 */
	public function setUsername($name)
	{
		$this->username = $name;
	}

	/**
	 * Function to set token.
	 *
	 * @param string $token
	 */
	public function setToken($token)
	{
		$this->clientToken = $token;
	}

	/**
	 * Function to get all issues.
	 *
	 * @param int    $numPage
	 * @param string $state
	 * @param string $author
	 *
	 * @return Settings_Github_Issues_Model[]
	 */
	public function getAllIssues($numPage, $state, $author = false)
	{
		$data['page'] = $numPage;
		$data['per_page'] = 20;
		$path = '/search/issues';
		$data['q'] = 'user:' . self::OWNER_REPOSITORY . ' repo:' . self::REPOSITORY . " is:issue is:$state";
		if ($author) {
			$data['q'] .= " author:$this->username";
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

	/**
	 * Function to create issue.
	 *
	 * @param string $body
	 * @param string $title
	 *
	 * @return bool|array
	 */
	public function createIssue($body, $title)
	{
		$path = '/repos/' . static::OWNER_REPOSITORY . '/' . static::REPOSITORY . '/issues';
		$data['title'] = $title;
		$data['body'] = $body;

		return $this->doRequest($path, 'POST', App\Json::encode($data), '201 OK');
	}

	/**
	 * Function to check autorization.
	 *
	 * @return bool
	 */
	public function isAuthorized()
	{
		if ((empty($this->username) || empty($this->clientToken))) {
			return false;
		}
		return true;
	}

	/**
	 * Function to get object.
	 *
	 * @return \self
	 */
	public static function getInstance()
	{
		$instance = new self();
		$row = (new App\Db\Query())
			->select(['token', 'username'])
			->from('u_#__github')
			->createCommand()->queryOne();
		if (!empty($row)) {
			$instance->setToken(App\Encryption::getInstance()->decrypt($row['token']));
			$instance->setUsername($row['username']);
		}
		return $instance;
	}

	/**
	 * Function to save key.
	 *
	 * @return int
	 */
	public function saveKeys()
	{
		return App\Db::getInstance()->createCommand()->update('u_#__github', [
			'token' => App\Encryption::getInstance()->encrypt($this->clientToken),
			'username' => $this->username,
		])->execute();
	}

	/**
	 * Function to check token.
	 *
	 * @return bool
	 */
	public function checkToken()
	{
		$data['access_token'] = $this->clientToken;
		$userInfo = $this->doRequest('/user', 'GET', $data, '200');
		if (!empty($userInfo->login) && $userInfo->login == $this->username) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get data from github.com.
	 *
	 * @param string $url
	 * @param string $method
	 * @param array  $data
	 * @param string $status
	 *
	 * @return bool|array
	 */
	private function doRequest($url, $method, $data, $status)
	{
		$url = self::URL . $url;
		$options = [];
		if ($this->isAuthorized()) {
			$options['auth'] = [$this->username, $this->clientToken];
		}
		try {
			switch ($method) {
				case 'GET':
					$url .= '?' . http_build_query($data);
					$content = \Requests::get($url, [], $options);
					break;
				case 'POST':
					$content = \Requests::post($url, [], $data, $options);
					break;
				default:
					break;
			}
		} catch (Exception $e) {
			\App\Log::warning($e->getMessage());
			return false;
		}

		$code = $content->status_code;
		if ($code != $status) {
			return false;
		}
		return App\Json::decode($content->body, App\Json::TYPE_OBJECT);
	}
}
