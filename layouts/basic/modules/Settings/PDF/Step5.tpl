{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step5" class="form-horizontal">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step6" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="5" />
			<input type="hidden" name="record" value="{$RECORDID}" />
			<input type="hidden" name="module_name" value="{$PDF_MODEL->get('module_name')}" />
			<div class="padding1per stepBorder">
				<label>
					<strong>{vtranslate('LBL_STEP_N',$QUALIFIED_MODULE, 5)}: {vtranslate('LBL_FOOTER_DETAILS',$QUALIFIED_MODULE)}</strong>
				</label>
				<br>
				<div class="row">
					{include file='layouts/basic/modules/Vtiger/VariablePanel.tpl' SELECTED_MODULE=$SELECTED_MODULE PARSER_TYPE='pdf'}
				</div>
				<div class="form-group">
					<div class="col-sm-12 controls">
						<textarea class="form-control" name="footer_content" id="footer_content">{$PDF_MODEL->get('footer_content')}</textarea>
					</div>
				</div>
			</div>
			<br>
			<div class="pull-right">
				<button class="btn btn-danger backStep" type="button"><strong>{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-warning cancelLink" type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}
