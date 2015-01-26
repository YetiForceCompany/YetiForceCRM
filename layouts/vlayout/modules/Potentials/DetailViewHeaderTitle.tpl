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
    <span class="span8 margin0px">
        <span class="row-fluid">
            <h4 class="recordLabel pushDown" title="{$RECORD->getName()}">
                {foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
                    {if $FIELD_MODEL->getPermissions()}
                        <span class="moduleColor_{$MODULE_NAME} {$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
                    {/if}
                {/foreach}
            </h4>
        </span>
        {assign var=RELATED_TO value=$RECORD->get('related_to')}
        {if !empty($RELATED_TO)}
            <span class="row-fluid">
				<span class="muted"></span>
				<h5><span class="">{$RECORD->getDisplayValue('related_to')}</span></h5>
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
		{if $RECORD->get('sales_stage') != ''}
            <span class="row-fluid">
				<span class="muted">{vtranslate('Sales Stage',$MODULE_NAME)} </span>
				<span class="wrapper">{$RECORD->getDisplayValue('sales_stage')}</span>
            </span>
		{/if}
    </span>
{/strip}