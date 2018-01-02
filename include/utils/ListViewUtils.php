<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header: /cvsroot/vtigercrm/vtiger_crm/include/utils/ListViewUtils.php,v 1.32 2006/02/03 06:53:08 mangai Exp $
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */

require_once('include/database/PearDatabase.php');
require_once('include/utils/CommonUtils.php'); //new
require_once('user_privileges/default_module_view.php'); //new
require_once('include/utils/UserInfoUtil.php');

function popup_decode_html($str)
{
	$defaultCharset = AppConfig::main('default_charset');
	$slashes_str = \vtlib\Functions::fromHtmlPopup($str);
	$slashes_str = htmlspecialchars($slashes_str, ENT_QUOTES, $defaultCharset);
	return \App\Purifier::decodeHtml(\vtlib\Functions::br2nl($slashes_str));
}
