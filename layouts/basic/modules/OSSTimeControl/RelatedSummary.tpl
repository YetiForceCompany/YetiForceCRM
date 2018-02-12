{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="sumaryRelatedTimeControl">
		<script type="text/javascript" src="{\App\Layout::getPublicUrl('libraries/Flot/jquery.flot.js')}"></script>
		<script type="text/javascript" src="{\App\Layout::getPublicUrl('libraries/Flot/jquery.flot.resize.js')}"></script>
		<script type="text/javascript" src="{\App\Layout::getPublicUrl('libraries/Flot/jquery.flot.stack.js')}"></script>
		<script type="text/javascript" src="{\App\Layout::getPublicUrl('libraries/flot-valuelabels/jquery.flot.valuelabels.js')}"></script>
		<script type="text/javascript" src="{\App\Layout::getLayoutFile('modules/OSSTimeControl/resources/InRelation.js')}"></script>
		<style type="text/css">
			.legendContainer{
				position: absolute;
				right: 30px;
				top: 15px;
				background-color: #F2F2F2;
				border: 1px solid #dddddd;
				padding: 3px;
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
					<div class="legendContainer">
						{\App\Language::translate('LBL_SUM', $RELATED_MODULE_NAME)}: {\App\Fields\DateTime::formatToHourText($RELATED_SUMMARY['totalTime'], 'full')}<br />
					</div>
					<div class="chartBlock" style="height: 200px;width:100%"></div>
				</div>
			</div>
			<hr />
		{/if}
	</div>
{/strip}
