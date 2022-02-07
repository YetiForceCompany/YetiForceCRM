{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-PublicHoliday-Configuration -->
	<div class="o-breadcrumb widget_header row">
		<div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="contents tabbable">
		<div class="tab-content themeTableColor overflowVisible">
			<div>
				<div id="moduleBlocks">
					<button type="button" class="btn btn-success addPublicHoliday my-2">
						<span class="fas fa-plus"></span>&nbsp;{\App\Language::translate('LBL_ADD_HOLIDAY', $QUALIFIED_MODULE)}
					</button>
					<div class="editFieldsTable block_1 mb-3">
						<div class="border border-bottom-0 bg-light p-2">
							<div class="row">
								<div class="col-12">
									<h4>{\App\Language::translate('LBL_HOLIDAY_LIST', $QUALIFIED_MODULE)}</h4>
								</div>
							</div>
						</div>
						<div class="row responsive-table-header-for-small">
							<div class="col-sm-8 text-right order-sm-2">
								<form>
									<div class="row text-right float-right">
										<label>{\App\Language::translate('LBL_DATE_RANGE', $QUALIFIED_MODULE)}</label>
										<div class="input-group input-group-sm col">
											<input type="text"
												class="ml-1 dateRangeField dateFilter text-center form-control"
												data-date-format="{$USER_MODEL->get('date_format')}"
												data-calendar-type="range" value="{$DATE}"
												data-validation-engine="validate[funcCall[Vtiger_Date_Validator_Js.invokeValidation]]" />
											<div class="input-group-append" title="{\App\Language::translate('LBL_ALL')}">
												<button type="button" class="btn btn-sm btn-default js-range-reset" title="{\App\Language::translate('LBL_ALL')}">
													<span class="fas fa-lg fa-window-close"></span>
												</button>
											</div>
										</div>
									</div>
								</form>
							</div>
							<div class="col-sm-4 order-sm-1">
								<div class="row">
									<div class="col-xs-4">
										<input type="checkbox" class="selectall"
											title="{\App\Language::translate('LBL_SELECT_ALL', $QUALIFIED_MODULE)}" />
									</div>
									<div class="col-xs-8">
										<button class="masscopy btn btn-info btn-xs text-white mr-2"
											title="{\App\Language::translate('LBL_DUPLICATE_SELECTED', $QUALIFIED_MODULE)}">
											<span class="fas fa-clone alignMiddle"></span>
										</button>
										<button class="massdelete btn btn-danger btn-xs text-white ml-2"
											title="{\App\Language::translate('LBL_DELETE_SELECTED', $QUALIFIED_MODULE)}">
											<span class="fas fa-trash-alt"></span>
										</button>
									</div>
								</div>
							</div>
						</div>
						<table class="table responsive-table table-bordered">
							<thead class="text-capitalize text-center">
								<tr>
									<th class="font-weight-normal">
										<button class="masscopy btn btn-info btn-xs"
											title="{\App\Language::translate('LBL_DUPLICATE_SELECTED', $QUALIFIED_MODULE)}">
											<span class="fas fa-clone"></span>
										</button>
										<button class="massdelete btn btn-danger btn-xs text-white ml-2"
											title="{\App\Language::translate('LBL_DELETE_SELECTED', $QUALIFIED_MODULE)}">
											<span class="fas fa-trash-alt"></span>
										</button>
									</th>
									<th colspan="5" class="text-right font-weight-normal">
										<form>
											<div class="row text-right float-right">
												{\App\Language::translate('LBL_DATE_RANGE', $QUALIFIED_MODULE)}
												<div class="input-group input-group-sm col">
													<input type="text"
														class="ml-1 dateRangeField dateFilter text-center form-control"
														data-date-format="{$USER_MODEL->get('date_format')}"
														data-calendar-type="range" value="{$DATE}"
														data-validation-engine="validate[funcCall[Vtiger_Date_Validator_Js.invokeValidation]]" />
													<div class="input-group-append" title="{\App\Language::translate('LBL_ALL')}">
														<button type="button" class="btn btn-sm btn-default js-range-reset" title="{\App\Language::translate('LBL_ALL')}">
															<span class="fas fa-lg fa-window-close"></span>
														</button>
													</div>
												</div>
											</div>
										</form>
									</th>
								</tr>
								<tr>
									<th scope="col">
										<input type="checkbox" class="selectall"
											title="{\App\Language::translate('LBL_SELECT_ALL', $QUALIFIED_MODULE)}" />
									</th>
									<th scope="col">{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</th>
									<th scope="col">{\App\Language::translate('LBL_DAY', $QUALIFIED_MODULE)}</th>
									<th scope="col">{\App\Language::translate('LBL_DAY_NAME', $QUALIFIED_MODULE)}</th>
									<th scope="col">{\App\Language::translate('LBL_HOLIDAY_TYPE', $QUALIFIED_MODULE)}</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody id="itemsContainer">
								{include file=App\Layout::getTemplatePath('ConfigurationItems.tpl', $QUALIFIED_MODULE) HOLIDAYS=$HOLIDAYS}
							</tbody>
						</table>
					</div>
				</div>
				<div class="publicHolidayModal modal fade" tabindex="-1">
					<div class="modal-dialog modal-md">
						<div class="modal-content">
							<div class="modal-header">
								<span class="fa fa-plus mt-2 u-mr-5px"></span>
								<h5 class="modal-title">{\App\Language::translate('LBL_ADD_NEW_HOLIDAY', $QUALIFIED_MODULE)}</h5>
								<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form class="form-horizontal">
									<input type="hidden" name="parent" value="Settings" />
									<input type="hidden" name="module" value="{$MODULE_NAME}" />
									<input type="hidden" name="action" value="Holiday" />
									<input type="hidden" name="mode" value="save" />
									<input type="hidden" name="holidayId" value="" />
									<div class="form-group form-row">
										<div class="col-sm-4 col-form-label u-text-small-bold text-right">
											<span class="redColor">*</span>
											<span>{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</span>
										</div>
										<div class="col-sm-6 controls">
											<input type="text" name="holidayDate" class="dateField form-control"
												data-date-format="{$USER_MODEL->get('date_format')}"
												data-validation-engine="validate[required,funcCall[Vtiger_Date_Validator_Js.invokeValidation]]" />
										</div>
									</div>
									<div class="form-group form-row">
										<div class="col-sm-4 col-form-label u-text-small-bold text-right">
											<span>{\App\Language::translate('LBL_HOLIDAY_TYPE', $QUALIFIED_MODULE)}</span>
										</div>
										<div class="col-sm-6 controls">
											<select name="holidayType" class="form-control">
												<option value="national">{\App\Language::translate('LBL_NATIONAL', $QUALIFIED_MODULE)}</option>
												<option value="ecclesiastical">{\App\Language::translate('LBL_ECCLESIASTICAL', $QUALIFIED_MODULE)}</option>
											</select>
										</div>
									</div>
									<div class="form-group form-row">
										<div class="col-sm-4 col-form-label u-text-small-bold text-right">
											<span class="redColor">*</span>
											<span>{\App\Language::translate('LBL_DAY_NAME', $QUALIFIED_MODULE)}</span>
										</div>
										<div class="col-sm-6 controls">
											<input type="text" name="holidayName" value="" class="form-control"
												placeholder="{\App\Language::translate('LBL_DAY_NAME_DESC', $QUALIFIED_MODULE)}"
												data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
										</div>
									</div>
									{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="publicHolidayModalMassDuplicate modal fade" tabindex="-1">
					<div class="modal-dialog modal-md">
						<div class="modal-content">
							<div class="modal-header">
								<span class="far fa-clone mt-2 u-mr-5px"></span>
								<h5 class="modal-title">{\App\Language::translate('LBL_DUPLICATE_HOLIDAY', $QUALIFIED_MODULE)}</h5>
								<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form class="form-horizontal">
									<input type="hidden" name="parent" value="Settings" />
									<input type="hidden" name="module" value="{$MODULE_NAME}" />
									<input type="hidden" name="action" value="Holiday" />
									<input type="hidden" name="mode" value="duplicate" />
									<input type="hidden" name="holidayIds" value="" />
									<div class="form-group form-row">
										<div class="col-sm-4 col-form-label u-text-small-bold text-right">
											<span>{\App\Language::translate('LBL_DUPLICATE_YEAR', $QUALIFIED_MODULE)}</span>
										</div>
										<div class="col-sm-6 controls">
											<select name="targetYear" class="form-control"
												data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]">
												<option></option>
												{for $Y=$YEAR to $YEAR+10}
													<option value="{$Y}">{$Y}</option>
												{/for}
											</select>
										</div>
									</div>
									{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', 'Vtiger') BTN_SUCCESS='LBL_DUPLICATE' BTN_DANGER='LBL_CANCEL'}
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-PublicHoliday-Configuration -->
{/strip}
