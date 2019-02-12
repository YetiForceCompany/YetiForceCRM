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
	<div class="tpl-Settings-Workflows-Tasks-VTEntityMethodTask row">
		<div class="col-md-2">{\App\Language::translate('LBL_METHOD_NAME',$QUALIFIED_MODULE)} :</div>
		<div class="col-md-8">
			{assign var=ENTITY_METHODS value=$WORKFLOW_MODEL->getEntityMethods()}
			{if empty($ENTITY_METHODS)}
				<div class="alert alert-info">{\App\Language::translate('LBL_NO_METHOD_IS_AVAILABLE_FOR_THIS_MODULE',$QUALIFIED_MODULE)}</div>
			{else}
				<select name="methodName" class="select2">
					{foreach from=$ENTITY_METHODS item=METHOD}
						<option {if isset($TASK_OBJECT->methodName) && $TASK_OBJECT->methodName eq $METHOD}selected="" {/if}
								value="{$METHOD}">{\App\Language::translate($METHOD,$QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			{/if}
		</div>
	</div>
{/strip}	