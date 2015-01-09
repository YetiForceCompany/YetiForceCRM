<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
vimport('~~modules/Calendar/activityTypes/Events.php');
class Calendar_MultipleEvents_ActivityTypes{
	public function process($feed, $request, $start, $end, &$result, $userid = false,$color = null,$textColor = 'white') {
		foreach ($request->get('mapping') as $id=>$backgroundColorAndTextColor) {
			$userEvents = array();
			$colorComponents = explode(',',$backgroundColorAndTextColor);
			Calendar_Events_ActivityTypes::process($feed, $request, $start, $end, $userEvents ,$id, $colorComponents[0], $colorComponents[1]);
			$result[$id] = $userEvents;
		}
	}
}