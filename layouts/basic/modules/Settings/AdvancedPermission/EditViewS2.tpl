{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="row widget_header">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			{vtranslate('LBL_ADVANCED_PERMISSION_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="editViewContainer">
		<form name="EditAdvPermission" action="index.php" method="post" id="EditView" class="form-horizontal">
			<input type="hidden" name="module" value="AdvancedPermission">
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Save">
			<input type="hidden" name="mode" value="step2">
			<input type="hidden" name="record" value="{$RECORD_ID}">
			<input type="hidden" name="conditions" id="advanced_filter"/>
			{include file='AdvanceFilterExpressions.tpl'|@vtemplate_path}
			<div class="row">
				<div class="col-md-5 pull-right">
					<span class="pull-right">
						<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
						<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</span>
				</div>
			</div>
		</form>
	</div>
{/strip}
