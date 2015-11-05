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
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
					<h3 class="modal-title">{vtranslate('LBL_QUICK_CREATE', $MODULE)} {vtranslate($SINGLE_MODULE, $MODULE)}</h3>
				</div>
				<form class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php">
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField" value='{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="action" value="SaveAjax">
					<div class="quickCreateContent">
						<div class="modal-body">
							<table class="massEditTable table table-bordered">
								<tr>
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}

										{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
										{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
										{assign var="refrenceListCount" value=count($refrenceList)}
										{if $FIELD_MODEL->get('uitype') eq "19"}
											{if $COUNTER eq '1'}
												<td></td><td></td></tr><tr>
												{assign var=COUNTER value=0}
											{/if}
										{/if}
										{if $COUNTER eq 2}
										</tr><tr>
											{assign var=COUNTER value=1}
										{else}
											{assign var=COUNTER value=$COUNTER+1}
										{/if}
										<td class='fieldLabel {$WIDTHTYPE}'>
											{if $isReferenceField neq "reference"}<label class="muted pull-right">{/if}
												{if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference"} <span class="redColor">*</span> {/if}
												{if $isReferenceField eq "reference"}
													{if $refrenceListCount > 1}
														{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
														{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
														{if !empty($REFERENCED_MODULE_STRUCT)}
															{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
														{/if}
														<label class="muted textAlignRight">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
														<span class="pull-right">
															<select style="width: 150px;" class="chzn-select referenceModulesList form-control" id="referenceModulesList_{$FIELD_MODEL->get('id')}">
																<optgroup>
																	{foreach key=index item=value from=$refrenceList}
																		<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
																	{/foreach}
																</optgroup>
															</select>
														</span>		
													{else}
														<label class="muted pull-right">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
													{/if}
												{else}
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
												{/if}
												{if $isReferenceField neq "reference"}</label>{/if}
										</td>
										<td class="fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
										</td>
									{/foreach}
								</tr>
							</table>
						</div>
					</div>
					<div class="modal-footer quickCreateActions">
						{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
						<button class="cancelLink cancelLinkContainer pull-right btn btn-warning" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</button>
						<button class="btn btn-success test" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
						<button class="btn btn-default" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>
					</div>
					{if !empty($SOURCE_RELATED_FIELD)}
						{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_VALUE from=$SOURCE_RELATED_FIELD}
							<input type="hidden" name="{$RELATED_FIELD_NAME}" value='{$RELATED_FIELD_VALUE}' />
						{/foreach}
					{/if}
				</form>
			</div>
		</div>
	</div>
	<script>
		setTimeout(function () {
			Vtiger_Edit_Js("OSSTimeControl_QuickCreate_Js",{},
				{
					/*sumHours : function () {	
					 var sumeTime = this.differenceDays();
					 var hours = (Math.round( (sumeTime/3600000) * 100 ) / 100).toFixed(2);
					 return hours
					 },	
					 */
					differenceDays: function () {
						var firstDate = jQuery('input[name="date_start"]');
						var firstDateFormat = firstDate.data('date-format');
						var firstDateValue = firstDate.val();
						var secondDate = jQuery('input[name="due_date"]');
						var secondDateFormat = secondDate.data('date-format');
						var secondDateValue = secondDate.val();
						var firstTime = jQuery('input[name="time_start"]');
						var secondTime = jQuery('input[name="time_end"]');
						var firstTimeValue = firstTime.val();
						var secondTimeValue = secondTime.val();
						var firstDateTimeValue = firstDateValue + ' ' + firstTimeValue;
						var secondDateTimeValue = secondDateValue + ' ' + secondTimeValue;
						var firstDateInstance = Vtiger_Helper_Js.getDateInstance(firstDateTimeValue, firstDateFormat);
						var secondDateInstance = Vtiger_Helper_Js.getDateInstance(secondDateTimeValue, secondDateFormat);

						var timeBetweenDates = secondDateInstance - firstDateInstance;
						if (timeBetweenDates >= 0) {
							return timeBetweenDates;
						}
						return 'Error';
					},
					registerRecordPreSaveEvent: function () {
						var differenceDays = this.differenceDays();
						/*var sumHours = this.sumHours();

						 if(sumHours > 24){
						 var params = {
						 text: app.vtranslate('JS_HOURS_SHOULD_BE_SMALLER_THAN'),
						 type: 'error'
						 };
						 Vtiger_Helper_Js.showPnotify(params);
						 return false;

						 }*/

						if (differenceDays == 'Error') {
							var params = {
								text: app.vtranslate('JS_DATE_SHOULD_BE_GREATER_THAN'),
								type: 'error'
							};
							Vtiger_Helper_Js.showPnotify(params);
							return false;
						}
					},
					registerEvents: function () {
						this._super();
						this.registerRecordPreSaveEvent();
					}
				}
			);
			jQuery(document).ready(function () {
				var currencyInstance = new OSSTimeControl_QuickCreate_Js();
				$(".btn-success").click(function () {
					currencyInstance.registerRecordPreSaveEvent();
				});
			})
		}, 1000);
	</script>
{/strip}
