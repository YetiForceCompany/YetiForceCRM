{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-ConfigEditor-Edit -->
	<div>
		<form id="ConfigEditorForm" class="form-horizontal js-form-ajax-submit js-validate-form" data-detail-url="{$MODEL->getDetailViewUrl()}" method="POST">
			<input name="module" type="hidden" value="{$MODULE_NAME}" />
			<input name="parent" type="hidden" value="Settings" />
			<input name="action" type="hidden" value="SaveAjax" />
			<input name="type" type="hidden" value="Main" />
			<table class="table table-bordered table-sm themeTableColor">
				<thead>
					<tr class="blockHeader">
						<th colspan="2"
							class="{$WIDTHTYPE}">{\App\Language::translate('LBL_MAIN_CONFIG', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach key=FIELD_NAME item=FIELD_LABEL from=$MODEL->listFields}
						{assign var="FIELD_MODEL" value=$MODEL->getFieldInstanceByName($FIELD_NAME)->set('fieldvalue',$MODEL->get($FIELD_NAME))}
						<tr>
							<td width="30%" class="{$WIDTHTYPE} textAlignRight">
								<div class="form-row">
									<label class="col-form-label col-md-4 u-text-small-bold text-left text-md-right">
										{\App\Language::translate($FIELD_LABEL, $QUALIFIED_MODULE)}
									</label>
									{if $FIELD_NAME eq 'upload_maxsize'}
										<div class="input-group col-md-3 fieldValue">
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=null }
											<div class="input-group-append">
												<span class="input-group-text">{\App\Language::translate('LBL_MB', $QUALIFIED_MODULE)}</span>
											</div>
										</div>
										<label class="col-form-label">
											(upload_max_filesize: {vtlib\Functions::showBytes(\App\Config::getMaxUploadSize(false))})
										</label>
									{else}
										<div class="col-md-3 fieldValue">
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=null }
										</div>
									{/if}
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<div class="c-form__action-panel">
				<button class="btn btn-success js-save" type="submit">
					<span class="fas fa-check mr-2"></span>
					{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
				</button>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-ConfigEditor-Edit -->
{/strip}
