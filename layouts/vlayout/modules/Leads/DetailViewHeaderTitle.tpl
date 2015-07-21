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
	<input type="hidden" id="conversion_available_status" value="{Vtiger_Util_Helper::toSafeHTML($CONVERSION_AVAILABLE_STATUS)}">
	{assign var=IMAGE value=$MODULE_NAME|cat:'48.png'}
	{if file_exists( vimage_path($IMAGE) )}
		<span class="pull-left spanModuleIcon moduleIcon{$MODULE_NAME}">
			<span class="moduleIcon">
				<img src="{vimage_path($IMAGE)}" class="summaryImg" alt="{vtranslate($MODULE, $MODULE)}" />
			</span>
		</span>
	{/if}
    <div class="col-xs-10 col-sm-9 col-md-8 margin0px">
        <div class="row">
            <h4 class="recordLabel pushDown marginbottomZero" title="{$RECORD->getName()}">
                {assign var=COUNTER value=0}
                {foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
                    {if $FIELD_MODEL->getPermissions()}
                        <span class="moduleColor_{$MODULE_NAME} {$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>
                    {if $COUNTER eq 0 && ($RECORD->get($NAME_FIELD))}&nbsp;{assign var=COUNTER value=$COUNTER+1}{/if}
                {/if}
            {/foreach}
            </h4>
        </div>
        <div class="row paddingLeft5px">
            <span class="designation_label">{$RECORD->getDisplayValue('designation')}</span>
            {if $RECORD->getDisplayValue('designation') && $RECORD->getDisplayValue('company')}
                &nbsp;{vtranslate('LBL_AT')}&nbsp;     
            {/if}
            <span class="company_label">{$RECORD->get('company')}</span>
        </div>
		<div class="row paddingLeft5px">
			<span class="muted">
				{vtranslate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
				{if $RECORD->get('shownerid') != ''}
				<br/>{vtranslate('Share with users',$MODULE_NAME)} {$RECORD->getDisplayValue('shownerid')}
				{/if}
			</span>
		</div>
    </div>
{/strip}
