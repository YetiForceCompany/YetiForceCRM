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
{assign var="_DefaultLoginTemplate" value=\App\Layout::getTemplatePath('Login.Default.tpl', 'Users')}
{assign var="_CustomLoginTemplate" value=\App\Layout::getTemplatePath('Login.Custom.tpl', 'Users')}
{assign var="_CustomLoginTemplateFullPath" value="layouts/basic/$_CustomLoginTemplate"}

{if file_exists($_CustomLoginTemplateFullPath)}
	{include file=$_CustomLoginTemplate}
{else}
	{include file=$_DefaultLoginTemplate}
{/if}
