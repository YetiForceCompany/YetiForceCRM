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
// <--------   YetiForce Sp. z o.o.   -------->
class yt_new_user extends rcube_plugin {

	function init() {
		$this->add_hook('login_after', array($this, 'login_after'));
	}

	function login_after($args) {
		$rcmail = rcmail::get_instance();
		$pass = rcube_utils::get_input_value('_pass', rcube_utils::INPUT_POST);
		$sql = "UPDATE " . $rcmail->db->table_name('users') . " SET password = ? WHERE user_id = ?";
		call_user_func_array(array($rcmail->db, 'query'), array_merge(array($sql), array($pass, $rcmail->get_user_id())));
		$rcmail->db->affected_rows();
		return $args;
	}

}
