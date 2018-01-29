{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
	<div class="modal-footer">
		<button class="btn btn-success" type="submit" name="saveButton"><span class="glyphicon glyphicon-ok"></span>&nbsp;<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong></button>
		<button class="btn btn-warning" type="reset" data-dismiss="modal"><span class="fas fa-times"></span>&nbsp;<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong></button>
	</div>
{/strip}
