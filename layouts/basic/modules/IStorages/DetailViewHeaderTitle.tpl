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
	<div class="col-xs-12 col-sm-12 col-md-4">
		<div class="moduleIcon">
			<span class="hierarchy">

			</span>
			<span class="detailViewIcon cursorPointer userIcon-{$MODULE}"></span>
		</div>
		<div class="paddingLeft5px">
			<h4 class="recordLabel marginbottomZero pushDown" title="{$RECORD->getName()}">
				{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
					{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
					{if $FIELD_MODEL->getPermissions()}
						<span class="moduleColor_{$MODULE_NAME} {$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>
					{/if}
				{/foreach}
			</h4>
			<span class="muted">
				{vtranslate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
				{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
				{if $SHOWNERS != ''}
					<br/>{vtranslate('Share with users',$MODULE_NAME)} {$SHOWNERS}
				{/if}
				{if $RECORD->get('accounttype') != ''}
					<br/>{vtranslate('Type',$MODULE_NAME)}: {vtranslate($RECORD->get('accounttype'),$MODULE_NAME)}
				{/if}
			</span>
		</div>
	</div>
	{include file='DetailViewHeaderFields.tpl'|@vtemplate_path:$MODULE_NAME}
{/strip}
