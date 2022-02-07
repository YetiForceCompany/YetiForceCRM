{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-PDF-Step2 pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step2" class="form-horizontal">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step3" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="2" />
			<input type="hidden" name="record" value="{$RECORDID}" />
			<input type="hidden" name="module_name" value="{$PDF_MODEL->get('module_name')}" />
			<input type="hidden" name="watermark_image" value="" />
			{assign var=FIELD_INFO value=\App\Purifier::encodeHtml('{"maximumlength":"16777215","type":"text"}')}
			<div class="row">
				<div class="col-12 mb-3">
					<div class="card">
						<div class="card-header">
							<span class="fa fa-copy"></span> {\App\Language::translate('LBL_VARIABLES',$QUALIFIED_MODULE)}
						</div>
						<div class="card-body">
							<div class="row js-variable-panel" data-js="container">
								{include file='layouts/basic/modules/Vtiger/VariablePanel.tpl' SELECTED_MODULE=$SELECTED_MODULE PARSER_TYPE='pdf'}
							</div>
						</div>
					</div>
				</div>
				<div class="col-12">
					<div class="mb-3">
						<div class="card">
							<div class="card-header">
								<span class="yfi yfi-full-editing-view mr-2"></span> {\App\Language::translate('LBL_HEADER_DETAILS',$QUALIFIED_MODULE)}
							</div>
							<div class="card-body p-0">
								<div class="controls">
									<textarea class="form-control w-100 js-editor" name="header_content" id="header_content" data-js="ckeditor" data-purify-mode="Html"
										data-validation-engine="validate[funcCall[Vtiger_MaxSizeInByte_Validator_Js.invokeValidation]]"
										data-fieldinfo='{$FIELD_INFO}'>{$PDF_MODEL->get('header_content')}</textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="mb-3">
						<div class="card">
							<div class="card-header">
								<span class="yfi yfi-full-editing-view mr-2"></span> {\App\Language::translate('LBL_BODY_DETAILS',$QUALIFIED_MODULE)}
							</div>
							<div class="card-body p-0">
								<div class="controls">
									<textarea class="form-control w-100 js-editor" name="body_content" id="body_content" data-js="ckeditor" data-purify-mode="Html"
										data-validation-engine="validate[funcCall[Vtiger_MaxSizeInByte_Validator_Js.invokeValidation]]"
										data-fieldinfo='{$FIELD_INFO}'>{$PDF_MODEL->get('body_content')}</textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="mb-3">
						<div class="card">
							<div class="card-header">
								<span class="yfi yfi-full-editing-view mr-2"></span> {\App\Language::translate('LBL_FOOTER_DETAILS',$QUALIFIED_MODULE)}
							</div>
							<div class="card-body p-0">
								<div class="controls">
									<textarea class="form-control js-editor" name="footer_content" id="footer_content" data-js="ckeditor" data-purify-mode="Html"
										data-validation-engine="validate[funcCall[Vtiger_MaxSizeInByte_Validator_Js.invokeValidation]]"
										data-fieldinfo='{$FIELD_INFO}'>{$PDF_MODEL->get('footer_content')}</textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="mb-3">
						<div class="card">
							<div class="card-header">
								<span class="yfi yfi-full-editing-view mr-2"></span> {\App\Language::translate('LBL_CUSTOM_STYLES',$QUALIFIED_MODULE)}
							</div>
							<div class="card-body p-0">
								<div class="controls">
									<textarea class="form-control" name="styles" id="styles">{$PDF_MODEL->get('styles')}</textarea>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>

			<div class="float-right mb-2">
				<button class="btn btn-danger backStep mr-1" type="button">
					<span class="fas fa-caret-left mr-1"></span>{\App\Language::translate('LBL_BACK', $QUALIFIED_MODULE)}
				</button>
				<button class="btn btn-success mr-1" type="submit">
					<span class="fas fa-caret-right mr-1"></span>{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}
				</button>
				<button class="btn btn-warning cancelLink" type="reset">
					<span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
				</button>
			</div>
		</form>
	</div>
{/strip}
