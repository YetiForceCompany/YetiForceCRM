{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		{if $ALL_RECORDS neq ''}
			<input type="hidden" name="all_records" id="all_records" value="{$ALL_RECORDS}" />
		{/if}
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_GENERATE_PDF_FILE', $QUALIFIED_MODULE)}</h3>
		<br />
		<div class="panel panel-default">
			<div class="panel-heading"><strong>{vtranslate('LBL_PDF_TEMPLATE', $QUALIFIED_MODULE)}</strong></div>
			<div class="panel-body">
			{foreach from=$TEMPLATES item=TEMPLATE}
				<div class="form-group row form-horizontal">
					<label class="col-sm-6 control-label" for="pdfTpl{$TEMPLATE->getId()}">
						{$TEMPLATE->get('primary_name')}<br />
						<span class="secondaryName">{$TEMPLATE->get('secondary_name')}</span>
					</label>
					<div class="col-sm-6 control-group">
						 <input type="checkbox" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" class="checkbox" value="{$TEMPLATE->getId()}" {if $TEMPLATE->get('default') eq 1}checked="checked"{/if} />
					</div>
				</div>
			{/foreach}
			</div>
		</div>
	</div>
	{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
{/strip}
