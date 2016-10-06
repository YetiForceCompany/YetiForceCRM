<?php

/**
 * Notification Record Model
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function to parse content
	 * @param string $fieldName
	 * @return string
	 */
	public function getParseField($fieldName)
	{
		$relatedModule = $this->get('relatedmodule');
		$reletedId = $this->get('relatedid');
		$value = $this->get($fieldName);
		if ($relatedModule != 'Users' && \includes\Record::isExists($reletedId)) {
			$textParser = Vtiger_TextParser_Helper::getInstanceById($reletedId, $relatedModule);
			$textParser->setContent($value);
			$value = $textParser->parse();
			return $value;
		} else {
			$textParser = Vtiger_TextParser_Helper::getCleanInstance();
			$textParser->setContent($value);
			$value = $textParser->parseTranslations();
		}
		return $value;
	}

	public function getTitle()
	{
		return $this->getParseField('title');
	}

	public function getName()
	{
		return $this->getParseField('title');
	}

	/**
	 * Function to get id
	 * @return type
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Return name type notification
	 * @return string
	 */
	public function getTypeName()
	{
		return $this->get('type');
	}

	/**
	 * Return message of notification
	 * @return string
	 */
	public function getMessage()
	{
		return $this->getParseField('description');
	}

	/**
	 * Function to set notification as read
	 */
	public function setMarked()
	{
		$this->set('notification_status', 'PLL_READ');
		$this->save();
	}
	/*
	 * Function to save record
	 */

	public function save()
	{
		$relatedModule = $this->get('relatedmodule');
		$reletedId = $this->get('relatedid');
		if ($relatedModule != 'Users' && \includes\Record::isExists($reletedId)) {
			$message = $this->get('description');
			$textParser = Vtiger_TextParser_Helper::getInstanceById($reletedId, $relatedModule);
			$textParser->set('withoutTranslations', true);
			$textParser->setContent($message);
			$message = $textParser->parse();
			$this->set('description', $message);

			$title = $this->get('title');
			$textParser->setContent($title);
			$title = $textParser->parse();
			$this->set('title', $title);
		}
		parent::save();
	}

	/**
	 * Function to get icon for notification
	 * @return <Array> params icon
	 */
	public function getIcon()
	{
		$icon = false;
		switch ($this->get('type')) {
			case 0:
				$userModel = Users_Privileges_Model::getInstanceById($this->get('relatedid'));
				$icon = [
					'type' => 'image',
					'title' => $userModel->getName(),
					'src' => $userModel->getImagePath(),
					'class' => 'userImage',
				];
				break;
			default:
				$icon = [
					'type' => 'icon',
					'title' => vtranslate($this->get('reletedmodule'), $this->get('relatedmodule')),
					'class' => 'userIcon-' . $this->get('reletedmodule'),
				];
				break;
		}
		return $icon;
	}
}
