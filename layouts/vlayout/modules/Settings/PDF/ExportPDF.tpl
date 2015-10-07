{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_GENERATE_PDF_FILE', $QUALIFIED_MODULE)}</h3>
		<br />
		<div class="form-group row form-horizontal">
			<label class="col-sm-3 control-label">
				{vtranslate('LBL_PDF_TEMPLATE', $QUALIFIED_MODULE)}:
			</label>
			<div class="col-sm-6 controls">
				<select class="select2 form-control" id="pdf_template" name="pdf_template">
					{foreach from=$TEMPLATES item=TEMPLATE}
						<option value="{$TEMPLATE.id}">
							{vtranslate($TEMPLATE.primary_name, $QUALIFIED_MODULE)}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>
	{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
{/strip}
