{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-PDF-Step3 pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step3" class="form-horizontal" enctype="multipart/form-data">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="3" />
			<input type="hidden" name="record" value="{$RECORDID}" />
			<input type="hidden" name="conditions" id="advanced_filter" value='' />

			<div class="row">
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('AdvanceFilterExpressions.tpl')}

					<div class="float-right mb-2">
						<button class="btn btn-danger backStep mr-1" type="button">
							<span class="fas fa-caret-left mr-1"></span>
							{\App\Language::translate('LBL_BACK', $QUALIFIED_MODULE)}
						</button>
						<button class="btn btn-success mr-1" type="submit">
							<span class="fas fa-caret-right mr-1"></span>
							{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
						</button>
						<a class="btn btn-primary mr-1" href="index.php?module=PDF&parent=Settings&page=1&view=List">
							<span class="fas fa-angle-double-left mr-1"></span>
							{\App\Language::translate('LBL_RETURN', $QUALIFIED_MODULE)}
						</a>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}
