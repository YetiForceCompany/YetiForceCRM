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
<div class="modelContainer modal fade" tabindex="-1">	
	<div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
				<h3 class="modal-title">{vtranslate($RECORD_MODEL->get('name'), $QUALIFIED_MODULE)}</h3>
			</div>
			<form class="form-horizontal" id="cronJobSaveAjax" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="SaveAjax" />
				<input  type="hidden" name="record" value="{$RECORD}" />
				<input type="hidden" id="minimumFrequency" value="{$RECORD_MODEL->getMinimumFrequency()}" />
				<input type="hidden" id="frequency" name="frequency" value="" />

				<div class="modal-body tabbable">
					<div class="form-group">
						<label class="col-sm-3 control-label">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</label>
						<div class="col-sm-8 controls">
							<select class="chzn-select form-control" name="status">
								<optgroup>
									<option {if $RECORD_MODEL->get('status') eq 1} selected="" {/if} value="1">{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}</option>
									<option {if $RECORD_MODEL->get('status') eq 0} selected="" {/if} value="0">{vtranslate('LBL_INACTIVE',$QUALIFIED_MODULE)}</option>
								</optgroup>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">
							{vtranslate('Frequency',$QUALIFIED_MODULE)}
						</label>
						<div class="controls col-sm-8">
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
							<div class="col-xs-6 col-sm-4 paddingLRZero">
								<input type="text" class="form-control" value="{$FIELD_VALUE}" data-validation-engine="validate[required,funcCall[Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation]]" id="frequencyValue"/></div>
							<div class="col-xs-6 col-sm-8 paddingLRZero">
								<select class="chzn-select form-control" id="time_format">
									<optgroup>
										<option value="mins" {if $MINUTES eq 'true'} selected="" {/if}>{vtranslate(LBL_MINUTES,$QUALIFIED_MODULE)}</option>
										<option value="hours" {if $MINUTES eq 'false'}selected="" {/if}>{vtranslate(LBL_HOURS,$QUALIFIED_MODULE)}</option>
									</optgroup>
								</select>
							</div>
						</div>	
					</div>
					{if $RECORD_MODEL->get('description') neq ''}
						<div class="alert alert-info">{vtranslate($RECORD_MODEL->get('description'),$QUALIFIED_MODULE)}</div>
					{/if}
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>		
	</div>		
</div>		
{/strip}	
