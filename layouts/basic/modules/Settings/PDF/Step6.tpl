{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-PDF-Step6 pdfTemplateContents">
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
			<div class="float-right mb-2">
				<button class="btn btn-danger backStep mr-1" type="button">
					<span class="fas fa-caret-left mr-1"></span>
					{\App\Language::translate('LBL_BACK', $QUALIFIED_MODULE)}
				</button>
				<button class="btn btn-success mr-1" type="submit">
					<span class="fas fa-caret-right mr-1"></span>
					{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}
				</button>
				<button class="btn btn-warning cancelLink" type="reset">
					<span class="fas fa-times mr-1"></span>
					{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
				</button>
			</div>
		</form>
	</div>
{/strip}
