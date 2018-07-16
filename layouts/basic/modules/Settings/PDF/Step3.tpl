{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-PDF-Step3 pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step3" class="form-horizontal">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step4"/>
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" class="step" value="3"/>
			<input type="hidden" name="record" value="{$RECORDID}"/>
			<input type="hidden" name="module_name" value="{$PDF_MODEL->get('module_name')}"/>
			<div class="padding1per stepBorder">
				<label>
					<strong>{\App\Language::translateArgs('LBL_STEP_N',$QUALIFIED_MODULE, 3)}: {\App\Language::translate('LBL_HEADER_DETAILS',$QUALIFIED_MODULE)}</strong>
				</label>
				<br/>
				<div class="row">
					{include file='layouts/basic/modules/Vtiger/VariablePanel.tpl' SELECTED_MODULE=$SELECTED_MODULE PARSER_TYPE='pdf'}
				</div>
				<div class="form-group">
					<div class="col-sm-12 controls">
						<textarea class="form-control js-editor" name="header_content" id="header_content"
								  data-js="ckeditor">{$PDF_MODEL->get('header_content')}</textarea>
					</div>
				</div>
			</div>
			<br/>
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
