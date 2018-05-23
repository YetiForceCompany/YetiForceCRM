<?php

/**
 * UIType sharedOwner Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_SharedOwner_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (is_array($value)) {
			$value = implode(',', $value);
		}

		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_array($value)) {
			settype($value, 'array');
		}
		$rangeValues = null;
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength) {
			$rangeValues = explode(',', $maximumLength);
		}
		foreach ($value as $shownerid) {
			if (!is_numeric($shownerid)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $shownerid, 406);
			}
			if ($rangeValues && (($rangeValues[1] ?? $rangeValues[0]) < $shownerid || (isset($rangeValues[1]) ? $rangeValues[0] : 0) > $shownerid)) {
				throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $shownerid, 406);
			}
		}
		$this->validate = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$isAdmin = \App\User::getCurrentUserModel()->isAdmin();
		if (empty($value)) {
			return '';
		} elseif (!is_array($value)) {
			$values = explode(',', $value);
		}
		$displayValue = [];
		foreach ($values as $shownerid) {
			$ownerName = rtrim(\App\Fields\Owner::getLabel($shownerid));
			if (!$isAdmin || $rawText) {
				$displayValue[] = $ownerName;
				continue;
			}
			$detailViewUrl = '';
			switch (\App\Fields\Owner::getType($shownerid)) {
				case 'Users':
					$userModel = Users_Privileges_Model::getInstanceById($shownerid);
					$userModel->setModule('Users');
					if ($userModel->get('status') === 'Inactive') {
						$ownerName = '<span class="redColor">' . $ownerName . '</span>';
					}
					if (App\User::getCurrentUserModel()->isAdmin()) {
						$detailViewUrl = $userModel->getDetailViewUrl();
					}
					break;
				case 'Groups':
					if (App\User::getCurrentUserModel()->isAdmin()) {
						$recordModel = new Settings_Groups_Record_Model();
						$recordModel->set('groupid', $shownerid);
						$detailViewUrl = $recordModel->getDetailViewUrl();
					}
					break;
				default:
					$ownerName = '<span class="redColor">---</span>';
					break;
			}
			if (!empty($detailViewUrl)) {
				$displayValue[] = "<a href=\"$detailViewUrl\">$ownerName</a>";
			}
		}

		return implode(', ', $displayValue);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$values = $this->getSharedOwners($record);
		if (empty($values)) {
			return '';
		}
		$display = $shownerData = [];
		$maxLengthText = $this->getFieldModel()->get('maxlengthtext');
		$isAdmin = \App\User::getCurrentUserModel()->isAdmin();
		foreach ($values as $key => $shownerid) {
			$name = \App\Fields\Owner::getLabel($shownerid);
			switch (\App\Fields\Owner::getType($shownerid)) {
				case 'Users':
					$userModel = Users_Privileges_Model::getInstanceById($shownerid);
					$userModel->setModule('Users');
					$display[$key] = $name;
					if ($userModel->get('status') === 'Inactive') {
						$shownerData[$key]['inactive'] = true;
					}
					if ($isAdmin && !$rawText) {
						$shownerData[$key]['link'] = $userModel->getDetailViewUrl();
					}
					break;
				case 'Groups':
					if (empty($name)) {
						continue;
					}
					$display[$key] = $name;
					$recordModel = new Settings_Groups_Record_Model();
					$recordModel->set('groupid', $shownerid);
					$detailViewUrl = $recordModel->getDetailViewUrl();
					if ($isAdmin && !$rawText) {
						$shownerData[$key]['link'] = $detailViewUrl;
					}

					break;
				default:
					break;
			}
		}
		$display = implode(', ', $display);
		$display = explode(', ', \App\TextParser::textTruncate($display, $maxLengthText));
		foreach ($display as $key => &$shownerName) {
			if (isset($shownerData[$key]['inactive'])) {
				$shownerName = '<span class="redColor">' . $shownerName . '</span>';
			}
			if (isset($shownerData[$key]['link'])) {
				$shownerName = "<a href='" . $shownerData[$key]['link'] . "'>$shownerName</a>";
			}
		}

		return implode(', ', $display);
	}

	/**
	 * Function to get the share users list.
	 *
	 * @param int  $record      record ID
	 * @param bool $returnArray whether return data in an array
	 *
	 * @return array
	 */
	public static function getSharedOwners($record, $moduleName = false)
	{
		$shownerid = Vtiger_Cache::get('SharedOwner', $record);
		if ($shownerid !== false) {
			return $shownerid;
		}

		$query = (new \App\Db\Query())->select('userid')->from('u_#__crmentity_showners')->where(['crmid' => $record])->distinct();
		$values = $query->column();
		if (empty($values)) {
			$values = [];
		}
		Vtiger_Cache::set('SharedOwner', $record, $values);

		return $values;
	}

	public static function getSearchViewList($moduleName, $cvId)
	{
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->initForCustomViewById($cvId);
		$queryGenerator->setFields([]);
		$queryGenerator->setCustomColumn('u_#__crmentity_showners.userid');
		$queryGenerator->addJoin(['INNER JOIN', 'u_#__crmentity_showners', "{$queryGenerator->getColumnName('id')} = u_#__crmentity_showners.crmid"]);
		$dataReader = $queryGenerator->createQuery()->distinct()->createCommand()->query();
		$users = $group = [];
		while ($id = $dataReader->readColumn(0)) {
			$name = \App\Fields\Owner::getUserLabel($id);
			if (!empty($name)) {
				$users[$id] = $name;
				continue;
			}
			$name = \App\Fields\Owner::getGroupName($id);
			if ($name !== false) {
				$group[$id] = $name;
				continue;
			}
		}
		asort($users);
		asort($group);

		return ['users' => $users, 'group' => $group];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/SharedOwner.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/SharedOwner.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function isListviewSortable()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRangeValues()
	{
		return '65535';
	}
}
