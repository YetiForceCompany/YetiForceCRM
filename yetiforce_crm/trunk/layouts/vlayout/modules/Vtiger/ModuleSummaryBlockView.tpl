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
	<div class="recordDetails">
		<div>
			<h4> {vtranslate('LBL_RECORD_SUMMARY',$MODULE_NAME)}	</h4>
			<hr>
		</div>
        {foreach item=SUMMARY_CATEGORY from=$SUMMARY_INFORMATION}
            <div class="row-fluid textAlignCenter roundedCorners">
                {foreach item=FIELD_VALUE from=$SUMMARY_CATEGORY}
                    <span class="well squeezedWell span3" data-reference="{$FIELD_VALUE.reference}">
                        <div>
                            <label class="font-x-small">
                                {vtranslate($FIELD_VALUE.name,$MODULE_NAME)}
                            </label>
                        </div>
                        <div>
                            <label class="font-x-x-large">
                                {if !empty($FIELD_VALUE.data)}{$FIELD_VALUE.data}{else}0{/if}
                            </label>
                        </div>
                    </span>
                {/foreach}
            </div>
        {/foreach}
		{include file='SummaryViewContents.tpl'|@vtemplate_path}
	</div>
{/strip}