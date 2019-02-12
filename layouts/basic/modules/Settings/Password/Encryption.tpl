{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Password-Encryption verticalScroll">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="encryptionContainer mt-3">
			<div class="alert alert-info alert-dismissible fade show" role="alert">
				{App\Language::translate('LBL_ENCRYPT_DESCRIPTION', $QUALIFIED_MODULE)}
				<button type="button" class="close" data-dismiss="alert">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			{if !$ENCRYPT->isActive()}
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					{App\Language::translate('LBL_ENCRYPT_IS_NOT_ACTIVE', $QUALIFIED_MODULE)}
					<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			{/if}
			{if $CRON_TASK->isDisabled()}
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					{App\Language::translate('LBL_ENCRYPTION_CRON_BATCH_METHODS', $QUALIFIED_MODULE)}
					<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			{/if}
			{if $IS_RUN_ENCRYPT}
				<div class="alert alert-info alert-dismissible fade show" role="alert">
					{App\Language::translate('LBL_ENCRYPTION_RUN', $QUALIFIED_MODULE)}
					<button type="button" class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			{/if}
			<input type="hidden" name="lengthVectors"
				   value="{\App\Purifier::encodeHtml(\App\Json::encode($MAP_LENGTH_VECTORS_METHODS))}">
			<form class="formEncryption">
				<input type="hidden" name="parent" value="Settings">
				<input type="hidden" name="module" value="{$MODULE}">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="mode" value="encryption">
				<div class="form-group row">
					<label for="inputPassword"
						   class="col-12 col-lg-2 col-form-label">{App\Language::translate('LBL_METHOD', $QUALIFIED_MODULE)}</label>
					<div class="col-12 col-lg-4">
						<select name="methods" class="select2"
								data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}"
								data-select="allowClear">
							<optgroup class="p-0">
								<option value="">{App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
							</optgroup>
							<optgroup label="{\App\Language::translate('LBL_RECOMENDED_METHODS', $QUALIFIED_MODULE)}">
								{foreach from=$RECOMENDED_METHODS item=METHOD}
									<option value="{$METHOD}"
											{if $ENCRYPT->get('method') === $METHOD}selected{/if}>{$METHOD}</option>
								{/foreach}
							</optgroup>
							<optgroup label="{\App\Language::translate('LBL_OTHER_METHODS', $QUALIFIED_MODULE)}">
								{foreach from=$AVAILABLE_METHODS item=METHOD}
									<option value="{$METHOD}"
											{if $ENCRYPT->get('method') === $METHOD}selected{/if}>{$METHOD}</option>
								{/foreach}
							</optgroup>
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-12 col-lg-2 col-form-label">{App\Language::translate('LBL_PASSWORD', $QUALIFIED_MODULE)}</label>
					<div class="col-12 col-lg-4">
						<div class="input-group">
							<input type="password" name="password" class="form-control"
								   {if !$ENCRYPT->isEmpty('method') && $MAP_LENGTH_VECTORS_METHODS[$ENCRYPT->get('method')] === 0}disabled="disabled"
								   {/if}data-validation-engine="{if $ENCRYPT->isEmpty('method')}validate[required]{else}validate[required,maxSize[{$MAP_LENGTH_VECTORS_METHODS[$ENCRYPT->get('method')]}],minSize[{$MAP_LENGTH_VECTORS_METHODS[$ENCRYPT->get('method')]}]]{/if}"
								   value="{\App\Purifier::encodeHtml($ENCRYPT->get('vector'))}">
							<span class="input-group-append">
								<button class="btn btn-outline-secondary previewPassword" type="button">
									<span class="fas fa-eye"></span>
								</button>
							</span>
						</div>
					</div>
				</div>
				<div class="c-form__action-panel">
					<button type="submit" class="btn btn-xs btn-success">
						<span class="fas fa-check"></span><strong>{App\Language::translate('LBL_SAVE')}</strong>
					</button>
				</div>
			</form>
		</div>
	</div>
{/strip}
