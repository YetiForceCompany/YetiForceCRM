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
<div class="formActionsPanel">
	<button class="btn btn-primary saveAndComplete" type="button">
		<span class="fas fa-check"></span>&nbsp;&nbsp;
		<strong>{\App\Language::translate('LBL_SAVE_AND_CLOSE', $MODULE)}</strong>
	</button> 
	<button class="btn btn-success" type="submit">
		<span class="fas fa-check"></span>&nbsp;&nbsp;
		<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
	</button>&nbsp;&nbsp;
	<button class="btn btn-warning" type="reset" onclick="javascript:window.history.back();">
		<span class="fas fa-times"></span>&nbsp;&nbsp;
		<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong></button>
	{foreach item=LINK from=$EDITVIEW_LINKS['EDIT_VIEW_HEADER']}
		{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='editViewHeader'}
		&nbsp;&nbsp;
	{/foreach}
</div>
</form>
</div>
</div>
{/strip}
