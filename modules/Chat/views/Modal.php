<?php

/**
 * Chat Modal View Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Chat_Modal_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-fullscreen';

	/**
	 * {@inheritdoc}
	 */
	public $modalIcon = 'fas fa-comments fa-fw';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$chat = \App\Chat::getInstance();
		$chatEntries = $chat->getEntries();
		$viewer->assign('CHAT_ENTRIES', $chatEntries);
		$viewer->assign('CHAT', $chat);
		$viewer->assign('SHOW_MORE_BUTTON', count($chatEntries) > \AppConfig::module('Chat', 'CHAT_ROWS_LIMIT'));
		$viewer->assign('CURRENT_ROOM', \App\Chat::getCurrentRoom());
		$viewer->assign('IS_MODAL_VIEW', true);
		$viewer->assign('IS_SOUND_NOTIFICATION', $this->isSoundNotification());
		$viewer->assign('IS_DESKTOP_NOTIFICATION', $this->isDesktopNotification());
		$viewer->assign('SEND_BY_ENTER', $this->sendByEnter());
		$viewer->view('Modal.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('ModalFooter.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(\App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'modules.Chat.resources.Modal'
		]));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function preProcessTplName(\App\Request $request)
	{
		return 'ModalHeader.tpl';
	}

	/**
	 * Check if sound notification is enabled.
	 *
	 * @return bool
	 */
	private function isSoundNotification(): bool
	{
		return isset($_COOKIE['chat-isSoundNotification']) ?
			filter_var($_COOKIE['chat-isSoundNotification'], FILTER_VALIDATE_BOOLEAN) :
			\AppConfig::module('Chat', 'DEFAULT_SOUND_NOTIFICATION');
	}

	/**
	 * Check if desktop notification is enabled.
	 *
	 * @return bool
	 */
	private function isDesktopNotification(): bool
	{
		return isset($_COOKIE['chat-isDesktopNotification']) ?
			filter_var($_COOKIE['chat-isDesktopNotification'], FILTER_VALIDATE_BOOLEAN) : false;
	}

	/**
	 * Check if sending on ENTER is active.
	 *
	 * @return bool
	 */
	private function sendByEnter(): bool
	{
		return isset($_COOKIE['chat-notSendByEnter']) ?
			!filter_var($_COOKIE['chat-notSendByEnter'], FILTER_VALIDATE_BOOLEAN) : true;
	}
}
