{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Companies-EditView -->
	<div class="row mb-2 widget_header">
		<div class="col-12 d-flex">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="editViewContainer container">
		<form name="EditCompanies" action="index.php" method="post" id="EditView" enctype="multipart/form-data">
			<input type="hidden" name="module" value="Companies">
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="SaveAjax" />
			<input type="hidden" name="mode" value="updateCompany">
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			{if !empty(RECORD_ID)}
				<input type="hidden" name="id" value="{$RECORD_ID}" />
			{/if}
			<div class="alert alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<span class="u-fs-13px">
					{\App\Language::translate('LBL_CHANGING_ENTITY_NAME', $QUALIFIED_MODULE)}
				</span>
			</div>
			{include file=\App\Layout::getTemplatePath('Form.tpl',$QUALIFIED_MODULE) MODULE_NAME=$QUALIFIED_MODULE COMPANY_ID=$RECORD_ID}
			<div class="card-footer text-center">
				<button class="btn btn-success mr-1" type="submit">
					<span class="fa fa-check"></span> {App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
				</button>
				<button class="cancelLink btn btn-warning ml-1" type="reset" onclick="javascript:window.history.back();">
					<span class="fa fa-times"></span> {App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
				</button>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-Companies-EditView -->
{/strip}
