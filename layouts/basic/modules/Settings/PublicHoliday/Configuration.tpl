{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-PublicHoliday-Configuration" id="widgetsManagementEditorContainer">
		<div class="widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="contents tabbable">
			<div class="tab-content themeTableColor overflowVisible">
				<div class="tab-pane active" id="layoutDashBoards">
					<button type="button" class="btn btn-success addDateWindow my-2">
						<span class="fas fa-plus"></span>&nbsp;{\App\Language::translate('LBL_ADD_HOLIDAY', $QUALIFIED_MODULE)}
					</button>
					<div id="moduleBlocks">
						<div class="editFieldsTable block_1 mb-3">
							<div class="row no-gutters border border-bottom-0 bg-light p-2">
								<div class="col-12 col-sm-12 col-md-7">
									<h4>{\App\Language::translate('LBL_HOLIDAY_LIST', $QUALIFIED_MODULE)}</h4>
								</div>
								<div class="col-12 col-sm-12 col-md-5">
									<div class="d-flex justify-content-end">
										<label class="d-block align-self-center w-50 text-left text-md-right mb-0 mr-2 font-weight-bold">
											{\App\Language::translate('LBL_DATE_RANGE', $QUALIFIED_MODULE)}:
										</label>
										<input type="text"
											   class="d-block dateRangeField dateFilter form-control text-center"
											   data-date-format="{$USER_MODEL->get('date_format')}"
											   data-calendar-type="range" value="{$DATE}"/>
									</div>
								</div>
							</div>
							<table class="table tableRWD table-bordered ">
								<thead class='text-capitalize text-center'>
								<tr>
									<th>{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</th>
									<th>{\App\Language::translate('LBL_DAY', $QUALIFIED_MODULE)}</th>
									<th>{\App\Language::translate('LBL_DAY_NAME', $QUALIFIED_MODULE)}</th>
									<th>{\App\Language::translate('LBL_HOLIDAY_TYPE', $QUALIFIED_MODULE)}</th>
									<th></th>
								</tr>
								</thead>
								<tbody>
								{foreach item=HOLIDAY from=$HOLIDAYS}
									<tr class="holidayElement text-center" data-holiday-id="{$HOLIDAY['id']}"
										data-holiday-type="{$HOLIDAY['type']}" data-holiday-name="{$HOLIDAY['name']}"
										data-holiday-date="{\App\Fields\Date::formatToDisplay($HOLIDAY['date'])}">
										<td>
											<span>{\App\Fields\Date::formatToDisplay($HOLIDAY['date'])}</span>
										</td>
										<td>
											<span>{\App\Language::translate($HOLIDAY['day'], $QUALIFIED_MODULE)}</span>
										</td>
										<td>
											<span>{\App\Language::translate($HOLIDAY['name'], $QUALIFIED_MODULE)}</span>
										</td>
										<td>
											<span>{\App\Language::translate($HOLIDAY['type'], $QUALIFIED_MODULE)}</span>
										</td>
										<td>
											<div class='float-right'>
												<button data-holiday-id="{$HOLIDAY['id']}"
														class="editHoliday mr-1 text-white btn btn-sm btn-info">
													<span title="{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"
														  class="fas fa-edit alignMiddle"></span>
												</button>
												<button data-holiday-id="{$HOLIDAY['id']}"
														class="deleteHoliday text-white btn btn-sm btn-danger">
													<span title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
														  class="fas fa-trash-alt alignMiddle"></span>
												</button>
											</div>
										</td>
									</tr>
								{/foreach}
								</tbody>
							</table>
						</div>
					</div>
					<div class="modal addDateWindowModal fade publicHolidayModal" tabindex="-1">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header contentsBackground">
									<span class="fa fa-plus mt-2 u-mr-5px"></span>
									<h5 class="modal-title">{\App\Language::translate('LBL_ADD_NEW_HOLIDAY', $QUALIFIED_MODULE)}</h5>
									<button type="button" class="close" data-dismiss="modal"
											title="{\App\Language::translate('LBL_CLOSE')}">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<form class="form-horizontal addDateWindowForm">
									<input type="hidden" name="holidayId" value=""/>
									<div class="modal-body">
										<div class="form-group form-row">
											<div class="col-sm-4 col-form-label u-text-small-bold text-right">
												<span>{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</span>
												<span class="redColor">*</span>
											</div>
											<div class="col-sm-6 controls">
												<input type="text" name="holidayDate" class="dateField form-control"
													   data-date-format="{$USER_MODEL->get('date_format')}"
													   value="{\App\Fields\Date::formatToDisplay(date('Y-m-d'))}"
													   required>
											</div>
										</div>
										<div class="form-group form-row">
											<div class="col-sm-4 col-form-label u-text-small-bold text-right">
												<span>{\App\Language::translate('LBL_HOLIDAY_TYPE', $QUALIFIED_MODULE)}</span>
												<span class="redColor">*</span>
											</div>
											<div class="col-sm-6 controls">
												<select name="holidayType" class="form-control" required
														data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]">
													<option value="national">{\App\Language::translate('LBL_NATIONAL', $QUALIFIED_MODULE)}</option>
													<option value="ecclesiastical">{\App\Language::translate('LBL_ECCLESIASTICAL', $QUALIFIED_MODULE)}</option>
												</select>
											</div>
										</div>
										<div class="form-group form-row">
											<div class="col-sm-4 col-form-label u-text-small-bold text-right">
												<span>{\App\Language::translate('LBL_DAY_NAME', $QUALIFIED_MODULE)}</span>
												<span class="redColor">*</span>
											</div>
											<div class="col-sm-6 controls">
												<input type="text" name="holidayName" value="" class="form-control"
													   placeholder="{\App\Language::translate('LBL_DAY_NAME_DESC', $QUALIFIED_MODULE)}"
													   required
													   data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"/>
											</div>
										</div>
									</div>
									{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', 'Vtiger') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
