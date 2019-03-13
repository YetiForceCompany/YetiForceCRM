{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Companies-EditView -->
	<div class="row mb-2 widget_header">
		<div class="col-12 d-flex">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="editViewContainer container">
		<form name="EditCompanies" action="index.php" method="post" id="EditView" enctype="multipart/form-data">
			<div class="card mb-2">
				<div class="card-header">
					<span class="adminIcon-company-detlis"
						  aria-hidden="true"></span> {App\Language::translate('LBL_COMPANIES_DESCRIPTION', $QUALIFIED_MODULE)}
				</div>
				<div class="alert alert-info" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<span class="u-font-size-13px">
						{\App\Language::translate('LBL_CHANGING_COMPANY_NAME', $QUALIFIED_MODULE)}
					</span>
				</div>
				<div class="card-body">
					<input type="hidden" name="module" value="Companies">
					<input type="hidden" name="parent" value="Settings"/>
					<input type="hidden" name="action" value="SaveAjax"/>
					<input type="hidden" name="mode" value="updateCompany">
					<input type="hidden" name="record" value="{$RECORD_ID}"/>
					{if !empty(RECORD_ID)}
						<input type="hidden" name="id" value="{$RECORD_ID}"/>
					{/if}
					{include file=\App\Layout::getTemplatePath('Form.tpl',$QUALIFIED_MODULE) MODULE_NAME=$QUALIFIED_MODULE COMPANY_ID=$RECORD_ID}
				</div>
				<div class="card-footer text-center">
					<button class="btn btn-success mr-1" type="submit">
						<span class="fa fa-check"></span> {App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
					</button>
					<button class="cancelLink btn btn-warning ml-1" type="reset"
							onclick="javascript:window.history.back();">
						<span class="fa fa-times"></span> {App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</button>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-Companies-EditView -->
{/strip}
