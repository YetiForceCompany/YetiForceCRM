{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="validationEngineContainer" id="EditView">
		<form>
			<div class="modal-header">
				{if $RECORD_MODEL}
					<h5 class="modal-title"><span class="yfi yfi-full-editing-view fa-sm mr-1"></span>{\App\Language::translate('LBL_TITLE_EDIT', $QUALIFIED_MODULE)}</h5>
				{else}
					<h5 class="modal-title"><span class="fas fa-plus fa-sm mr-1"></span>{\App\Language::translate('LBL_TITLE_ADDED', $QUALIFIED_MODULE)}</h5>
				{/if}
				<button type="button" class="close" data-dismiss="modal"
						title="{\App\Language::translate('LBL_CLOSE')}">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body form-row">
				<div class="form-group form-row col-sm-12">
					<label class="col-sm-2 col-form-label text-right u-text-small-bold"><span class="redColor">*</span>{\App\Language::translate('LBL_APP_NAME', $QUALIFIED_MODULE)}</label>
					<div class="col-sm-10">
						<input type="text" name="name" data-validation-engine="validate[required]" value="{if $RECORD_MODEL}{$RECORD_MODEL->getName()}{/if}" class="form-control">
					</div>
				</div>
				<div class="form-group form-row col-sm-12">
					<label class="col-sm-2 col-form-label text-right u-text-small-bold"><span class="redColor">*</span>{\App\Language::translate('LBL_PASS', $QUALIFIED_MODULE)}</label>
					<div class="col-sm-10">
						<div class="input-group">
							<input type="password" name="pass" data-validation-engine="validate[required]" value="{if $RECORD_MODEL}{\App\Purifier::encodeHtml(\App\Encryption::getInstance()->decrypt($RECORD_MODEL->get('pass')))}{/if}" class="form-control">
							<span class="input-group-append">
								<button class="btn btn-outline-secondary previewPassword" type="button">
									<span class="fas fa-eye"></span>
								</button>
								<button class="btn btn-outline-secondary copyPassword" data-copy-target='[name="pass"]' type="button">
									<span class="fas fa-copy"></span>
								</button>
							</span>
						</div>
					</div>
				</div>
				<div class="form-group form-row col-sm-12">
					<label class="col-sm-2 col-form-label text-right u-text-small-bold">{\App\Language::translate('Status', $QUALIFIED_MODULE)}</label>
					<div class="col-sm-10">
						<input type="checkbox" {if $RECORD_MODEL && $RECORD_MODEL->get('status') eq 1}checked{/if} name="status">
					</div>
				</div>
				<div class="form-group form-row col-sm-12">
					<label class="col-sm-2 col-form-label text-right u-text-small-bold">{\App\Language::translate('LBL_TYPE_SERVER', $QUALIFIED_MODULE)}</label>
					<div class="col-sm-10">
						<select class="select2 typeServer" {if $RECORD_MODEL} disabled {/if}>
							{foreach from=$TYPES_SERVERS item=TYPE}
								<option value="{$TYPE}" {if $RECORD_MODEL && $TYPE eq  $RECORD_MODEL->get('type')}selected{/if}>
									{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group form-row col-sm-12">
					<label class="col-sm-2 col-form-label text-right u-text-small-bold">
						{\App\Language::translate('LBL_ADDRESS_URL', $QUALIFIED_MODULE)}
						<a href="#" class="js-popover-tooltip ml-2" data-placement="top" data-content="{\App\Language::translate('LBL_ADDRESS_URL_INFO', $QUALIFIED_MODULE)} x.x.x.x , y.y.y.y">
							<i class="fas fa-info-circle"></i>
						</a>
					</label>
					<div class="col-sm-10">
						<input type="text" name="addressUrl" value="{if $RECORD_MODEL}{$RECORD_MODEL->get('acceptable_url')}{/if}" class="form-control">
					</div>
				</div>
				<div class="col-sm-12">
					<div class="alert alert-info mb-0" role="alert">
						<span class="mdi mdi-information-outline u-fs-3x mr-2 float-left"></span>
						{\App\Language::translate('LBL_MODAL_ALERT', $QUALIFIED_MODULE)}
						<pre class="mb-0 mt-2">{\App\Purifier::encodeHtml('Authorization: Basic base64_encode($name . \':\' . $password)')}</pre>
					</div>
				</div>
			</div>
		</form>
		{include file=App\Layout::getTemplatePath('Modals/Footer.tpl') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
	</div>
{/strip}
