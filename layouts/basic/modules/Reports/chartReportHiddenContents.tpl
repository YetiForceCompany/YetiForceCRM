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
<select id="groupbyfield_element">
	<option value="">{vtranslate('LBL_NONE',$MODULE)}</option>
	{foreach key=PRIMARY_MODULE_NAME item=PRIMARY_MODULE from=$PRIMARY_MODULE_FIELDS}
		{foreach key=BLOCK_LABEL item=BLOCK from=$PRIMARY_MODULE}
			<optgroup label='{vtranslate($PRIMARY_MODULE_NAME,$MODULE)}-{vtranslate($BLOCK_LABEL,$PRIMARY_MODULE_NAME)}'>
				{foreach key=FIELD_KEY item=FIELD_LABEL from=$BLOCK}
					{assign var=FIELD_INFO value=explode(':', $FIELD_KEY)}
					{if $FIELD_INFO[4] eq 'D' or $FIELD_INFO[4] eq 'DT'}
						<option value="{$FIELD_KEY}:Y">{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)} ({vtranslate('LBL_YEAR', $PRIMARY_MODULE_NAME)})</option>
						<option value="{$FIELD_KEY}:MY">{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)} ({vtranslate('LBL_MONTH', $PRIMARY_MODULE_NAME)})</option>
						<option value="{$FIELD_KEY}">{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)}</option>
					{else if $FIELD_INFO[4] neq 'I' and $FIELD_INFO[4] neq 'N' and $FIELD_INFO[4] neq 'NN'}
						<option value="{$FIELD_KEY}">{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)}</option>
					{/if}
				{/foreach}
			</optgroup>
		{/foreach}
	{/foreach}
	{foreach key=SECONDARY_MODULE_NAME item=SECONDARY_MODULE from=$SECONDARY_MODULE_FIELDS}
		{foreach key=BLOCK_LABEL item=BLOCK from=$SECONDARY_MODULE}
			<optgroup label='{vtranslate($SECONDARY_MODULE_NAME,$MODULE)}-{vtranslate($BLOCK_LABEL,$SECONDARY_MODULE_NAME)}'>
				{foreach key=FIELD_KEY item=FIELD_LABEL from=$BLOCK}
					{assign var=FIELD_INFO value=explode(':', $FIELD_KEY)}
					{if $FIELD_INFO[4] eq 'D' or $FIELD_INFO[4] eq 'DT'}
						<option value="{$FIELD_KEY}:Y">{vtranslate($SECONDARY_MODULE_NAME, $SECONDARY_MODULE_NAME)} {vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)} ({vtranslate('LBL_YEAR', $SECONDARY_MODULE_NAME)})</option>
						<option value="{$FIELD_KEY}:MY">{vtranslate($SECONDARY_MODULE_NAME, $SECONDARY_MODULE_NAME)} {vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)} ({vtranslate('LBL_MONTH', $SECONDARY_MODULE_NAME)})</option>
						<option value="{$FIELD_KEY}">{vtranslate($SECONDARY_MODULE_NAME, $SECONDARY_MODULE_NAME)} {vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)}</option>
					{else if $FIELD_INFO[4] neq 'I' and $FIELD_INFO[4] neq 'N' and $FIELD_INFO[4] neq 'NN'}
						<option value="{$FIELD_KEY}">{vtranslate($SECONDARY_MODULE_NAME, $SECONDARY_MODULE_NAME)} {vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)}</option>
					{/if}
				{/foreach}
			</optgroup>
		{/foreach}
	{/foreach}
</select>

<select id="datafields_element">
	<option value='count(*)'>{vtranslate('LBL_RECORD_COUNT', $MODULE)}</option>
	{foreach key=CALCULATION_FIELDS_MODULE_LABEL item=CALCULATION_FIELDS_MODULE from=$CALCULATION_FIELDS}
		<optgroup label="{vtranslate($CALCULATION_FIELDS_MODULE_LABEL, $CALCULATION_FIELDS_MODULE_LABEL)}">
		{foreach key=CALCULATION_FIELD_KEY item=CALCULATION_FIELD_TRANSLATED_LABEL from=$CALCULATION_FIELDS_MODULE}
			<option value="{$CALCULATION_FIELD_KEY}">{$CALCULATION_FIELD_TRANSLATED_LABEL}</option>
		{/foreach}
		</optgroup>
	{/foreach}
</select>