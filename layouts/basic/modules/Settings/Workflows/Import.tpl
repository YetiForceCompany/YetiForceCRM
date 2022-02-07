{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Workflows-Import -->
	<div class="row">
		<div class="col-md-12">
			<h3>{\App\Language::translate('LBL_IMPORT_VIEW', $QUALIFIED_MODULE)}</h3>
		</div>
	</div>
	<hr>
	<div class="importTemplateDiv" id="importTemplateContainer">
		{if isset($UPLOAD)}
			{if $UPLOAD eq true}
				<p class="bgMessage bgOK">
					<i class="fas fa-check-circle"></i> {\App\Language::translate('LBL_UPLOAD_OK', $QUALIFIED_MODULE)} <a href="index.php?module=Workflows&parent=Settings&view=Edit&record={$RECORDID}">{\App\Language::translate('LBL_GO_TO_TEMPLATE', $QUALIFIED_MODULE)}</a>
				</p>
				{if isset($MESSAGES['error']) }
					{foreach from=$MESSAGES['error'] item=MSG}
						<p class="bgMessage bgWARNING"><i class="fas fa-info-circle"></i> {$MSG}</p>
					{/foreach}
				{/if}
			{elseif $UPLOAD eq false}
				<p class="bgMessage bgERROR">
					{\App\Language::translate('LBL_UPLOAD_ERROR', $QUALIFIED_MODULE)} <a href="{Settings_Workflows_Module_Model::getDefaultUrl()}">{\App\Language::translate('LBL_RETURN', $QUALIFIED_MODULE)}</a>
				</p>
			{/if}
		{else}
			<form name="ImportWorkflowTemplate" action="index.php" method="post" class="form-horizontal" enctype="multipart/form-data">
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="module" value="Workflows" />
				<input type="hidden" name="view" value="Import" />
				<input type="hidden" name="upload" value="true" />
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_TRIGGER_XML', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<input type="file" name="imported_xml" accept="text/xml" class="form-control" data-validation-engine='validate[required]' id="imported_xml" />
					</div>
				</div>
				<br />
				<div class="float-right">
					<button class="btn btn-success" type="submit"><strong>{\App\Language::translate('LBL_UPLOAD_TRIGGER', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
					<a href="{Settings_Workflows_Module_Model::getDefaultUrl()}" class="btn btn-warning" type="button">{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
				</div>
			</form>
		{/if}
	</div>
	<!-- /tpl-Settings-Workflows-Import -->
{/strip}
