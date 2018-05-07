{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Calendar-UsersColors UserColors">
		<div class="widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				{\App\Language::translate('LBL_CALENDAR_CONFIG_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
		</div>
		<hr>
		<div>
			<div class="contents tabbable">
				<ul class="nav nav-tabs layoutTabs massEditTabs">
					<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#calendarConfig"><strong>{\App\Language::translate('LBL_CALENDAR_CONFIG', $QUALIFIED_MODULE)}</strong></a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#workingDays"><strong>{\App\Language::translate('LBL_NOTWORKING_DAYS', $QUALIFIED_MODULE)}</strong></a></li>
				</ul>
				<div class="tab-content layoutContent pt-2">
					<div class="tab-pane paddingTop20 active" id="calendarConfig">
						<table class="table table-sm border listViewEntriesTable">
							<tbody>
								{foreach from=$MODULE_MODEL->getCalendarConfig('reminder') item=item key=key}
									<tr data-id="{$item.name}" data-color="{$item.value}">
										<td class="w-25"><p class="paddingTop10">{\App\Language::translate($item.label,$QUALIFIED_MODULE)}</p></td>
										<td>
											<input class="marginTop10" type="checkbox" id="update_event" name="update_event" data-metod="updateCalendarConfig" value=1 {if $item.value eq 1} checked{/if}/>
										</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
					<div class="tab-pane paddingTop20" id="workingDays">
						<table class="table table-sm border listViewEntriesTable workingDaysTable">
							<tbody>
								<tr>
									<td class="w-25"><p style="padding-top:10px;">{\App\Language::translate('LBL_NOTWORKEDDAYS_INFO', $QUALIFIED_MODULE)}</p></td>
									<td>
										<div class="col-md-4">
											<select class="select2 workignDaysField float-left" multiple id="update_workingdays" name="notworkingdays" data-metod="updateNotWorkingDays">
												<option value="1" {if in_array(1, $NOTWORKINGDAYS)} selected {/if} >{\App\Language::translate(PLL_MONDAY,$QUALIFIED_MODULE)}</option>
												<option value="2" {if in_array(2, $NOTWORKINGDAYS)} selected {/if} >{\App\Language::translate(PLL_TUESDAY,$QUALIFIED_MODULE)}</option>
												<option value="3" {if in_array(3, $NOTWORKINGDAYS)} selected {/if} >{\App\Language::translate(PLL_WEDNESDAY,$QUALIFIED_MODULE)}</option>
												<option value="4" {if in_array(4, $NOTWORKINGDAYS)} selected {/if} >{\App\Language::translate(PLL_THURSDAY,$QUALIFIED_MODULE)}</option>
												<option value="5" {if in_array(5, $NOTWORKINGDAYS)} selected {/if} >{\App\Language::translate(PLL_FRIDAY,$QUALIFIED_MODULE)}</option>
												<option value="6" {if in_array(6, $NOTWORKINGDAYS)} selected {/if} >{\App\Language::translate(PLL_SATURDAY,$QUALIFIED_MODULE)}</option>
												<option value="7" {if in_array(7, $NOTWORKINGDAYS)} selected {/if} >{\App\Language::translate(PLL_SUNDAY,$QUALIFIED_MODULE)}</option>
											</select>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="modal editColorContainer fade" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header contentsBackground">
						<h5 class="modal-title">{\App\Language::translate('LBL_EDIT_COLOR', $QUALIFIED_MODULE)}</h5>
						<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form class="form-horizontal">
							<input type="hidden" class="selectedColor" value="" />
							<div class="form-group">
								<label class=" col-sm-3 col-form-label">{\App\Language::translate('LBL_SELECT_COLOR', $QUALIFIED_MODULE)}</label>
								<div class=" col-sm-8 controls">
									<p class="calendarColorPicker"></p>
								</div>
							</div>
						</form>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</div>
			</div>
		</div>
	{/strip}
