{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-PDF-Step2 pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step2" class="form-horizontal">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step3"/>
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" class="step" value="2"/>
			<input type="hidden" name="record" value="{$RECORDID}"/>
			<input type="hidden" name="module_name" value="{$PDF_MODEL->get('module_name')}"/>
			<input type="hidden" name="watermark_image" value=""/>

			<div class="row">
				<div class="col-12">

					<div class="mb-2">
						<div class="card">
							<div class="card-header">{\App\Language::translate('LBL_HEADER_DETAILS',$QUALIFIED_MODULE)}</div>
							<div class="card-body">
								<div class="row mb-4 pb-4 border-bottom">
									{include file='layouts/basic/modules/Vtiger/VariablePanel.tpl' SELECTED_MODULE=$SELECTED_MODULE PARSER_TYPE='pdf'}
								</div>
								<div class="controls">
									<textarea class="form-control w-100 js-editor" name="header_content" id="header_content" data-js="ckeditor">{$PDF_MODEL->get('header_content')}</textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="mb-2">
						<div class="card">
							<div class="card-header">{\App\Language::translate('LBL_BODY_DETAILS',$QUALIFIED_MODULE)}</div>
							<div class="card-body">
								<div class="row">
									{include file='layouts/basic/modules/Vtiger/VariablePanel.tpl' SELECTED_MODULE=$SELECTED_MODULE PARSER_TYPE='pdf'}
								</div>
								<hr class="my-4 pb-2 border-bottom-0"/>
								<div class="controls">
									<textarea class="form-control w-100 js-editor" name="body_content" id="body_content" data-js="ckeditor">{$PDF_MODEL->get('body_content')}</textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="mb-2">
						<div class="card">
							<div class="card-header">{\App\Language::translate('LBL_FOOTER_DETAILS',$QUALIFIED_MODULE)}</div>
							<div class="card-body">
								<div class="row">
									{include file='layouts/basic/modules/Vtiger/VariablePanel.tpl' SELECTED_MODULE=$SELECTED_MODULE PARSER_TYPE='pdf'}
								</div>
								<hr class="my-4 pb-2 border-bottom-0"/>
								<div class="controls">
									<textarea class="form-control js-editor" name="footer_content" id="footer_content" data-js="ckeditor">{$PDF_MODEL->get('footer_content')}</textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="mb-2">
						<div class="card">
							<div class="card-header">{\App\Language::translate('LBL_WATERMARK_DETAILS',$QUALIFIED_MODULE)}</div>
							<div class="card-body">
								<div class="row">
									{include file='layouts/basic/modules/Vtiger/VariablePanel.tpl' SELECTED_MODULE=$SELECTED_MODULE PARSER_TYPE='pdf'}
								</div>
								<hr class="my-4 pb-2 border-bottom-0"/>
								<div class="row">
									<div class="form-group col-12 col-xl-6 col-xxl-3">
										<div class="row">
											<div class="col-12 col-sm-6">
												<label>{\App\Language::translate('LBL_WATERMARK_TYPE', $QUALIFIED_MODULE)}</label>
											</div>
											<div class="col-sm-6 controls">
												<select class="select2 form-control" id="watermark_type" name="watermark_type"
												        required="true">
													{foreach from=$PDF_MODEL->getWatermarkType() key=VALUE item=LABEL}
														<option value="{$VALUE}" {if $PDF_MODEL->get('watermark_type') eq $VALUE} selected {/if}>
															{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}
														</option>
													{/foreach}
												</select>
											</div>
										</div>
									</div>
									<div class="form-group col-12 col-sm-12 col-xl-6 col-xxl-3 watertext {if $PDF_MODEL->get('watermark_type') neq $WATERMARK_TEXT}d-none{/if}">
										<div class="row">
											<div class="col-12 col-sm-6">
												<label>{\App\Language::translate('LBL_WATERMARK_TEXT', $QUALIFIED_MODULE)}</label>
											</div>
											<div class="col-sm-6 controls">
												<input type="text" name="watermark_text" class="form-control"
												       value="{$PDF_MODEL->get('watermark_text')}" id="watermark_text"/>
											</div>
										</div>
									</div>
									<div class="form-group col-12 col-sm-6 col-xl-6 col-xxl-3 watertext {if $PDF_MODEL->get('watermark_type') neq $WATERMARK_TEXT}d-none{/if}">
										<div class="row">
											<div class="col-12 col-sm-6">
												<label>{\App\Language::translate('LBL_WATERMARK_SIZE', $QUALIFIED_MODULE)}</label>
											</div>
											<div class="col-sm-6 controls">
												<input type="number" name="watermark_size" class="form-control"
												       value="{intval($PDF_MODEL->get('watermark_size'))}" id="watermark_size" min="0"
												       max="99"/>
											</div>
										</div>
									</div>
									<div class="form-group col-12 col-sm-6 col-xl-6 col-xxl-3 watertext {if $PDF_MODEL->get('watermark_type') neq $WATERMARK_TEXT}d-none{/if}">
										<div class="row">
											<div class="col-12 col-sm-6">
												<label>{\App\Language::translate('LBL_WATERMARK_ANGLE', $QUALIFIED_MODULE)}</label>
											</div>
											<div class="col-sm-6 controls">
												<input type="number" name="watermark_angle" class="form-control"
												       value="{intval($PDF_MODEL->get('watermark_angle'))}" id="watermark_angle" min="0"
												       max="360"/>
											</div>
										</div>
									</div>
									<div class="form-group col-12 col-xl-5 waterimage {if $PDF_MODEL->get('watermark_type') eq $WATERMARK_TEXT}d-none{/if}">
										<div class="row">
											<div class="col-12 col-sm-4">
												<label>{\App\Language::translate('LBL_WATERMARK_IMAGE', $QUALIFIED_MODULE)}</label>
											</div>
											<div class="col-sm-8 controls">
												<div class="row">
													<div id="watermark" class="col-3">
														{if $PDF_MODEL->get('watermark_image')}
															<img src="{\App\Fields\File::getImageBaseData($PDF_MODEL->get('watermark_image'))}" class="w-100"/>
														{/if}
													</div>
													<div class="col-9">
														<input type="file" name="watermark_image_file" accept="images/*" class="form-control" data-validation-engine='validate[required]' id="watermark_image"/>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group col-12 col-xxl-4 pt-2 pt-xxl-0 text-center waterimage {if $PDF_MODEL->get('watermark_type') eq $WATERMARK_TEXT}d-none{/if}">
										<button id="uploadWM" class="btn btn-success mr-2">{\App\Language::translate('LBL_UPLOAD_WM', $QUALIFIED_MODULE)}</button>
										<button id="deleteWM" class="btn btn-danger {if $PDF_MODEL->get('watermark_image') eq ''}d-none{/if}">{\App\Language::translate('LBL_DELETE_WM', $QUALIFIED_MODULE)}</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

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
