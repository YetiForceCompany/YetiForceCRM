{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="row widget_header tpl-Settings-AdvancedPermission-EditVewS2">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="editViewContainer">
		<form name="EditAdvPermission" action="index.php" method="post" id="EditView" class="form-horizontal">
			<input type="hidden" name="module" value="AdvancedPermission">
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="mode" value="step2" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" name="conditions" id="advanced_filter" />
			{include file=\App\Layout::getTemplatePath('AdvanceFilterExpressions.tpl')}
			<div class="row">
				<div class="col-md-5 float-right">
					<span class="float-right">
						<button class="btn btn-success" type="submit"><strong><span class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
						<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();"><span class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</span>
				</div>
			</div>
		</form>
	</div>
{/strip}
