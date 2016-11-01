{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}
{strip}
<div class="" id="widgetsManagementEditorContainer">
	<div class="widget_header row">
		<div class="col-md-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_PUBLIC_HOLIDAY_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="contents tabbable">
		<div class="tab-content themeTableColor overflowVisible">
		<div class="tab-pane active" id="layoutDashBoards">
			<div class="btn-toolbar marginBottom10px">
				<button type="button" class="btn btn-success addDateWindow"><span class="glyphicon glyphicon-plus"></span>&nbsp;{vtranslate('LBL_ADD_HOLIDAY', $QUALIFIED_MODULE)}</button>
			</div>
			<div id="moduleBlocks">
				<div style="border-radius: 4px 4px 0px 0px;background: white;" class="editFieldsTable block_1 marginBottom10px border1px">
					<div class="row no-margin">
						<table class="table table-bordered layoutBlockHeader">
							<tr>
								<td>
									<div class="col-xs-12 col-sm-6 col-md-6 paddingLRZero">
										<h4>{vtranslate('LBL_HOLIDAY_LIST', $QUALIFIED_MODULE)}:</h4>
									</div>
									<div class="pull-right col-xs-12 col-sm-6 col-md-6 paddingLRZero">
										<div class="pull-right">
											<div class="col-xs-3 paddingTop10 paddingLRZero">
												<strong>{vtranslate('LBL_DATE_RANGE', $QUALIFIED_MODULE)}:</strong>
											</div>
											<div class="col-xs-8 col-xs-pull-1">
												<input type="text" class="dateField dateFilter marginbottomZero form-control" data-date-format="{$CURRENTUSER->get('date_format')}" data-calendar-type="range" value="{$DATE}" />
											</div>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>
					<table class="table tableRWD table-bordered ">
						<thead class='text-capitalize'>
						    <tr>
							<th><span class='marginLeft20'>{vtranslate('LBL_DATE', $QUALIFIED_MODULE)}</span></th>
							<th><span class='marginLeft20'>{vtranslate('LBL_DAY', $QUALIFIED_MODULE)}</span></th>
							<th><span class='marginLeft20'>{vtranslate('LBL_DAY_NAME', $QUALIFIED_MODULE)}</span></th>
							<th><span class='marginLeft20'>{vtranslate('LBL_HOLIDAY_TYPE', $QUALIFIED_MODULE)}</span></th>
							<th></th>
						    </tr>
						</thead>
						<tbody>
						{foreach item=HOLIDAY from=$HOLIDAYS}
							<tr class="holidayElement" data-holiday-id="{$HOLIDAY['id']}" data-holiday-type="{$HOLIDAY['type']}" data-holiday-name="{$HOLIDAY['name']}" data-holiday-date="{\App\Fields\DateTime::currentUserDisplayDate($HOLIDAY['date'])}">
								<td>
									<span class="fieldLabel marginLeft20">{\App\Fields\DateTime::currentUserDisplayDate($HOLIDAY['date'])}</span>
								</td>
								<td>
									<span class="fieldLabel marginLeft20">{vtranslate($HOLIDAY['day'], $QUALIFIED_MODULE)}</span>
								</td>
								<td>
									<span class="marginLeft20">{vtranslate($HOLIDAY['name'], $QUALIFIED_MODULE)}</span>
								</td>
								<td>
									<span class="marginLeft20">{vtranslate($HOLIDAY['type'], $QUALIFIED_MODULE)}</span>
								</td>
								<td>
									<div class='pull-right'>
										<a data-holiday-id="{$HOLIDAY['id']}" data-toggle="dropdown" class="dropdown-toggle editHoliday" href="javascript:void(0)">
											<span title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span>
										</a>
										<a data-holiday-id="{$HOLIDAY['id']}" class="deleteHoliday" href="javascript:void(0)">
											<span title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span>
										</a>
									</div>
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
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
										<span>{vtranslate('LBL_DATE', $QUALIFIED_MODULE)}</span>
										<span class="redColor">*</span>
									</div>
									<div class="col-sm-6 controls">
										<input type="text" name="holidayDate" class="dateField form-control" data-date-format="{$CURRENTUSER->column_fields['date_format']}" value="{\App\Fields\DateTime::currentUserDisplayDate(date('Y-m-d'))}" required >

									</div>
								</div>
								<div class="form-group">
									<div class="col-sm-3 control-label">
										<span>{vtranslate('LBL_HOLIDAY_TYPE', $QUALIFIED_MODULE)}</span>
										<span class="redColor">*</span>
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
										<span>{vtranslate('LBL_DAY_NAME', $QUALIFIED_MODULE)}</span>
										<span class="redColor">*</span>
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
{/strip}
