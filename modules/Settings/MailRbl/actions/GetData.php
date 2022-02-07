<?php

/**
 * Settings MailRbl get data action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailRbl get data action class.
 */
class Settings_MailRbl_GetData_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;
	use \App\Controller\Traits\SettingsPermission;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('forVerification');
		$this->exposeMethod('toSend');
		$this->exposeMethod('request');
		$this->exposeMethod('blackList');
		$this->exposeMethod('whiteList');
		$this->exposeMethod('publicRbl');
		$this->exposeMethod('counters');
	}

	/**
	 * Counters mode.
	 *
	 * @param App\Request $request
	 */
	public function counters(App\Request $request)
	{
		$requestQuery = (new \App\Db\Query())->from('s_#__mail_rbl_request');
		$listQuery = (new \App\Db\Query())->from('s_#__mail_rbl_list');
		$response = new Vtiger_Response();
		$response->setResult([
			'forVerification' => $requestQuery->where(['status' => 0])->count(),
			'toSend' => $requestQuery->where(['status' => 1])->count(),
			'request' => $requestQuery->where(null)->count(),
			'blackList' => $listQuery->where(['type' => \App\Mail\Rbl::LIST_TYPE_BLACK_LIST])->count(),
			'whiteList' => $listQuery->where(['type' => \App\Mail\Rbl::LIST_TYPE_WHITE_LIST])->count(),
			'publicRbl' => $listQuery->where(['type' => [\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST, \App\Mail\Rbl::LIST_TYPE_PUBLIC_WHITE_LIST]])->count(),
		]);
		$response->emit();
	}

	/**
	 * For verification mode.
	 *
	 * @param App\Request $request
	 */
	public function forVerification(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_request')->select(['id',  'type', 'datetime', 'user', 'header']);
		$query->andWhere(['status' => 0]);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$mailRbl = \App\Mail\Rbl::getInstance($row);
			$mailRbl->parse();
			$type = \App\Mail\Rbl::LIST_TYPES[$row['type']];
			if ($ip = ($mailRbl->getSender()['ip'] ?? '')) {
				$icon = '';
				if ($ips = \App\Mail\Rbl::findIp($ip, true)) {
					$type = \App\Mail\Rbl::LIST_TYPES[$ips[0]['type']];
					$icon = "<span class=\"{$type['icon']} mr-2 u-cursor-pointer\" title=\"" . \App\Language::translate('LBL_IP_ALREADY_EXISTS_LIST', 'Settings:MailRbl') . ' ' . \App\Language::translate($type['label'], 'Settings:MailRbl') . '"></span>';
				}
				$ip = $icon . \App\Purifier::encodeHtml($ip);
			}
			$rows[] = [
				'id' => $row['id'],
				'datetime' => \App\Fields\DateTime::formatToDisplay($row['datetime']),
				'user' => \App\Fields\Owner::getUserLabel($row['user']) ?: '',
				'sender' => \App\Purifier::encodeHtml($mailRbl->mailMimeParser->getHeaderValue('from')),
				'recipient' => \App\Purifier::encodeHtml($mailRbl->mailMimeParser->getHeaderValue('to')),
				'ip' => $ip,
				'type' => "<span class=\"{$type['icon']} mr-2\"></span>" . \App\Language::translate($type['label'], 'Settings:MailRbl'),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(['status' => 0])->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows,
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * To send mode.
	 *
	 * @param App\Request $request
	 */
	public function toSend(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_request')->select(['id',  'type', 'datetime', 'user', 'header']);
		$query->andWhere(['status' => 1]);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$mailRbl = \App\Mail\Rbl::getInstance($row);
			$mailRbl->parse();
			$type = \App\Mail\Rbl::LIST_TYPES[$row['type']];
			$rows[] = [
				'id' => $row['id'],
				'datetime' => \App\Fields\DateTime::formatToDisplay($row['datetime']),
				'user' => \App\Fields\Owner::getUserLabel($row['user']) ?: '',
				'sender' => \App\Purifier::encodeHtml($mailRbl->mailMimeParser->getHeaderValue('from')),
				'recipient' => \App\Purifier::encodeHtml($mailRbl->mailMimeParser->getHeaderValue('to')),
				'ip' => \App\Purifier::encodeHtml($mailRbl->getSender()['ip'] ?? ''),
				'type' => "<span class=\"{$type['icon']} mr-2\"></span>" . \App\Language::translate($type['label'], 'Settings:MailRbl'),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(['status' => 1])->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows,
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * Request mode.
	 *
	 * @param App\Request $request
	 */
	public function request(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_request')->select(['id', 'status', 'type', 'datetime', 'user', 'header', 'body']);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$mailRbl = \App\Mail\Rbl::getInstance($row);
			$mailRbl->parse();
			$status = \App\Mail\Rbl::REQUEST_STATUS[$row['status']];
			$type = \App\Mail\Rbl::LIST_TYPES[$row['type']];
			if ($ip = ($mailRbl->getSender()['ip'] ?? '')) {
				$icon = '';
				if ($ips = \App\Mail\Rbl::findIp($ip, true)) {
					$type = \App\Mail\Rbl::LIST_TYPES[$ips[0]['type']];
					$icon = "<span class=\"{$type['icon']} mr-2 u-cursor-pointer\" title=\"" . \App\Language::translate('LBL_IP_ALREADY_EXISTS_LIST', 'Settings:MailRbl') . ' ' . \App\Language::translate($type['label'], 'Settings:MailRbl') . '"></span>';
				}
				$ip = $icon . \App\Purifier::encodeHtml($ip);
			}
			$rows[] = [
				'id' => $row['id'],
				'datetime' => \App\Fields\DateTime::formatToDisplay($row['datetime']),
				'user' => \App\Fields\Owner::getUserLabel($row['user']) ?: '',
				'sender' => \App\Purifier::encodeHtml($mailRbl->mailMimeParser->getHeaderValue('from')),
				'recipient' => \App\Purifier::encodeHtml($mailRbl->mailMimeParser->getHeaderValue('to')),
				'ip' => $ip,
				'statusId' => $row['status'],
				'status' => "<span class=\"{$status['icon']} mr-2\"></span>" . \App\Language::translate($status['label'], 'Settings:MailRbl'),
				'type' => "<span class=\"{$type['icon']} mr-2\"></span>" . \App\Language::translate($type['label'], 'Settings:MailRbl'),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(null)->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows,
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * Black list mode.
	 *
	 * @param App\Request $request
	 */
	public function blackList(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_list')->andWhere(['type' => \App\Mail\Rbl::LIST_TYPE_BLACK_LIST]);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$status = \App\Mail\Rbl::LIST_STATUS[$row['status']];
			$rows[] = [
				'id' => $row['id'],
				'ip' => $row['ip'],
				'statusId' => $row['status'],
				'request' => $row['request'],
				'status' => "<span class=\"{$status['icon']} mr-2\"></span>" . \App\Language::translate($status['label'], 'Settings:MailRbl'),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(['type' => \App\Mail\Rbl::LIST_TYPE_BLACK_LIST])->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows,
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * White list mode.
	 *
	 * @param App\Request $request
	 */
	public function whiteList(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_list')->andWhere(['type' => \App\Mail\Rbl::LIST_TYPE_WHITE_LIST]);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$status = \App\Mail\Rbl::LIST_STATUS[$row['status']];
			$rows[] = [
				'id' => $row['id'],
				'ip' => $row['ip'],
				'statusId' => $row['status'],
				'request' => $row['request'],
				'status' => "<span class=\"{$status['icon']} mr-2\"></span>" . \App\Language::translate($status['label'], 'Settings:MailRbl'),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(['type' => \App\Mail\Rbl::LIST_TYPE_WHITE_LIST])->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows,
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * Public RBL mode.
	 *
	 * @param App\Request $request
	 */
	public function publicRbl(App\Request $request)
	{
		$rows = [];
		$query = $this->getQuery($request);
		$query->from('s_#__mail_rbl_list')->select(['ip', 'status', 'type', 'comment'])->andWhere(['type' => [\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST, \App\Mail\Rbl::LIST_TYPE_PUBLIC_WHITE_LIST]]);
		$dataReader = $query->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			$status = \App\Mail\Rbl::LIST_STATUS[$row['status']];
			$rows[] = [
				'ip' => $row['ip'],
				'type' => \App\Language::translate((\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST == $row['type'] ? 'LBL_BLACK_LIST' : 'LBL_WHITE_LIST'), 'Settings:MailRbl'),
				'statusId' => $row['status'],
				'status' => "<span class=\"{$status['icon']} mr-2\"></span>" . \App\Language::translate($status['label'], 'Settings:MailRbl'),
				'comment' => \App\Layout::truncateText($row['comment'], 30),
			];
		}
		$query->limit(null)->offset(null)->orderBy(null);
		$count = $query->count();
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalRecords' => $query->where(['type' => [\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST, \App\Mail\Rbl::LIST_TYPE_PUBLIC_WHITE_LIST]])->count(),
			'iTotalDisplayRecords' => $count,
			'aaData' => $rows,
		];
		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * Get query.
	 *
	 * @param App\Request $request
	 *
	 * @return App\Db\Query
	 */
	private function getQuery(App\Request $request): App\Db\Query
	{
		$columns = [];
		foreach ($request->getArray('columns') as $key => $value) {
			$columns[$key] = $value['data'];
		}
		$query = (new \App\Db\Query());
		$query->limit($request->getInteger('length'));
		$query->offset($request->getInteger('start'));
		$order = current($request->getArray('order', 'Alnum'));
		if ($order && isset($columns[$order['column']])) {
			$query->orderBy([$columns[$order['column']] => 'asc' === $order['dir'] ? \SORT_ASC : \SORT_DESC]);
		}
		if (!$request->isEmpty('type') && ($type = $request->getArray('type', 'Integer'))) {
			$query->andWhere(['type' => $type]);
		}
		if (!$request->isEmpty('status') && ($status = $request->getArray('status', 'Integer'))) {
			$query->andWhere(['status' => $status]);
		}
		if (!$request->isEmpty('ip') && ($ip = $request->getByType('ip', 'ip'))) {
			$query->andWhere(['ip' => $ip]);
		}
		if (!$request->isEmpty('date') && ($date = $request->getDateRange('date'))) {
			$query->andWhere(['between', 'datetime', $date[0] . ' 00:00:00', $date[1] . ' 23:59:59']);
		}
		if (!$request->isEmpty('users') && ($users = $request->getArray('users', 'Integer'))) {
			$query->andWhere(['user' => $users]);
		}
		return $query;
	}
}
