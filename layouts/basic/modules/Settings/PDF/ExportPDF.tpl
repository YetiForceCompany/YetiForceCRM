{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		{if $ALL_RECORDS neq ''}
			<input type="hidden" name="all_records" id="all_records" value="{$ALL_RECORDS}" />
		{/if}
		<h5 id="massEditHeader" class="modal-title">{\App\Language::translate('LBL_GENERATE_PDF_FILE', $QUALIFIED_MODULE)}</h5>
		<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
		<br />
		<div class="panel panel-default">
			<div class="panel-heading"><strong>{\App\Language::translate('LBL_PDF_TEMPLATE', $QUALIFIED_MODULE)}</strong></div>
			<div class="panel-body">
				{foreach from=$TEMPLATES item=TEMPLATE}
					<div class="form-group row form-horizontal">
						<label class="col-sm-6 col-form-label" for="pdfTpl{$TEMPLATE->getId()}">
							{$TEMPLATE->get('primary_name')}<br />
							<span class="secondaryName">{$TEMPLATE->get('secondary_name')}</span>
						</label>
						<div class="col-sm-6 control-group">
							<input type="checkbox" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" class="checkbox" value="{$TEMPLATE->getId()}" {if $TEMPLATE->get('default') eq 1}checked="checked" {/if} />
						</div>
					</div>
				{/foreach}
			</div>
		</div>
	</div>
	{include file=\App\Layout::getTemplatePath('ModalFooter.tpl', $QUALIFIED_MODULE)}
{/strip}
