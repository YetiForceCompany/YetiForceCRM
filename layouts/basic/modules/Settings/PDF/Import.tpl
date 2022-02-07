{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="o-breadcrumb widget_header row mb-2">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="importTemplateDiv" id="importTemplateContainer">
		{if isset($UPLOAD)}
			{if $UPLOAD eq true}
				<p class="bgMessage bgOK">
					{\App\Language::translate('LBL_UPLOAD_OK', $QUALIFIED_MODULE)} <a class="tdUnderline" href="index.php?module=PDF&parent=Settings&view=Edit&record={$RECORDID}"> {\App\Language::translate('LBL_GO_TO_TEMPLATE', $QUALIFIED_MODULE)}</a>
				</p>
			{elseif $UPLOAD eq false}
				<p class="bgMessage bgERROR">
					{\App\Language::translate('LBL_UPLOAD_ERROR', $QUALIFIED_MODULE)} <a class="tdUnderline" href="{Settings_PDF_Module_Model::getDefaultUrl()}"> {\App\Language::translate('LBL_RETURN', $QUALIFIED_MODULE)}</a>
				</p>
			{/if}
		{else}
			<form name="ImportPdfTemplate" class="form-horizontal js-validation-engine" action="index.php" method="post" enctype="multipart/form-data">
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="module" value="PDF" />
				<input type="hidden" name="view" value="Import" />
				<input type="hidden" name="upload" value="true" />
				<div name="uploadPdfTemplate">
					<table class="massEditTable table table-bordered">
						<thead>
							<tr class="blockHeader">
								<th class="fieldLabel">
									<strong>{\App\Language::translate('LBL_IMPORT_VIEW', $QUALIFIED_MODULE)} ({\App\Language::translate('LBL_TEMPLATE_XML', $QUALIFIED_MODULE)})</strong>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div class="fieldValue position-relative">
										<input type="file" name="imported_xml" accept="text/xml" data-validation-engine='validate[required]' id="imported_xml" />
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="float-right">
					<button class="btn btn-success" type="submit"><strong>{\App\Language::translate('LBL_UPLOAD_TEMPLATE', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
					<a href="{Settings_PDF_Module_Model::getDefaultUrl()}" class="btn btn-warning" type="button">{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
				</div>
			</form>
		{/if}
	</div>
{/strip}
