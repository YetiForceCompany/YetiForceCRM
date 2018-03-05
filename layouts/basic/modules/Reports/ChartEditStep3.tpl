{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<form class="form-horizontal recordEditView padding1per" id="chart_report_step3" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="ChartSave" />
		<input type="hidden" name="record" value="{$RECORD_ID}" />
		<input type="hidden" name="reportname" value="{$REPORT_MODEL->get('reportname')}" />
		<input type="hidden" name="folderid" value="{$REPORT_MODEL->get('folderid')}" />
		<input type="hidden" name="reports_description" value="{$REPORT_MODEL->get('reports_description')}" >
		<input type="hidden" name="primary_module" value="{$PRIMARY_MODULE}" />
		<input type="hidden" name="secondary_modules" value="{\App\Purifier::encodeHtml(\App\Json::encode($SECONDARY_MODULES))}" >
		<input type="hidden" name="isDuplicate" value="{$IS_DUPLICATE}" />
		<input type="hidden" name="advanced_filter" id="advanced_filter" value="" />
		<input type="hidden" class="step" value="3" />
		<input type="hidden" name='groupbyfield' value={$CHART_MODEL->getGroupByField()} >
		<input type="hidden" name='datafields' value="{\App\Purifier::encodeHtml(\App\Json::encode($CHART_MODEL->getDataFields()))}">
		<input type="hidden" name='charttype' value={$CHART_MODEL->getChartType()}>

		<div class="padding1per border1px">
			<div class="">
				<div>
					<div><h4><strong>{\App\Language::translate('LBL_SELECT_CHART_TYPE',$MODULE)}</strong></h4></div><br />
					<div>
						<div>
							<ul class="nav nav-tabs" name="charttab" style="text-align:center;font-size:14px;font-weight: bold;margin:0 3%;border:0px">
								<li class="active marginRight5px" >
									<a data-type="pieChart" data-toggle="tab">
										<div><img src="{\App\Layout::getImagePath('pie.png')}" alt="{\App\Language::translate('LBL_PIE_CHART', $MODULE)}" style="border:1px solid #ccc;" /></div><br />
										<div>{\App\Language::translate('LBL_PIE_CHART', $MODULE)}</div>
									</a>
								</li>
								<li class="marginRight5px">
									<a data-type="verticalbarChart" data-toggle="tab">
										<div><img src="{\App\Layout::getImagePath('vbar.png')}" alt="{\App\Language::translate('LBL_VERTICAL_BAR_CHART', $MODULE)}" style="border:1px solid #ccc;" /></div><br />
										<div>{\App\Language::translate('LBL_VERTICAL_BAR_CHART', $MODULE)}</div>
									</a>
								</li>
								<li class="marginRight5px">
									<a data-type="horizontalbarChart" data-toggle="tab">
										<div><img src="{\App\Layout::getImagePath('hbar.png')}" alt="{\App\Language::translate('LBL_HORIZONTAL_BAR_CHART', $MODULE)}" style="border:1px solid #ccc;" /></div><br />
										<div>{\App\Language::translate('LBL_HORIZONTAL_BAR_CHART', $MODULE)}</div>
									</a>
								</li>
								<li class="marginRight5px" >
									<a data-type="lineChart" data-toggle="tab">
										<div><img src="{\App\Layout::getImagePath('line.png')}" alt="{\App\Language::translate('LBL_LINE_CHART', $MODULE)}" style="border:1px solid #ccc;" /></div><br />
										<div>{\App\Language::translate('LBL_LINE_CHART', $MODULE)}</div>
									</a>
								</li>
							</ul>
							<div class='tab-content contentsBackground' style="height:auto;padding:4%;border:1px solid #ccc;">
								<br />
								<div class="row tab-pane active">
									<div>
										<span class="col-md-4">
											<div><span>{\App\Language::translate('LBL_SELECT_GROUP_BY_FIELD', $MODULE)}</span><span class="redColor">*</span></div><br />
											<div class="row">
												<select id='groupbyfield' name='groupbyfield' class="col-md-10 validate[required] form-control" data-validation-engine="validate[required]" style='min-width:300px;'></select>
											</div>
										</span>
										<span class="col-md-2">&nbsp;</span>
										<span class="col-md-4">
											<div><span>{\App\Language::translate('LBL_SELECT_DATA_FIELD', $MODULE)}</span><span class="redColor">*</span></div><br />
											<div class="row">
												<select id='datafields' name='datafields[]' class="col-md-10 validate[required] form-control" data-validation-engine="validate[required]" style='min-width:300px;'>
												</select></div>
										</span>
									</div>
								</div>
								<div class='row alert-info well' style="position: relative; top: 50px;width:95%">
									<span class='span alert-info'>
										<div>
											<span class="fas fa-info-circle"></span>&nbsp;&nbsp;
											{\App\Language::translate('LBL_PLEASE_SELECT_ATLEAST_ONE_GROUP_FIELD_AND_DATA_FIELD', $MODULE)}
										</div>
										<br />
										<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{\App\Language::translate('LBL_FOR_BAR_GRAPH_AND_LINE_GRAPH_SELECT_3_MAX_DATA_FIELDS', $MODULE)}</div>
									</span>
								</div>
							</div>
						</div>
					</div>
					<div class='d-none'>
						{include file=\App\Layout::getTemplatePath('chartReportHiddenContents.tpl', $MODULE)}
					</div>
				</div>
			</div>
		</div>
		<br />
		<div class="float-right block padding20px">
			<button type="button" class="btn btn-danger backStep"><strong>{\App\Language::translate('LBL_BACK',$MODULE)}</strong></button>&nbsp;&nbsp;
			<button type="submit" class="btn btn-success" id="generateReport"><strong>{\App\Language::translate('LBL_GENERATE_CHART',$MODULE)}</strong></button>&nbsp;&nbsp;
			<button class="cancelLink btn btn-warning" onclick="window.history.back()">{\App\Language::translate('LBL_CANCEL',$MODULE)}</a>&nbsp;&nbsp;
				<br />
		</div>
	</form>
{/strip}
