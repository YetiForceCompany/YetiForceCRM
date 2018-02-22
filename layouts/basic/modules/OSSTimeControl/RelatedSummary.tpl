{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="sumaryRelatedTimeControl">
		<script type="text/javascript" src="{\App\Layout::getPublicUrl('libraries/chart.js/dist/Chart.js')}"></script>
		<script type="text/javascript" src="{\App\Layout::getPublicUrl('libraries/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.js')}"></script>
		<script type="text/javascript" src="{\App\Layout::getLayoutFile('modules/OSSTimeControl/resources/InRelation.js')}"></script>
		<style type="text/css">
			.legendContainer{
				position: absolute;
				right: 30px;
				top: 15px;
				background-color: #F2F2F2;
				border: 1px solid #dddddd;
				padding: 3px;
				z-index:2;
			}
			.switchChartContainer{
				margin-right: 5px;
			}
		</style>
		{if count($RELATED_SUMMARY['userTime']) gt 0 }
			<div class="row">
				<div class="col-md-12">
					<button class="btn btn-sm float-left btn-light switchChartContainer" type="button">
						<span class="fas fa-chevron-up"></span>
					</button>
					<h5>{\App\Language::translate('LBL_SUM_OF_WORKING_TIME_DIVIDED_INTO_USERS', $RELATED_MODULE_NAME)}:</h5>
				</div>
			</div>
			<div class="row chartContainer">
				<div class="col-md-12">
					<input class="widgetData" type="hidden" value='{\App\Purifier::encodeHtml(\App\Json::encode($RELATED_SUMMARY['userTime']))}' />
					<div class="chartBlock chart-container" style="position: relative; height: 200px;width:100%">
						<canvas id="related-summary-chart-canvas"></canvas>
					</div>
				</div>
			</div>
			<hr />
		{/if}
	</div>
{/strip}
