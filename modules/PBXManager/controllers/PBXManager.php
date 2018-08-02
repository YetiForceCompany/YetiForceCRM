<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PBXManager_PBXManager_Controller
{
	public function getConnector()
	{
		return new PBXManager_PBXManager_Connector();
	}

	/**
	 * Function to process the request.
	 *
	 * @param \App\Request $request
	 *                              return response object
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->get('callstatus');

		switch ($mode) {
			case 'StartApp':
				$this->processStartupCall($request);
				break;
			case 'DialAnswer':
				$this->processDialCall($request);
				break;
			case 'Record':
				$this->processRecording($request);
				break;
			case 'EndCall':
				$this->processEndCall($request);
				break;
			case 'Hangup':
				$callCause = $request->get('causetxt');
				if ($callCause === 'null') {
					break;
				}
				$this->processHangupCall($request);
				break;
		}
	}

	/**
	 * Function to process Incoming call request.
	 *
	 * @param \App\Request $request
	 *                              return response object
	 */
	public function processStartupCall(\App\Request $request)
	{
		$connector = $this->getConnector();

		$temp = $request->get('channel');
		$temp = explode('-', $temp);
		$temp = explode('/', $temp[0]);

		$callerNumber = $request->get('callerIdNumber');
		$userInfo = PBXManager_Record_Model::getUserInfoWithNumber($callerNumber);

		if (!$userInfo) {
			$callerNumber = $temp[1];
			if (is_numeric($callerNumber)) {
				$userInfo = PBXManager_Record_Model::getUserInfoWithNumber($callerNumber);
			}
		}

		if ($userInfo) {
			// Outbound Call
			$request->set('Direction', 'outbound');

			if ($request->get('callerIdNumber') == $temp[1]) {
				$to = $request->get('callerIdName');
			} elseif ($request->get('callerIdNumber')) {
				$to = $request->get('callerIdNumber');
			} elseif ($request->get('callerId')) {
				$to = $request->get('callerId');
			}

			$request->set('to', $to);
			$customerInfo = PBXManager_Record_Model::lookUpRelatedWithNumber($to);
			$connector->handleStartupCall($request, $userInfo, $customerInfo);
		} else {
			// Inbound Call
			$request->set('Direction', 'inbound');
			$customerInfo = PBXManager_Record_Model::lookUpRelatedWithNumber($request->get('callerIdNumber'));
			$request->set('from', $request->get('callerIdNumber'));
			$connector->handleStartupCall($request, $userInfo, $customerInfo);
		}
	}

	/**
	 * Function to process Dial call request.
	 *
	 * @param \App\Request $request
	 *                              return response object
	 */
	public function processDialCall(\App\Request $request)
	{
		$connector = $this->getConnector();
		$connector->handleDialCall($request);
	}

	/**
	 * Function to process EndCall event.
	 *
	 * @param \App\Request $request
	 *                              return response object
	 */
	public function processEndCall(\App\Request $request)
	{
		$connector = $this->getConnector();
		$connector->handleEndCall($request);
	}

	/**
	 * Function to process Hangup call request.
	 *
	 * @param \App\Request $request
	 *                              return response object
	 */
	public function processHangupCall(\App\Request $request)
	{
		$connector = $this->getConnector();
		$connector->handleHangupCall($request);
	}

	/**
	 * Function to process recording.
	 *
	 * @param \App\Request $request
	 *                              return response object
	 */
	public function processRecording(\App\Request $request)
	{
		$connector = $this->getConnector();
		$connector->handleRecording($request);
	}
}
