{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="sumaryRelatedTimeControl">
		<script type="text/javascript" src="{\App\Layout::getLayoutFile('modules/OSSTimeControl/resources/InRelation.js')}"></script>
		{if $RELATED_SUMMARY['userTime']['show_chart']}
			<div class="row">
				<div class="col-md-12">
					<button class="btn btn-sm btn-default float-left mr-2 switchChartContainer" type="button">
						<span class="fa fa-chevron-up"></span>
					</button>
					<h5>{\App\Language::translate('LBL_SUM_OF_WORKING_TIME_DIVIDED_INTO_USERS', $RELATED_MODULE_NAME)}:</h5>
				</div>
			</div>
			<div class="row chartContainer">
				<div class="col-md-12">
					<input class="widgetData" type="hidden" value='{\App\Purifier::encodeHtml(\App\Json::encode($RELATED_SUMMARY['userTime']))}' />
					<div class="chartBlock chart-container" style="position: relative; height:200px; width:100%">
						<div id="related-summary-chart-canvas" class="js-chart-container"></div>
					</div>
				</div>
			</div>
			<hr />
		{/if}
	</div>
{/strip}
