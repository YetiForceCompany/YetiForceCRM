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
		<div class="modal-dialog modal-full">
			<div class="modal-content">
				<form class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php">
					<div class="modal-header contentsBackground">
						<div class="pull-left">
							<h3 class="modal-title">{vtranslate('LBL_QUICK_CREATE', $MODULE)} {vtranslate($SINGLE_MODULE, $MODULE)}</h3>
						</div>
						<div class="pull-right quickCreateActions">
							{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
							<button class="btn btn-default" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>&nbsp;
							<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
							<button class="cancelLink  btn btn-warning" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
						</div>
						<div class="clearfix"></div>
					</div>
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField" value='{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="action" value="SaveAjax">
					<div class="quickCreateContent">
						<div class="modal-body row no-margin">
							<div class="massEditTable row no-margin">
								<div class="col-xs-12 paddingLRZero fieldRow">
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
										{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
										{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
										{assign var="refrenceListCount" value=count($refrenceList)}
										{if $COUNTER eq 2}
										</div>
										<div class="col-xs-12 paddingLRZero fieldRow">
											{assign var=COUNTER value=1}
										{else}
											{assign var=COUNTER value=$COUNTER+1}
										{/if}
										<div class="col-xs-12 col-md-6 fieldsLabelValue {$WIDTHTYPE} paddingLRZero">
											<div class="fieldLabel col-xs-12 col-sm-5">
												{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
												{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->get('label')}
												{if in_array($VIEW,$HELPINFO) && vtranslate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
													<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{htmlspecialchars(vtranslate($MODULE|cat:'|'|cat:$FIELD_MODEL->get('label'), 'HelpInfo'))}" data-original-title='{vtranslate($FIELD_MODEL->get("label"), $MODULE)}'><i class="glyphicon glyphicon-info-sign"></i></a>
												{/if}
												<label class="muted pull-left-xs pull-right-sm pull-right-lg">
													{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span>{/if}
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
												</label>
											</div>
											<div class="fieldValue col-xs-12 col-sm-7" >
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
											</div>
										</div>
									{/foreach}
								</div>
							</div>
						</div>
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
