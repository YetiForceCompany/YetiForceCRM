{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="targetFieldsTableContainer">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			{\App\Language::translate('LBL_HIDEBLOCKS_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
	{if $MANDATORY_FIELDS}
		<div class="alert alert-warning">
			{\App\Language::translate('LBL_MANDATORY_FIELDS_EXIST', $QUALIFIED_MODULE)}
		</div>
		<br />	
		<div class="float-right">
			<a class="btn btn-danger" type="reset" onclick="javascript:window.history.back();">{\App\Language::translate('LBL_BACK', $MODULE)}</a>
		</div>
		<div class="clearfix"></div>
	{else}
		<form method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" name="blockid" value="{$BLOCKID}" />
			<input type="hidden" name="enabled" value="{$ENABLED}" />
			<input type="hidden" name="views" value="{$VIEWS}" />
			<input type="hidden" name="conditions" class="advanced_filter" value="{$ENABLED}" />
			<div class="listViewEntriesDiv contents-bottomscroll" style="overflow-x: visible !important;">
				<div class="bottomscroll-div">
					{include file=\App\Layout::getTemplatePath('AdvanceFilter.tpl') RECORD_STRUCTURE=$RECORD_STRUCTURE}
				</div>
			</div>
			<br />	
			<div class="">
				<div class="float-right">
					<a class="saveLink btn btn-success" ><strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong></a>
					<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{\App\Language::translate('LBL_BACK', $MODULE)}</a>
				</div>
				<div class="clearfix"></div>
			</div>
		</form>	
	{/if}
</div>
{/strip}
