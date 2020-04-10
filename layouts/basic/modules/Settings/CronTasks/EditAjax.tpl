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
	<div class="tpl-Settings-CronTasks-EditAjax modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="yfi yfi-full-editing-view mr-2 mt-2"></span>
						{\App\Language::translate($RECORD_MODEL->get('name'), $QUALIFIED_MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
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
							<label class="col-sm-3 col-form-label">{\App\Language::translate('LBL_STATUS',$QUALIFIED_MODULE)}</label>
							<div class="col-sm-8 controls">
								<select class="select2 form-control" name="status">
									<optgroup>
										<option {if $RECORD_MODEL->get('status') eq 1} selected="" {/if} value="1">{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}</option>
										<option {if $RECORD_MODEL->get('status') eq 0} selected="" {/if} value="0">{\App\Language::translate('LBL_INACTIVE',$QUALIFIED_MODULE)}</option>
									</optgroup>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 col-form-label">
								{\App\Language::translate('Frequency',$QUALIFIED_MODULE)}
							</label>
							<div class="controls row col-sm-8">
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
								<div class="col-sm-4 paddingLRZero">
									<input type="text" class="form-control" value="{$FIELD_VALUE}" data-validation-engine="validate[required,funcCall[Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation]]" id="frequencyValue" /></div>
								<div class="col-sm-8 paddingLRZero">
									<select class="select2 form-control" id="time_format">
										<optgroup>
											<option value="mins" {if $MINUTES eq 'true'} selected="" {/if}>{\App\Language::translate(LBL_MINUTES,$QUALIFIED_MODULE)}</option>
											<option value="hours" {if $MINUTES eq 'false'}selected="" {/if}>{\App\Language::translate(LBL_HOURS,$QUALIFIED_MODULE)}</option>
										</optgroup>
									</select>
								</div>
							</div>
						</div>
						{if $RECORD_MODEL->get('description') neq ''}
							<div class="alert alert-info">{\App\Language::translate($RECORD_MODEL->get('description'),$QUALIFIED_MODULE)}</div>
						{/if}
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
