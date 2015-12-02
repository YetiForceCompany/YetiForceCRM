{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}
 
<style>
.fieldDetailsForm .zeroOpacity{
display: none;
}
.visibility{
visibility: hidden;
}
.marginLeft20{
margin-left: 20px;
}
.marginRight20{
	margin-right: 20px;
}
.marginTop5{
	margin-top: 5px;
}
.paddingNoTop20{
padding: 20px 20px 20px 20px;
}
.paddingAll10{
padding: 10px;
}.paddingAll5{
padding: 5px;
}
.marginLRZero{
	margin-left: 0;
	margin-right: 0;
}
</style>
<div class="" id="widgetsManagementEditorContainer">
	<div class="widget_header row">
		<div class="col-md-12">
			<h3>{vtranslate('LBL_PUBLIC_HOLIDAY', $QUALIFIED_MODULE)}</h3>
			{vtranslate('LBL_PUBLIC_HOLIDAY_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>

	<div class="contents tabbable">
		<div class="tab-content layoutContent publicHolidayContent themeTableColor overflowVisible">
		<hr>
		<div class="tab-pane active" id="layoutDashBoards">
			<div class="btn-toolbar">
				<button type="button" class="btn btn-success addDateWindow"><span class="glyphicon glyphicon-plus"></span>&nbsp;{vtranslate('LBL_ADD_HOLIDAY', $QUALIFIED_MODULE)}</button>
			</div>
			<div id="moduleBlocks">
				<div style="border-radius: 4px 4px 0px 0px;background: white;" class="editFieldsTable block_1 marginBottom10px border1px pushDown">
					<div class="row layoutBlockHeader marginLRZero">
						<div class="col-md-12" style="padding: 10px 0;">
							<span class="marginLeft20">
								<strong>{vtranslate('LBL_HOLIDAY_LIST', $QUALIFIED_MODULE)}:</strong>
							</span>
							<span class="pull-right marginRight20">
								<strong>{vtranslate('LBL_DATE_RANGE', $QUALIFIED_MODULE)}:</strong>
								<input type="text" class="dateField dateFilter marginbottomZero" data-date-format="yyyy-mm-dd" data-calendar-type="range" value="{$DATE}" />
							</span>
						</div>
					</div>
					<div style="padding:5px;min-height: 27px" class="blockFieldsList row marginLRZero">
						<ul class="col-md-12 holidayList">
							{foreach item=HOLIDAY from=$HOLIDAYS}
							<li>
								<div data-holiday-id="{$HOLIDAY['id']}" data-holiday-type="{$HOLIDAY['type']}" data-holiday-name="{$HOLIDAY['name']}" data-holiday-date="{Vtiger_Functions::currentUserDisplayDate($HOLIDAY['date'])}" class="opacity holidayElement marginLeftZero border1px">
									<div class="row paddingAll5">
										<div style="word-wrap: break-word;" class="col-md-10 ">
											<span class="fieldLabel marginLeft20 col-md-2">{Vtiger_Functions::currentUserDisplayDate($HOLIDAY['date'])}</span>
											<span class="fieldLabel marginLeft20 col-md-2">{vtranslate($HOLIDAY['day'], $QUALIFIED_MODULE)}</span>
											<span class="marginLeft20 col-md-3">{vtranslate($HOLIDAY['name'], $QUALIFIED_MODULE)}</span>
											<span class="marginLeft20 col-md-3">{vtranslate($HOLIDAY['type'], $QUALIFIED_MODULE)}</span>
										</div>
										<span class="btn-group pull-right marginRight20 actions">
											<a data-holiday-id="{$HOLIDAY['id']}" data-toggle="dropdown" class="dropdown-toggle editHoliday" href="javascript:void(0)">
												<span title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span>
											</a>
											<a data-holiday-id="{$HOLIDAY['id']}" class="deleteHoliday" href="javascript:void(0)">
												<span title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span>
											</a>
										</span>
									</div>
								</div>
							</li>
							{/foreach}
						</ul>
					</div>
				</div>
			</div>
			{* copy elements hide *}
			<div class="modal addDateWindowModal fade publicHolidayModal" tabindex="-1">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header contentsBackground">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3 class="modal-title">{vtranslate('LBL_ADD_NEW_HOLIDAY', $QUALIFIED_MODULE)}</h3>
						</div>
						<form class="form-horizontal addDateWindowForm">
							<input type="hidden" name="holidayId" value="" />
							<div class="modal-body">
								<div class="form-group">
									<div class="col-sm-3 control-label">
										<span class="redColor">*</span>
										<span>{vtranslate('LBL_DATE', $QUALIFIED_MODULE)}</span>
									</div>
									<div class="col-sm-6 controls">
										<input type="text" name="holidayDate" class="dateField form-control" data-date-format="{$CURRENTUSER->column_fields['date_format']}" value="{Vtiger_Functions::currentUserDisplayDate(date('Y-m-d'))}" required >

									</div>
								</div>
								<div class="form-group">
									<div class="col-sm-3 control-label">
										<span class="redColor">*</span>
										<span>{vtranslate('LBL_HOLIDAY_TYPE', $QUALIFIED_MODULE)}</span>
									</div>
									<div class="col-sm-6 controls">
										 <select name="holidayType" class="form-control" required data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" >
											<option value="national">{vtranslate('LBL_NATIONAL', $QUALIFIED_MODULE)}</option>
											<option value="ecclesiastical">{vtranslate('LBL_ECCLESIASTICAL', $QUALIFIED_MODULE)}</option>
										</select> 
									</div>
								</div>
								<div class="form-group">
									<div class="col-sm-3 control-label">
										<span class="redColor">*</span>
										<span>{vtranslate('LBL_DAY_NAME', $QUALIFIED_MODULE)}</span>
									</div>
									<div class="col-sm-6 controls">
										<input type="text" name="holidayName" value="" class="form-control" placeholder="{vtranslate('LBL_DAY_NAME_DESC', $QUALIFIED_MODULE)}" required data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
									</div>
								</div>
							</div>
							{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
						</form>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>
</div>
