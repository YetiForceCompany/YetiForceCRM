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
		<span class="pull-left spanModuleIcon moduleIcon{$MODULE_NAME}">
			<span class="moduleIcon">
				<img src="{vimage_path($IMAGE)}" class="summaryImg" alt="{vtranslate($MODULE, $MODULE)}" />
			</span>
		</span>
	{/if}
    <div class="col-xs-10 col-sm-9 col-md-10 margin0px">
        <div class="row">
            <h4 class="recordLabel margin0px" title="{$RECORD->getName()}">
                {foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
                    {if $FIELD_MODEL->getPermissions()}
                        <span class="moduleColor_{$MODULE_NAME} {$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
                    {/if}
                {/foreach}
            </h4>
        </div>
        {assign var=RELATED_TO value=$RECORD->get('parent_id')}
        {if !empty($RELATED_TO)}
            <div class="row paddingLeft5px">
				<span class="muted"></span>
				<h5 class="margin0px"><span class="">{$RECORD->getDisplayValue('parent_id')}</span></h5>
            </div>
        {/if}
        {assign var=PRIORITY value=$RECORD->get('ticketpriorities')}
        {if !empty($PRIORITY)}
            <div class="row paddingLeft5px">
                <span class="muted">{vtranslate('Priority',$MODULE_NAME)} - </span>
                {$RECORD->getDisplayValue('ticketpriorities')}
            </div>
        {/if}
		<div class="row">
			<div class="muted paddingLeft5px">
				{vtranslate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
				{if $RECORD->get('shownerid') != ''}
				<br/>{vtranslate('Share with users',$MODULE_NAME)}: {$RECORD->getDisplayValue('shownerid')}
				{/if}
			</div>
		</div>
    </div>
{/strip}
