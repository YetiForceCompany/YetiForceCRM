{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="statsContainer">
	<div class="form-horizontal">
		<div class="form-group form-row">
			<label for="langs_list" class="col-form-label col-md-2">{\App\Language::translate('LBL_BASE_LANGUAGE',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select class="form-control select2" name="langs_basic">
					{foreach from=$LANGS item=LABEL key=PREFIX}
						<option value="{$PREFIX}">{$LABEL}</option>
					{/foreach}
				</select>
			</div>
			<label class="col-md-2 col-form-label">{\App\Language::translate('LBL_LANGUAGE',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select multiple="multiple" class="form-control select2" name="langs" placeholder="{\App\Language::translate('LBL_SELECT_SOME_OPTIONS',$QUALIFIED_MODULE)}">
					{foreach from=$LANGS item=LABEL key=PREFIX}
						<option value="{$PREFIX}">{$LABEL}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-md-2">
				<button class="btn btn-success showStats">{\App\Language::translate('LBL_SHOW', $QUALIFIED_MODULE)}</button>
			</div>
		</div>
		<div class="row">
			<div class="col-md-1"></div>
			<div class="alert alert-warning col-md-10">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				{\App\Language::translate('LBL_STATS_INFO', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-1"></div>
		</div>
		<div class="chartBlock row" id="chartBlock">
			<div class="col-md-2"></div>
			<div class="col-md-8">
				<div class="widgetChartContainer" id="widgetChartContainer" style="position:relative;">
					<canvas id="language-stats-chart"></canvas>
				</div>
			</div>
			<div class="col-md-2"></div>
			<input class="widgetData" type="hidden" value=''>
		</div>
		<br />
		<div class="row">
			<div class="col-md-1"></div>
			<div class="col-md-10 statsData"></div>
			<div class="col-md-1"></div>
		</div>
	</div>
</div>
