{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_GENERATE_PDF_FILE', $QUALIFIED_MODULE)}</h3>
		<br />
		<div class="panel panel-default">
			<div class="panel-heading"><strong>{vtranslate('LBL_PDF_TEMPLATE', $QUALIFIED_MODULE)}</strong></div>
			<div class="panel-body">
			{foreach from=$TEMPLATES item=TEMPLATE}
				<div class="form-group row form-horizontal">
					<label class="col-sm-4 control-label" for="pdfTpl{$TEMPLATE->getId()}">{$TEMPLATE->get('primary_name')}</label>
					<div class="col-sm-6 controls">
						 <input type="checkbox" id="pdfTpl{$TEMPLATE->getId()}" name="pdf_template[]" value="{$TEMPLATE->getId()}" />
					</div>
				</div>
			{/foreach}
			</div>
		</div>
	</div>
	{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
{/strip}
