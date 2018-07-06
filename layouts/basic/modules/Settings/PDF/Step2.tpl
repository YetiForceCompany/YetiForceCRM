{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-PDF-Step2 pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step2" class="form-horizontal">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step3" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="2" />
			<input type="hidden" name="record" value="{$RECORDID}" />

			<div class="padding1per stepBorder">
				<label>
					<strong>{\App\Language::translateArgs('LBL_STEP_N',$QUALIFIED_MODULE, 2)}: {\App\Language::translate('LBL_DOCUMENT_SETTINGS_DETAILS',$QUALIFIED_MODULE)}</strong>
				</label>
				<br />
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_PAGE_FORMAT', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						<select class="select2 form-control rtl" id="page_format" name="page_format" data-validation-engine="validate[required]">
							<option value="" selected="">{\App\Language::translate('LBL_SELECT', $QUALIFIED_MODULE)}</option>
							{foreach item=FORMAT from=Settings_PDF_Module_Model::getPageFormats()}
								<option value="{$FORMAT}" {if $PDF_MODEL->get('page_format') eq $FORMAT} selected="selected" {/if}>
									{\App\Language::translate($FORMAT, $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_MAIN_MARGIN', $QUALIFIED_MODULE)}
					</label>
						{if $PDF_MODEL->get('margin_chkbox') === 1}
							{assign 'MARGIN_CHECKED' true}
						{else}
							{assign 'MARGIN_CHECKED' false}
						{/if}
						<div class="col-sm-1">
							<input type="checkbox" id="margin_chkbox" name="margin_chkbox" value="1" {if $MARGIN_CHECKED eq 'true'}checked="checked"{/if} />
						</div>
					<div class="col-sm-6 row d-flex justify-content-center mx-auto">
						<div class="col-md-6 col-lg-3 mb-2 margin_inputs {if $MARGIN_CHECKED eq 'true'}d-none{/if}">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text" id="margin_top"><span class="fas fa-arrow-up" title="{\App\Language::translate('LBL_TOP', $QUALIFIED_MODULE)}"></span></span>
								</div>
								<input type="text" class="form-control" aria-describedby="margin_top" name="margin_top" id="margin_top" value="{$PDF_MODEL->get('margin_top')}" placeholder="{\App\Language::translate('LBL_TOP', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_TOP_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
							</div>
						</div>
						<div class="col-md-6 col-lg-3 mb-2 margin_inputs {if $MARGIN_CHECKED eq 'true'}d-none{/if}">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text" id="margin_right"><span class="fas fa-arrow-right" title="{\App\Language::translate('LBL_RIGHT', $QUALIFIED_MODULE)}"></span></span>
								</div>
								<input type="text" class="form-control" aria-describedby="margin_right" name="margin_right" id="margin_right" value="{$PDF_MODEL->get('margin_right')}" placeholder="{\App\Language::translate('LBL_RIGHT', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_RIGHT_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
							</div>
						</div>
						<div class="col-md-6 col-lg-3 mb-2 margin_inputs {if $MARGIN_CHECKED eq 'true'}d-none{/if}">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text" id="margin_bottom"><span class="fas fa-arrow-down" title="{\App\Language::translate('LBL_BOTTOM', $QUALIFIED_MODULE)}"></span></span>
								</div>
								<input type="text" class="form-control" aria-describedby="margin_bottom" name="margin_bottom" id="margin_bottom" value="{$PDF_MODEL->get('margin_bottom')}" placeholder="{\App\Language::translate('LBL_BOTTOM', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_BOTTOM_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
							</div>
						</div>
						<div class="col-md-6 col-lg-3 mb-2 margin_inputs {if $MARGIN_CHECKED eq 'true'}d-none{/if}">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text" id="margin_left"><span class="fas fa-arrow-left" title="{\App\Language::translate('LBL_LEFT', $QUALIFIED_MODULE)}"></span></span>
								</div>
								<input type="text" class="form-control" aria-describedby="margin_left" name="margin_left" id="margin_left" value="{$PDF_MODEL->get('margin_left')}" placeholder="{\App\Language::translate('LBL_LEFT', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_LEFT_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
							</div>
						</div>
					</div>
				</div>
				<div class="form-group margin_inputs {if $MARGIN_CHECKED eq 'true'}d-none{/if}">
					<div class="col-sm-6 row d-flex justify-content-center mx-auto">
						<div class="col-12  col-lg-6 mb-2">
							<label class="col-12 col-form-label text-center u-text-ellipsis">{\App\Language::translate('LBL_HEADER_HEIGHT', $QUALIFIED_MODULE)}</label>
							<input type="text" class="form-control" name="header_height" id="header_height" value="{$PDF_MODEL->get('header_height')}" placeholder="{\App\Language::translate('LBL_HEADER_HEIGHT', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_HEADER_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
						</div>
						<div class="col-md-12 col-lg-6 mb-2">
							<label class="col-12 col-form-label text-center u-text-ellipsis">{\App\Language::translate('LBL_FOOTER_HEIGHT', $QUALIFIED_MODULE)}</label>
							<input type="text" class="form-control" name="footer_height" id="footer_height" value="{$PDF_MODEL->get('footer_height')}" placeholder="{\App\Language::translate('LBL_FOOTER_HEIGHT', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_FOOTER_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_PAGE_ORIENTATION', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<select class="select2 form-control" id="page_orientation" name="page_orientation">
							<option value="PLL_PORTRAIT" {if $PDF_MODEL->get('page_orientation') eq 'PLL_PORTRAIT'} selected="selected" {/if}>
								{\App\Language::translate('PLL_PORTRAIT', $QUALIFIED_MODULE)}
							</option>
							<option value="PLL_LANDSCAPE" {if $PDF_MODEL->get('page_orientation') eq 'PLL_LANDSCAPE'} selected="selected" {/if}>
								{\App\Language::translate('PLL_LANDSCAPE', $QUALIFIED_MODULE)}
							</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_LANGUAGE_CHOICE', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<select class="select2 form-control" id="language" name="language">
							<option value="">{\App\Language::translate('LBL_DEFAULT')}</option>
							{foreach from=\App\Language::getAll() key=CODE item=NAME}
								<option value="{$CODE}" {if $PDF_MODEL->get('language') eq $CODE} selected="selected" {/if}>
									{\App\Language::translate($NAME, $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_FILENAME', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<input type="text" name="filename" class="form-control" value="{$PDF_MODEL->get('filename')}" id="filename" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_VISIBILITY', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="col-sm-6 controls">
						{assign 'VISIBILITY' explode(',',$PDF_MODEL->get('visibility'))}
						<select class="select2 form-control rtl" data-tags="false" id="visibility" name="visibility" multiple="multiple" data-validation-engine="validate[required]">
							<option value="PLL_LISTVIEW" {if in_array('PLL_LISTVIEW', $VISIBILITY)}selected="selected"{/if}>{\App\Language::translate('PLL_LISTVIEW', $QUALIFIED_MODULE)}</option> 
							<option value="PLL_DETAILVIEW" {if in_array('PLL_DETAILVIEW', $VISIBILITY)}selected="selected"{/if}>{\App\Language::translate('PLL_DETAILVIEW', $QUALIFIED_MODULE)}</option> 
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_DEFAULT_TPL', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6">
						{if $PDF_MODEL->get('default') === 0}
							{assign 'DEFAULT' false}
						{else}
							{assign 'DEFAULT' true}
						{/if}
						<input type="checkbox" id="default" name="default" value="1" {if $DEFAULT eq 'true'}checked="checked"{/if} />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_GENERATE_ONE_PDF', $QUALIFIED_MODULE)}
						<span class="js-popover-tooltip delay0" data-js="popover" data-placement="top"
							  data-content="{\App\Language::translate('LBL_GENERATE_ONE_PDF_INFO',$QUALIFIED_MODULE)}">
							<span class="fas fa-info-circle"></span>
						</span>
					</label>
					<div class="col-sm-6">
						{if $PDF_MODEL->get('one_pdf') == 0}
							{assign 'ONE_PDF' false}
						{else}
							{assign 'ONE_PDF' true}
						{/if}
						<input type="checkbox" id="one_pdf" name="one_pdf" value="1" {if $ONE_PDF eq 'true'}checked="checked"{/if} />
					</div>
				</div>
			</div>
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
