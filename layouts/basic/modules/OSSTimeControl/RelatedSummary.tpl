{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="sumaryRelatedTimeControl">
		<link rel="stylesheet" href="libraries/jquery/flot/jquery.flot.valuelabels.css">
		<script type="text/javascript" src="libraries/jquery/flot/jquery.flot.min.js"></script>
		<script type="text/javascript" src="libraries/jquery/flot/jquery.flot.stack.min.js"></script>
		<script type="text/javascript" src="libraries/jquery/flot/jquery.flot.valuelabels.min.js"></script>
		<script type="text/javascript" src="{Yeti_Layout::getLayoutFile('modules/OSSTimeControl/resources/InRelation.js')}"></script>
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
		{assign var=TOTALTIME value=vtlib\Functions::decimalTimeFormat($RELATED_SUMMARY['totalTime'])}
		{if count($RELATED_SUMMARY['userTime']) gt 0 }
			<div class="row">
				<div class="col-md-12">
					<button class="btn btn-sm pull-left btn-default switchChartContainer" type="button">
						<span class="glyphicon glyphicon-chevron-up"></span>
					</button>
					<h5>{vtranslate('LBL_SUM_OF_WORKING_TIME_DIVIDED_INTO_USERS', $RELATED_MODULE_NAME)}:</h5>
				</div>
			</div>
			<div class="row chartContainer">
				<div class="col-md-12">
					<input class="widgetData" type="hidden" value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($RELATED_SUMMARY['userTime']))}' />
					<div class="legendContainer">
						{vtranslate('LBL_SUM', $RELATED_MODULE_NAME)}: {$TOTALTIME['full']}<br />
					</div>
					<div class="chartBlock" style="height: 200px;width:100%"></div>
				</div>
			</div>
			<hr />
		{/if}
	</div>
{/strip}
