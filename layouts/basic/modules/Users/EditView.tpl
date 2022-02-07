{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Users-EditView -->
	{assign var=QUALIFIED_MODULE value={$MODULE_NAME}}
	{include file=\App\Layout::getTemplatePath('EditViewBlocks.tpl', $MODULE)}
	<input type="hidden" name="isPreference" value="{$IS_PREFERENCE}" />
	<input type="hidden" name="timeFormatOptions" data-value="{\App\Purifier::encodeHtml($DAY_STARTS)}" />
	{include file=\App\Layout::getTemplatePath('EditViewActions.tpl', $MODULE)}
	<!-- /tpl-Users-EditView -->
{/strip}
