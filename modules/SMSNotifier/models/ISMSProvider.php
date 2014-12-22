<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

vimport('~~/vtlib/Vtiger/Net/Client.php');

interface SMSNotifier_ISMSProvider_Model {

	const MSG_STATUS_DISPATCHED = 'Dispatched';
	const MSG_STATUS_PROCESSING = 'Processing';
	const MSG_STATUS_DELIVERED  = 'Delivered';
	const MSG_STATUS_FAILED     = 'Failed';
	const MSG_STATUS_ERROR      = 'ERR: ';

	const SERVICE_SEND = 'SEND';
	const SERVICE_QUERY= 'QUERY';
	const SERVICE_PING = 'PING';
	const SERVICE_AUTH = 'AUTH';

	/**
	 * Function to get required parameters other than (userName, password)
	 */
	public function getRequiredParams();

	/**
	 * Function to get service URL to use for a given type
	 * @param <String> $type like SEND, PING, QUERY
	 */
	public function getServiceURL($type = false);

	/**
	 * Function to set authentication parameters
	 * @param <String> $userName
	 * @param <String> $password
	 */
	public function setAuthParameters($userName, $password);

	/**
	 * Function to set non-auth parameter.
	 * @param <String> $key
	 * @param <String> $value
	 */
	public function setParameter($key, $value);

	/**
	 * Function to handle SMS Send operation
	 * @param <String> $message
	 * @param <Mixed> $toNumbers One or Array of numbers
	 */
	public function send($message, $toNumbers);

	/**
	 * Function to get query for status using messgae id
	 * @param <Number> $messageId
	 */
	public function query($messageId);

}
?>
