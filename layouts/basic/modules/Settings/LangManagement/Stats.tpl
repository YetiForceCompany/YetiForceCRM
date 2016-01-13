{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
<div class="statsContainer">
	<div class="form-horizontal">
		<div class="form-group">
			<label for="langs_list" class="control-label col-md-2" >{vtranslate('LBL_BASE_LANGUAGE',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select class="form-control selectize" name="langs_basic">
					{foreach from=$LANGS item=LANG key=ID}
						<option value="{$LANG['prefix']}">{$LANG['label']}</option>
					{/foreach}
				</select>
			</div>
			<label class="col-md-2 control-label">{vtranslate('LBL_LANGUAGE',$QUALIFIED_MODULE)}:</label>
			<div class="col-md-3">
				<select multiple="multiple" class="form-control selectize" name="langs" placeholder="{vtranslate('LBL_SELECT_SOME_OPTIONS',$QUALIFIED_MODULE)}" >
					{foreach from=$LANGS item=LANG key=ID}
						<option value="{$LANG['prefix']}">{$LANG['label']}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-md-2">
				<button class="btn btn-success showStats">{vtranslate('LBL_SHOW', $QUALIFIED_MODULE)}</button>
			</div>
		</div>
		<div class="row">
			<div class="col-md-1"></div>
			<div class="alert alert-warning col-md-10">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				{vtranslate('LBL_STATS_INFO', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-1"></div>
		</div>
		<div class="chartBlock row" id="chartBlock">
			<div class="col-md-2"></div>
			<div class="col-md-8">
				<div class="widgetChartContainer" id="widgetChartContainer"></div>
			</div>
			<div class="col-md-2"></div>
			<input class="widgetData" type="hidden" value=''>
		</div>
		<br>
		<div class="row">
			<div class="col-md-2"></div>
			<div class="statsData"></div>
			<div class="col-md-2"></div>
		</div>
	</div>
</div>
