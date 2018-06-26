{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="pdfTemplateContents">
		<form name="EditPdfTemplate" action="index.php" method="post" id="pdf_step8" class="form-horizontal" enctype="multipart/form-data">
			<input type="hidden" name="module" value="PDF">
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="8" />
			<input type="hidden" name="record" value="{$RECORDID}" />
			<input type="hidden" name="watermark_image" value="{$PDF_MODEL->get('watermark_image')}" />
			<div class="padding1per stepBorder">
				<label>
					<strong>{\App\Language::translate('LBL_STEP_N',$QUALIFIED_MODULE, 8)|replace:'%d':'8'}: {\App\Language::translate('LBL_WATERMARK_DETAILS',$QUALIFIED_MODULE)}</strong>
				</label>
				<br />
				<div class="form-group">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_WATERMARK_TYPE', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<select class="chzn-select form-control" id="watermark_type" name="watermark_type" required="true">
							{foreach from=$PDF_MODEL->getWatermarkType() key=VALUE item=LABEL}
								<option value="{$VALUE}" {if $PDF_MODEL->get('watermark_type') eq $VALUE} selected {/if}>
									{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group watertext {if $PDF_MODEL->get('watermark_type') neq $WATERMARK_TEXT}d-none{/if}">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_WATERMARK_TEXT', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<input type="text" name="watermark_text" class="form-control" value="{$PDF_MODEL->get('watermark_text')}" id="watermark_text" />
					</div>
				</div>
				<div class="form-group watertext {if $PDF_MODEL->get('watermark_type') neq $WATERMARK_TEXT}d-none{/if}">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_WATERMARK_SIZE', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<input type="number" name="watermark_size" class="form-control" value="{intval($PDF_MODEL->get('watermark_size'))}" id="watermark_size" min="0" max="99" />
					</div>
				</div>
				<div class="form-group watertext {if $PDF_MODEL->get('watermark_type') neq $WATERMARK_TEXT}d-none{/if}">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_WATERMARK_ANGLE', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<input type="number" name="watermark_angle" class="form-control" value="{intval($PDF_MODEL->get('watermark_angle'))}" id="watermark_angle" min="0" max="360" />
					</div>
				</div>
				<div class="form-group waterimage {if $PDF_MODEL->get('watermark_type') eq $WATERMARK_TEXT}d-none{/if}">
					<label class="col-sm-3 col-form-label">
						{\App\Language::translate('LBL_WATERMARK_IMAGE', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						<div id="watermark">
							{if $PDF_MODEL->get('watermark_image')}
								<img src="{$PDF_MODEL->get('watermark_image')}" class="col-md-9" />
							{/if}
						</div>
						<input type="file" name="watermark_image_file" accept="images/*" class="form-control" data-validation-engine='validate[required]' id="watermark_image" />
					</div>
				</div>
				<div class="form-group waterimage form-row {if $PDF_MODEL->get('watermark_type') eq $WATERMARK_TEXT}d-none{/if}">
					<div class="pl-4 col-sm-2 controls">
						<button id="deleteWM" class="btn btn-danger {if $PDF_MODEL->get('watermark_image') eq ''}d-none{/if}">{\App\Language::translate('LBL_DELETE_WM', $QUALIFIED_MODULE)}</button>
					</div>
					<div class="col-sm-2 controls">
						<button id="uploadWM" class="btn btn-success float-right d-flex justify-content-sm-start">{\App\Language::translate('LBL_UPLOAD_WM', $QUALIFIED_MODULE)}</button>
					</div>
				</div>
			</div>
			<br />
			<div class="float-right">
				<button class="btn btn-danger backStep" type="button"><strong>{\App\Language::translate('LBL_BACK', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
				<button class="btn btn-success" type="submit"><strong>{\App\Language::translate('LBL_FINISH', $QUALIFIED_MODULE)}</strong></button>
			</div>
		</form>
	</div>
{/strip}
