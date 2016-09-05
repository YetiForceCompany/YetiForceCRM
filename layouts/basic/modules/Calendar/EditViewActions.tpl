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
       <div>
            <div class="pull-right">
				<button class="btn btn-primary saveAndComplete" type="button">{vtranslate('LBL_SAVE_AND_CLOSE', $MODULE)}</button> 
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-warning" type="reset" onclick="javascript:window.history.back();"><strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong></button>
			</div>
			<div class="clearfix"></div>
        </div>
		<br>
    </form>
</div>
{/strip}
