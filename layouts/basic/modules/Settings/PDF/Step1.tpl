{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-PDF-Step1 -->
	{if empty($RECORDID)}
		{assign var=RECORDID value=''}
	{/if}
	<div class="pdfTemplateContents" data-js="container">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step1" class="form-horizontal" data-js="container">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step2" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="1" />
			<input type="hidden" name="record" value="{$RECORDID}" />
			<div class="row">
				<div class="col-12 mb-3">
					<div class="card">
						<div class="card-header">
							<span class="fa fa-copy mr-2"></span>{\App\Language::translate('LBL_VARIABLES',$QUALIFIED_MODULE)}
						</div>
						<div class="card-body">
							<div class="row js-variable-panel" data-js="container">
								{include file='layouts/basic/modules/Vtiger/VariablePanel.tpl' SELECTED_MODULE=$SELECTED_MODULE PARSER_TYPE='pdf'}
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-xl-6 col-xxl-4 mb-3">
					<div class="card">
						<div class="card-header">
							<span class="fa fa-edit mr-2"></span>{\App\Language::translate('LBL_ENTER_BASIC_DETAILS',$QUALIFIED_MODULE)}
						</div>
						<div class="card-body">
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)}
									<span class="redColor">*</span>
								</label>
								<div class="col-sm-6 controls">
									<select class="select2 form-control" id="status" name="status" required="true">
										<option value="1" {if $PDF_MODEL->get('status') eq 1} selected {/if}>
											{\App\Language::translate('PLL_ACTIVE', $QUALIFIED_MODULE)}
										</option>
										<option value="0" {if $PDF_MODEL->get('status') eq 0} selected {/if}>
											{\App\Language::translate('PLL_INACTIVE', $QUALIFIED_MODULE)}
										</option>
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_GENERATOR_ENGINE', $QUALIFIED_MODULE)}
									<span class="redColor">*</span>
								</label>
								<div class="col-sm-6 controls">
									<div class="input-group">
										<select class="select2 form-control" id="generator" name="generator" required="true">
											{foreach key=DRIVER_NAME item=DRIVER_LABEL from=\App\Pdf\Pdf::getSupportedDrivers()}
												<option value="{$DRIVER_NAME}" {if $PDF_MODEL->get('generator') eq $DRIVER_NAME}selected="selected" {/if}>
													{\App\Language::translate($DRIVER_LABEL, $QUALIFIED_MODULE)}
												</option>
											{/foreach}
										</select>
										<div class="input-group-append">
											<span class="input-group-text js-popover-tooltip" data-content="{\App\Language::translate('LBL_GENERATOR_ENGINE_DESC',$QUALIFIED_MODULE)}">
												<span class="fas fa-info-circle"></span>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_PRIMARY_NAME', $QUALIFIED_MODULE)}
									<span class="redColor">*</span>
								</label>
								<div class="col-sm-6 controls">
									<div class="input-group">
										<input type="text" name="primary_name" class="form-control" data-validation-engine='validate[required]' value="{$PDF_MODEL->get('primary_name')}" id="primary_name" />
										<div class="input-group-append">
											<span class="input-group-text js-popover-tooltip" data-content="{\App\Language::translate('LBL_USE_VARIABLES',$QUALIFIED_MODULE)}"><span class="fas fa-info-circle"></span></span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_SECONDARY_NAME', $QUALIFIED_MODULE)}
									<span class="redColor">*</span>
								</label>
								<div class="col-sm-6 controls">
									<div class="input-group">
										<input type="text" name="secondary_name" class="form-control" data-validation-engine='validate[required]' value="{$PDF_MODEL->get('secondary_name')}" id="secondary_name" />
										<div class="input-group-append">
											<span class="input-group-text js-popover-tooltip" data-content="{\App\Language::translate('LBL_USE_VARIABLES',$QUALIFIED_MODULE)}"><span class="fas fa-info-circle"></span></span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
									<span class="redColor">*</span>
								</label>
								<div class="col-sm-6 controls">
									<select class="select2 form-control" id="moduleName" name="module_name" required="required" data-validation-engine="validate[required]" data-js="change">
										{foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
											<option value="{$MODULE_MODEL->getName()}" {if $SELECTED_MODULE == $MODULE_MODEL->getName()} selected {/if}>
												{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
											</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_METATAGS', $QUALIFIED_MODULE)}
								</label>
								<div class="col-sm-6 controls">
									<input type="checkbox" name="metatags_status" id="metatags_status" value="1" class="checkboxForm" {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}checked="checked" {/if} data-js="click" />
								</div>
							</div>
							<div class="form-group row metatags {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}d-none{/if}">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_META_TITLE', $QUALIFIED_MODULE)}
								</label>
								<div class="col-sm-6 controls">
									<div class="input-group">
										<input type="text" name="meta_title" class="form-control" value="{$PDF_MODEL->get('meta_title')}" id="meta_title" />
										<div class="input-group-append">
											<span class="input-group-text js-popover-tooltip" data-content="{\App\Language::translate('LBL_USE_VARIABLES',$QUALIFIED_MODULE)}"><span class="fas fa-info-circle"></span></span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group row metatags {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}d-none{/if}">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_META_AUTHOR', $QUALIFIED_MODULE)}
								</label>
								<div class="col-sm-6 controls">
									<div class="input-group">
										<input type="text" name="meta_author" class="form-control" value="{$PDF_MODEL->get('meta_author')}" id="meta_author" />
										<div class="input-group-append">
											<span class="input-group-text js-popover-tooltip" data-content="{\App\Language::translate('LBL_USE_VARIABLES',$QUALIFIED_MODULE)}"><span class="fas fa-info-circle"></span></span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group row metatags {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}d-none{/if}">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_META_SUBJECT', $QUALIFIED_MODULE)}
								</label>
								<div class="col-sm-6 controls">
									<div class="input-group">
										<input type="text" name="meta_subject" class="form-control" value="{$PDF_MODEL->get('meta_subject')}" id="meta_subject" />
										<div class="input-group-append">
											<span class="input-group-text js-popover-tooltip" data-content="{\App\Language::translate('LBL_USE_VARIABLES',$QUALIFIED_MODULE)}"><span class="fas fa-info-circle"></span></span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group row metatags {if $PDF_MODEL->get('metatags_status') eq true || $RECORDID eq ''}d-none{/if}">
								<label class="col-sm-12 col-form-label">
									{\App\Language::translate('LBL_META_KEYWORDS', $QUALIFIED_MODULE)}
								</label>
								<div class="col-sm-12 controls">
									{assign 'KEYWORDS' explode(',',$PDF_MODEL->get('meta_keywords'))}

									<select class="select2 form-control" id="meta_keywords" name="meta_keywords" data-select="tags" multiple="multiple">
										{foreach item=KEYWORD from=$KEYWORDS}
											<option value="{$KEYWORD}" selected="selected">{$KEYWORD}</option>
										{/foreach}
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-xl-6 col-xxl-4 mb-3 order-1 order-xl-2 order-xxl-1">
					<div class="card">
						<div class="card-header">
							<span class="fa fa-cogs mr-2"></span> {\App\Language::translate('LBL_DOCUMENT_SETTINGS_DETAILS',$QUALIFIED_MODULE)}
						</div>
						<div class="card-body">
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_PAGE_FORMAT', $QUALIFIED_MODULE)}
									<span class="redColor">*</span>
								</label>
								<div class="col-sm-6 controls">
									<select class="select2 form-control rtl" id="page_format" name="page_format" data-validation-engine="validate[required]">
										<option value="" selected="">{\App\Language::translate('LBL_SELECT', $QUALIFIED_MODULE)}</option>
										{foreach item=FORMAT from=\App\Pdf\Pdf::getPageFormats()}
											<option value="{$FORMAT}" {if $PDF_MODEL->get('page_format') eq $FORMAT} selected="selected" {/if}>
												{\App\Language::translate($FORMAT, $QUALIFIED_MODULE)}
											</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_MAIN_MARGIN', $QUALIFIED_MODULE)}
								</label>
								{if $PDF_MODEL->get('margin_chkbox') === 1}
									{assign 'MARGIN_CHECKED' true}
								{else}
									{assign 'MARGIN_CHECKED' false}
								{/if}
								<div class="col-sm-6">
									<input type="checkbox" id="margin_chkbox" name="margin_chkbox" value="1" {if $MARGIN_CHECKED eq 'true'}checked="checked" {/if} data-js="click" />
								</div>
							</div>
							<div class="form-group row margin_inputs {if $MARGIN_CHECKED eq 'true'}d-none{/if}">
								<label class="col-sm-6 col-form-label">{\App\Language::translate('LBL_MARGIN', $QUALIFIED_MODULE)}</label>
								<div class="col-sm-6">
									<div class="form-row d-flex justify-content-center mx-auto">
										<div class="col-md-6 mb-2">
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text" id="margin_top"><span class="fas fa-arrow-up" title="{\App\Language::translate('LBL_TOP', $QUALIFIED_MODULE)}"></span></span>
												</div>
												<input type="text" class="form-control" aria-describedby="margin_top" name="margin_top" id="margin_top" value="{$PDF_MODEL->get('margin_top')}" placeholder="{\App\Language::translate('LBL_TOP', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_TOP_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
											</div>
										</div>
										<div class="col-md-6 mb-2">
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text" id="margin_right"><span class="fas fa-arrow-right" title="{\App\Language::translate('LBL_RIGHT', $QUALIFIED_MODULE)}"></span></span>
												</div>
												<input type="text" class="form-control" aria-describedby="margin_right" name="margin_right" id="margin_right" value="{$PDF_MODEL->get('margin_right')}" placeholder="{\App\Language::translate('LBL_RIGHT', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_RIGHT_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
											</div>
										</div>
										<div class="col-md-6 mb-2">
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text" id="margin_bottom"><span class="fas fa-arrow-down" title="{\App\Language::translate('LBL_BOTTOM', $QUALIFIED_MODULE)}"></span></span>
												</div>
												<input type="text" class="form-control" aria-describedby="margin_bottom" name="margin_bottom" id="margin_bottom" value="{$PDF_MODEL->get('margin_bottom')}" placeholder="{\App\Language::translate('LBL_BOTTOM', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_BOTTOM_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
											</div>
										</div>
										<div class="col-md-6 mb-2">
											<div class="input-group">
												<div class="input-group-prepend">
													<span class="input-group-text" id="margin_left"><span class="fas fa-arrow-left" title="{\App\Language::translate('LBL_LEFT', $QUALIFIED_MODULE)}"></span></span>
												</div>
												<input type="text" class="form-control" aria-describedby="margin_left" name="margin_left" id="margin_left" value="{$PDF_MODEL->get('margin_left')}" placeholder="{\App\Language::translate('LBL_LEFT', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_LEFT_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="col-12  col-lg-6 mb-2">
											<label class="col-form-label text-center u-text-ellipsis--no-hover">{\App\Language::translate('LBL_HEADER_HEIGHT', $QUALIFIED_MODULE)}</label>
											<input type="text" class="form-control" name="header_height" id="header_height" value="{$PDF_MODEL->get('header_height')}" placeholder="{\App\Language::translate('LBL_HEADER_HEIGHT', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_HEADER_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
										</div>
										<div class="col-md-12 col-lg-6 mb-2">
											<label class="col-form-label text-center u-text-ellipsis--no-hover">{\App\Language::translate('LBL_FOOTER_HEIGHT', $QUALIFIED_MODULE)}</label>
											<input type="text" class="form-control" name="footer_height" id="footer_height" value="{$PDF_MODEL->get('footer_height')}" placeholder="{\App\Language::translate('LBL_FOOTER_HEIGHT', $QUALIFIED_MODULE)}" title="{\App\Language::translate('LBL_FOOTER_IN_MILIMETERS', $QUALIFIED_MODULE)}" />
										</div>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
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
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
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
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_FILENAME', $QUALIFIED_MODULE)}
								</label>
								<div class="col-sm-6 controls">
									<div class="input-group">
										<input type="text" name="filename" class="form-control" value="{$PDF_MODEL->get('filename')}" id="filename" />
										<div class="input-group-append">
											<span class="input-group-text js-popover-tooltip" data-content="{\App\Language::translate('LBL_USE_VARIABLES',$QUALIFIED_MODULE)}"><span class="fas fa-info-circle"></span></span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_DEFAULT_TPL', $QUALIFIED_MODULE)}
								</label>
								<div class="col-sm-6">
									<input type="checkbox" id="default" name="default" value="1" {if !empty($PDF_MODEL->get('default'))}checked="checked" {/if} />
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-6 col-form-label">
									{\App\Language::translate('LBL_GENERATE_ONE_PDF', $QUALIFIED_MODULE)}
									<span class="js-popover-tooltip delay0" data-js="popover" data-placement="top" data-content="{\App\Language::translate('LBL_GENERATE_ONE_PDF_INFO',$QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle ml-1"></span>
									</span>
								</label>
								<div class="col-sm-6">
									{if $PDF_MODEL->get('one_pdf') == 0}
										{assign 'ONE_PDF' false}
									{else}
										{assign 'ONE_PDF' true}
									{/if}
									<input type="checkbox" id="one_pdf" name="one_pdf" value="1" {if $ONE_PDF eq 'true'}checked="checked" {/if} />
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-xl-6 col-xxl-4 mb-2  order-2 order-xl-1 order-xxl-2">
					<div class="card">
						<div class="card-header">
							<span class="fa fa-user-shield mr-2"></span> {\App\Language::translate('LBL_PERMISSIONS_DETAILS',$QUALIFIED_MODULE)}
						</div>
						<div class="card-body">
							<div class="form-group row">
								<div class="col-md-12 col-form-label">
									{\App\Language::translate('LBL_GROUP_MEMBERS', 'Settings:Groups')}
								</div>
								<div class="col-md-12 controls">
									<select class="select2 form-control" multiple="true" name="template_members[]" data-placeholder="{\App\Language::translate('LBL_ADD_USERS_ROLES', 'Settings:Groups')}">
										{assign 'TEMPLATE_MEMBERS' explode(',',$PDF_MODEL->get('template_members'))}
										{foreach from=Settings_Groups_Member_Model::getAll(false) key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
											<optgroup label="{\App\Language::translate($GROUP_LABEL, $QUALIFIED_MODULE)}">
												{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
													<option value="{$MEMBER->get('id')}" data-member-type="{$GROUP_LABEL}" {if in_array($MEMBER->get('id'), $TEMPLATE_MEMBERS)}selected="true" {/if}>{\App\Language::translate($MEMBER->get('name'), $QUALIFIED_MODULE)}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-12 col-form-label">
									{\App\Language::translate('LBL_VISIBILITY', $QUALIFIED_MODULE)}
									<span class="redColor">*</span>
								</label>
								<div class="col-sm-12 controls">
									{assign 'VISIBILITY' explode(',',$PDF_MODEL->get('visibility'))}
									<select class="select2 form-control rtl" id="visibility" name="visibility" multiple data-validation-engine="validate[required]">
										<option value="PLL_DETAILVIEW" {if in_array('PLL_DETAILVIEW', $VISIBILITY)}selected="selected" {/if}>{\App\Language::translate('PLL_DETAILVIEW', $QUALIFIED_MODULE)}</option>
										<option value="PLL_LISTVIEW" {if in_array('PLL_LISTVIEW', $VISIBILITY)}selected="selected" {/if}>{\App\Language::translate('PLL_LISTVIEW', $QUALIFIED_MODULE)}</option>
										<option value="PLL_RELATEDLISTVIEW" {if in_array('PLL_RELATEDLISTVIEW', $VISIBILITY)}selected="selected" {/if}>{\App\Language::translate('PLL_RELATEDLISTVIEW', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12 mb-3 order-5">
					<div class="card">
						<div class="card-header">
							<span class="fas fa-tint mr-2"></span> {\App\Language::translate('LBL_WATERMARK_DETAILS',$QUALIFIED_MODULE)}
						</div>
						<div class="card-body">
							<div class="row">
								<div class="form-group col-12 col-xl-6 col-xxl-4">
									<div class="row">
										<div class="col-12 col-sm-6">
											<label class="col-form-label">{\App\Language::translate('LBL_WATERMARK_TYPE', $QUALIFIED_MODULE)}</label>
										</div>
										<div class="col-sm-6 controls">
											<select class="select2 form-control" id="watermark_type" name="watermark_type" required="true" data-js="change">
												{foreach from=$PDF_MODEL->getWatermarkType() key=VALUE item=LABEL}
													<option value="{$VALUE}" {if $PDF_MODEL->get('watermark_type') eq $VALUE} selected {/if}>
														{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}
													</option>
												{/foreach}
											</select>
										</div>
									</div>
								</div>
								<div class="form-group col-12 col-sm-6 col-xl-6 col-xxl-4 watertext {if !$PDF_MODEL->isEmpty('watermark_type')}d-none{/if}">
									<div class="row">
										<div class="col-12 col-sm-6">
											<label class="col-form-label">{\App\Language::translate('LBL_WATERMARK_ANGLE', $QUALIFIED_MODULE)}</label>
										</div>
										<div class="col-sm-6 controls">
											<input type="number" name="watermark_angle" class="form-control" value="{intval($PDF_MODEL->get('watermark_angle'))}" id="watermark_angle" min="0" max="360" />
										</div>
									</div>
								</div>
								<div class="form-group col-12 watertext {if !$PDF_MODEL->isEmpty('watermark_type')}d-none{/if}">
									<div class="row">
										<div class="col-12">
											<label class="col-form-label">{\App\Language::translate('LBL_WATERMARK_TEXT', $QUALIFIED_MODULE)}
												<span class="ml-2 js-popover-tooltip" data-content="{\App\Language::translate('LBL_USE_VARIABLES',$QUALIFIED_MODULE)}"><span class="fas fa-info-circle"></span></span></label>
										</div>
										<div class="col-12 controls">
											<textarea name="watermark_text" class="form-control js-editor" id="watermark_text" data-purify-mode="Html">{$PDF_MODEL->get('watermark_text')}</textarea>
										</div>
									</div>
								</div>
								<div class="form-group col-12 col-xl-4 waterimage {if $PDF_MODEL->isEmpty('watermark_type')}d-none{/if}">
									<div class="row">
										<div class="col-12 col-sm-4">
											<label class="col-form-label">{\App\Language::translate('LBL_WATERMARK_IMAGE', $QUALIFIED_MODULE)}</label>
										</div>
										<div class="col-sm-8 controls">
											<div class="row">
												<div id="watermark" class="col-3">
													{if $PDF_MODEL->get('watermark_image')}
														<img src="{\App\Fields\File::getImageBaseData($PDF_MODEL->get('watermark_image'))}" class="w-100" />
													{/if}
												</div>
												<div class="col-9">
													<input type="file" name="watermark_image_file" accept="images/*" class="form-control" id="watermark_image" />
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group col-12 col-xxl-4 pt-2 pt-xxl-0 text-center waterimage {if $PDF_MODEL->isEmpty('watermark_type')}d-none{/if}">
									<button id="deleteWM" class="btn btn-danger {if $PDF_MODEL->get('watermark_image') eq ''}d-none{/if}">{\App\Language::translate('LBL_DELETE_WM', $QUALIFIED_MODULE)}</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="float-right mb-2">
				<button class="btn btn-success mr-1" type="submit" disabled>
					<span class="fas fa-caret-right mr-1"></span>
					{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}
				</button>
				<button class="btn btn-danger cancelLink" type="reset">
					<span class="fas fa-times mr-1"></span>
					{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
				</button>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-PDF-Step1 -->
{/strip}
