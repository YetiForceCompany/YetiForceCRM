{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	{assign var=IMAGE value=$MODULE_NAME|cat:'48.png'}
	{if file_exists( vimage_path($IMAGE) )}
		<span class="span2 spanModuleIcon moduleIcon{$MODULE_NAME}">
			<span class="moduleIcon">
				<img src="{vimage_path($IMAGE)}" class="summaryImg" />
			</span>
		</span>
	{/if}
    <span class="span10 margin0px">
        <span class="row-fluid">
            <h4 class="recordLabel" title="{$RECORD->getName()}">
                {foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
                    {if $FIELD_MODEL->getPermissions()}
                        <span class="moduleColor_{$MODULE_NAME} {$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
                    {/if}
                {/foreach}
            </h4>
        </span>
        {assign var=RELATED_TO value=$RECORD->get('parent_id')}
        {if !empty($RELATED_TO)}
            <span class="row-fluid">
				<span class="muted"></span>
				<h5><span class="">{$RECORD->getDisplayValue('parent_id')}</span></h5>
            </span>
        {/if}
        {assign var=PRIORITY value=$RECORD->get('ticketpriorities')}
        {if !empty($PRIORITY)}
            <span class="row-fluid">
                <span class="muted">{vtranslate('Priority',$MODULE_NAME)} - </span>
                {$RECORD->getDisplayValue('ticketpriorities')}
            </span>
        {/if}
		<span class="row-fluid">
			<span class="muted">
				{vtranslate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
				{if $RECORD->get('shownerid') != ''}
				<br/>{vtranslate('Share with users',$MODULE_NAME)} {$RECORD->getDisplayValue('shownerid')}
				{/if}
			</span>
		</span>
    </span>
{/strip}