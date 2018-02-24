{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="verticalScroll">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="encryptionContainer marginTop10">
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
			<input type="hidden" name="lengthVectors" value="{\App\Purifier::encodeHtml(\App\Json::encode($MAP_LENGTH_VECTORS_METHODS))}">
			<form class="formEncryption">
				<input type="hidden" name="parent" value="Settings">
				<input type="hidden" name="module" value="{$MODULE}">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="mode" value="encryption">
				<div class="form-group row">
					<label for="inputPassword" class="col-12 col-lg-2 col-form-label"><span class="redColor">*</span>{App\Language::translate('LBL_METHOD', $QUALIFIED_MODULE)}</label>
					<div class="col-12 col-lg-4">
						<select class="select2" name="methods" data-validation-engine="validate[required]">
							{foreach from=$AVAILABLE_METHODS item=METHOD}
								<option></option>
								<option value="{$METHOD}" {if $ENCRYPT->get('method') === $METHOD}selected{/if}>{$METHOD}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-12 col-lg-2 col-form-label"><span class="redColor">*</span>{App\Language::translate('LBL_PASSWORD', $QUALIFIED_MODULE)}</label>
					<div class="col-12 col-lg-4">
						<div class="input-group">
							<input type="password" name="password" class="form-control" {if !$ENCRYPT->isEmpty('method') && $MAP_LENGTH_VECTORS_METHODS[$ENCRYPT->get('method')] === 0}disabled="disabled"{/if}data-validation-engine="{if $ENCRYPT->isEmpty('method')}validate[required]{else}validate[required,maxSize[{$MAP_LENGTH_VECTORS_METHODS[$ENCRYPT->get('method')]}],minSize[{$MAP_LENGTH_VECTORS_METHODS[$ENCRYPT->get('method')]}]]{/if}" value="{$ENCRYPT->get('vector')}">
							<span class="input-group-append">
								<button class="btn btn-outline-secondary" type="button" onmousedown="password.type = 'text';" onmouseup="password.type = 'password';" onmouseout="password.type = 'password';">
									<span class="fas fa-eye"></span>
								</button>
							</span>
						</div>
					</div>
				</div>
				<div class="formActionsPanel">
					<button type="submit" class="btn btn-xs btn-success"><span class="fas fa-check"></span>&nbsp;&nbsp;{App\Language::translate('LBL_SAVE')}</button>
				</div>
			</form>
		</div>
	</div>
{/strip}
