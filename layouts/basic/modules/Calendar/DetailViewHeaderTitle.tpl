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
	<div class="col-md-4 margin0px">
		<div class="moduleIcon">
			<span class="detailViewIcon userIcon-{$MODULE}"></span>
		</div>
		<div class="paddingLeft5px pull-left">
			<span class="recordLabel font-x-x-large textOverflowEllipsis span" title="{$RECORD->getName()}">
				{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
					{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
					{if $FIELD_MODEL->getPermissions()}
						<span class="moduleColor_{$MODULE_NAME} {$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
					{/if}
				{/foreach}
			</span>
			{assign var=LINK value=$RECORD->get('link')}
			{if !empty($LINK)}
				<div class="paddingLeft5px">
					<span class="muted">{vtranslate('Relation',$MODULE_NAME)}: </span> <span>{$RECORD->getDisplayValue('link')}</span>
				</div>
			{/if}
			{assign var=PROCESS value=$RECORD->get('process')}
			{if !empty($PROCESS)}
				<div class="paddingLeft5px">
					<span class="muted">{vtranslate('Process',$MODULE_NAME)}: </span> <span>{$RECORD->getDisplayValue('process')}</span>
				</div>
			{/if}
			<div class="paddingLeft5px">
				<span class="muted">
					{vtranslate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
					{if $RECORD->get('shownerid') != ''}
						<br/>{vtranslate('Share with users',$MODULE_NAME)} {$RECORD->getDisplayValue('shownerid')}
					{/if}
				</span>
			</div>
		</div>
	</div>
{/strip}

