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
	<span class="col-xs-6">
		<div class="">
			<select class="chzn-select col-xs-11 selectedSortFields form-control" title="{vtranslate('LBL_GROUP_BY',$MODULE)}">
				<option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
				{foreach key=PRIMARY_MODULE_NAME item=PRIMARY_MODULE from=$PRIMARY_MODULE_FIELDS}
					{foreach key=BLOCK_LABEL item=BLOCK from=$PRIMARY_MODULE}
						<optgroup label='{vtranslate($PRIMARY_MODULE_NAME,$MODULE)}-{vtranslate($BLOCK_LABEL,$PRIMARY_MODULE_NAME)}'>
							{foreach key=FIELD_KEY item=FIELD_LABEL from=$BLOCK}
								<option value="{$FIELD_KEY}"{if $FIELD_KEY eq $SELECTED_SORT_FIELD_KEY}selected=""{/if}>{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				{/foreach}
				{foreach key=SECONDARY_MODULE_NAME item=SECONDARY_MODULE from=$SECONDARY_MODULE_FIELDS}
					{foreach key=BLOCK_LABEL item=BLOCK from=$SECONDARY_MODULE}
						<optgroup label='{vtranslate($SECONDARY_MODULE_NAME,$MODULE)}-{vtranslate($BLOCK_LABEL,$SECONDARY_MODULE_NAME)}'>
							{foreach key=FIELD_KEY item=FIELD_LABEL from=$BLOCK}
								<option value="{$FIELD_KEY}"{if $FIELD_KEY eq $SELECTED_SORT_FIELD_KEY}selected=""{/if}>{vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				{/foreach}
			</select>
		</div>
	</span>
	<span class="col-xs-6">
		<div class="">
			<span class="col-xs-12 col-md-6">
				{assign var=ROW value='row_'|cat:$ROW_VAL}
				<div class="col-xs-12 col-md-6 paddingLRZero">
					<input style='margin:5px;' type="radio" name="{$ROW}" class="sortOrder" value="Ascending" {if $SELECTED_SORT_FIELD_VALUE eq Ascending} checked="" {/if} title="{vtranslate('LBL_ASCENDING',$MODULE)}" />&nbsp;<span>{vtranslate('LBL_ASCENDING',$MODULE)}</span>&nbsp;&nbsp;
				</div>
				<div class="col-xs-12 col-md-6 paddingLRZero">
					<input style='margin:5px;'type="radio" name="{$ROW}" class="sortOrder" value="Descending" {if $SELECTED_SORT_FIELD_VALUE eq Descending} checked="" {/if} title="{vtranslate('LBL_DESCENDING',$MODULE)}" />&nbsp;<span>{vtranslate('LBL_DESCENDING',$MODULE)}</span>
				</div>
			</span>
	</div>
	</span>
{/strip}
