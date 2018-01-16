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
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
		<button class="btn btn-success margin-right5px" type="submit">
			<span class="glyphicon glyphicon-ok margin-right5px" aria-hidden="true"></span>
			<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
		</button>
		<button class="btn btn-warning" type="reset" onclick="javascript:window.history.back();">
			<span class="glyphicon glyphicon-remove margin-right5px"></span>
			<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
		</button>
		{foreach item=LINK from=$EDITVIEW_LINKS['EDIT_VIEW_HEADER']}
			{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='editViewHeader'}
			&nbsp;&nbsp;
		{/foreach}
	</div>
</form>
</div>
</div>
{/strip}
