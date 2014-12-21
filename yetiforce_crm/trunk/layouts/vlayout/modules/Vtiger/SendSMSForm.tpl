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
<div id="sendSmsContainer" class='modelContainer'>
	<div class="modal-header contentsBackground">
        <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
		<h3>{vtranslate('LBL_SEND_SMS_TO_SELECTED_NUMBERS', $MODULE)}</h3>
	</div>
	<form class="form-horizontal" id="massSave" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
		<input type="hidden" name="action" value="MassSaveAjax" />
		<input type="hidden" name="viewname" value="{$VIEWNAME}" />
		<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
		<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
        <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
        <input type="hidden" name="operator" value="{$OPERATOR}" />
        <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
        <input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}' />
               
		<div class="modal-body tabbable">
			<div>
				<span><strong>{vtranslate('LBL_STEP_1',$MODULE)}</strong></span>
				&nbsp;:&nbsp;
				{vtranslate('LBL_SELECT_THE_PHONE_NUMBER_FIELDS_TO_SEND',$MODULE)}
			</div>
			<select name="fields[]" data-placeholder="{vtranslate('LBL_ADD_MORE_FIELDS',$MODULE)}" multiple class="chzn-select">
				<optgroup>
					{foreach item=PHONE_FIELD from=$PHONE_FIELDS}
						{assign var=PHONE_FIELD_NAME value=$PHONE_FIELD->get('name')}
						<option value="{$PHONE_FIELD_NAME}">
							{if !empty($SINGLE_RECORD)}
								{assign var=FIELD_VALUE value=$SINGLE_RECORD->get($PHONE_FIELD_NAME)}
							{/if}
							{vtranslate($PHONE_FIELD->get('label'), $SOURCE_MODULE)}{if !empty($FIELD_VALUE)} ({$FIELD_VALUE}){/if}
						</option>
					{/foreach}
				</optgroup>
			</select>
			<hr>
			<div>
				<span><strong>{vtranslate('LBL_STEP_2',$MODULE)}</strong></span>
				&nbsp;:&nbsp;
				{vtranslate('LBL_TYPE_THE_MESSAGE',$MODULE)}&nbsp;(&nbsp;{vtranslate('LBL_SMS_MAX_CHARACTERS_ALLOWED',$MODULE)}&nbsp;)
			</div>
			<textarea class="input-xxlarge" name="message" id="message" placeholder="{vtranslate('LBL_WRITE_YOUR_MESSAGE_HERE', $MODULE)}"></textarea>
		</div>
		<div class="modal-footer">
			<div class=" pull-right cancelLinkContainer">
				<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</div>
			<button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_SEND', $MODULE)}</strong></button>
		</div>
	</form>
</div>
{/strip}
