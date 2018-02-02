{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step6" class="form-horizontal">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step7" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="6" />
			<input type="hidden" name="record" value="{$RECORDID}" />
			<input type="hidden" name="conditions" id="advanced_filter" value='' />

			{include file=\App\Layout::getTemplatePath('AdvanceFilterExpressions.tpl')}
			<br />
			<div class="float-right">
				<button class="btn btn-danger backStep" type="button"><strong>{\App\Language::translate('LBL_BACK', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-success" type="submit"><strong>{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-warning cancelLink" type="reset">{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}
