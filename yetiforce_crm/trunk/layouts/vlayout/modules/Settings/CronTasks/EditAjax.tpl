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
<div class="modelContainer">	
	<div class="modal-header">
		<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
		<h3>{vtranslate($RECORD_MODEL->get('name'), $QUALIFIED_MODULE)}</h3>
	</div>
	<form class="form-horizontal" id="cronJobSaveAjax" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="parent" value="Settings" />
		<input type="hidden" name="action" value="SaveAjax" />
		<input  type="hidden" name="record" value="{$RECORD}" />
		<input type="hidden" id="minimumFrequency" value="{$RECORD_MODEL->getMinimumFrequency()}" />
		<input type="hidden" id="frequency" name="frequency" value="" />

		<div class="modal-body tabbable">
			<div class="control-group">
				<div class="control-label">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</div>
				<div class="controls">
					<select class="chzn-select" name="status">
						<optgroup>
							<option {if $RECORD_MODEL->get('status') eq 1} selected="" {/if} value="1">{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}</option>
							<option {if $RECORD_MODEL->get('status') eq 0} selected="" {/if} value="0">{vtranslate('LBL_INACTIVE',$QUALIFIED_MODULE)}</option>
						</optgroup>
					</select>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					{vtranslate('Frequency',$QUALIFIED_MODULE)}
				</div>
				<div class="controls row-fluid">
					{assign var=VALUES value=':'|explode:$RECORD_MODEL->getDisplayValue('frequency')}
					{if $VALUES[0] == '00' && $VALUES[1] == '00'}
						{assign var=MINUTES value="true"}
						{assign var=FIELD_VALUE value=$VALUES[1]}
					{elseif $VALUES[0] == '00'}
						{assign var=MINUTES value="true"}
						{assign var=FIELD_VALUE value=$VALUES[1]}
					{elseif $VALUES[1] == '00'}
						{assign var=MINUTES value="false"}
						{assign var=FIELD_VALUE value=($VALUES[0])}
					{else}
						{assign var=MINUTES value="true"}
						{assign var=FIELD_VALUE value=($VALUES[0]*60)+$VALUES[1]}
					{/if}
					<input type="text" class="span2" value="{$FIELD_VALUE}" data-validation-engine="validate[required,funcCall[Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation]]" id="frequencyValue"/>&nbsp;
					<select class="chzn-select span5" id="time_format">
						<optgroup>
							<option value="mins" {if $MINUTES eq 'true'} selected="" {/if}>{vtranslate(LBL_MINUTES,$QUALIFIED_MODULE)}</option>
							<option value="hours" {if $MINUTES eq 'false'}selected="" {/if}>{vtranslate(LBL_HOURS,$QUALIFIED_MODULE)}</option>
						</optgroup>
					</select>
				</div>	
			</div>
			<div class="alert alert-info">{vtranslate($RECORD_MODEL->get('description'),$QUALIFIED_MODULE)}</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
</div>		
{/strip}	