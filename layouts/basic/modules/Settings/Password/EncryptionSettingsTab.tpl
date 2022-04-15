{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Password-EncryptionSettingsTab -->
	<div class="alert alert-info alert-dismissible fade show" role="alert">
		<span class="mdi mdi-information-outline mr-2 u-fs-2em"></span>
		{App\Language::translate('LBL_ENCRYPT_DESCRIPTION', $QUALIFIED_MODULE)}
		<button type="button" class="close" data-dismiss="alert">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	{assign var=ENCRYPT value=\App\Encryption::getInstance()}
	{if !$ENCRYPT->isActive()}
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<span class="mdi mdi-alert mr-2 u-fs-lg float-left"></span>
			{App\Language::translate('LBL_ENCRYPT_IS_NOT_ACTIVE', $QUALIFIED_MODULE)}
		</div>
	{/if}
	{if $CRON_TASK->isDisabled()}
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<span class="mdi mdi-alert mr-2 u-fs-lg float-left"></span>
			{App\Language::translate('LBL_ENCRYPTION_CRON_BATCH_METHODS', $QUALIFIED_MODULE)}
		</div>
	{/if}
	{if $ENCRYPT->isReady()}
		<div class="alert alert-danger alert-dismissible fade show " role="alert">
			<div class="row">
				<div class="col float-left"><span class="mdi mdi-progress-clock mr-2 u-fs-lg "></span>
					{App\Language::translate('LBL_ENCRYPTION_WAITING', $QUALIFIED_MODULE)}</div>
				<div class="float-right">
					{if $CRON_TASK->getLastStartDateTime()}
						{App\Language::translate('LBL_CRON_TASK_LAST_START', 'Settings:CronTasks')}: {$CRON_TASK->getLastStartDateTime()}
					{/if}
				</div>
			</div>
		</div>
	{elseif $ENCRYPT->isRunning()}
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<span class="mdi mdi-progress-wrench mr-2 u-fs-lg float-left"></span>
			{App\Language::translate('LBL_ENCRYPTION_RUN', $QUALIFIED_MODULE)}
		</div>
	{/if}
	<input type="hidden" name="lengthVectors" value="{\App\Purifier::encodeHtml(\App\Json::encode($MAP_LENGTH_VECTORS_METHODS))}">
	<form class="formEncryption">
		<input type="hidden" name="parent" value="Settings">
		<input type="hidden" name="module" value="{$MODULE_NAME}">
		<input type="hidden" name="action" value="Save">
		<input type="hidden" name="mode" value="encryption">
		<input type="hidden" name="target" value="{\App\Encryption::TARGET_SETTINGS}">
		<table class="table table-bordered table-sm themeTableColor">
			<thead>
				<tr class="blockHeader">
					<th colspan="2" class="mediumWidthType">
						<span class="fas fa-key mr-2"></span>
						{\App\Language::translate('LBL_ENCRYPTION_CONFIG', $QUALIFIED_MODULE)}
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="u-w-37per px-2">
						<label class="muted float-right col-form-label u-text-small-bold">
							{\App\Language::translate('LBL_METHOD', $QUALIFIED_MODULE)}
						</label>
					</td>
					<td class="border-left-0">
						<div class="form-row px-3">
							<div class="col-5 px-0">
								<select name="methods" class="select2 form-control" data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}" data-select="allowClear">
									<optgroup class="p-0">
										<option value="">{App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
									</optgroup>
									<optgroup label="{\App\Language::translate('LBL_RECOMENDED_METHODS', $QUALIFIED_MODULE)}">
										{foreach from=$RECOMENDED_METHODS item=METHOD}
											<option value="{$METHOD}" {if $ENCRYPT->get('method') === $METHOD}selected{/if}>{$METHOD}</option>
										{/foreach}
									</optgroup>
									<optgroup label="{\App\Language::translate('LBL_OTHER_METHODS', $QUALIFIED_MODULE)}">
										{foreach from=$AVAILABLE_METHODS item=METHOD}
											<option value="{$METHOD}" {if $ENCRYPT->get('method') === $METHOD}selected{/if}>{$METHOD}</option>
										{/foreach}
									</optgroup>
								</select>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="u-w-37per px-2">
						<label class="muted float-right col-form-label u-text-small-bold">
							{\App\Language::translate('LBL_ENCRYPTION_KEY', $QUALIFIED_MODULE)}
						</label>
					</td>
					<td class="border-left-0">
						<div class="form-row px-3">
							<div class="col-5 px-0">
								<div class="input-group ">
									<input type="password" name="password" id="password" class="form-control" {' '}
										data-validation-engine="validate[required,minSize[8],maxSize[64]]" value="{\App\Purifier::encodeHtml((string) $ENCRYPT->get('pass'))}">
									<span class="input-group-append">
										<button class="btn btn-outline-secondary previewPassword" type="button" data-id="password">
											<span class="fas fa-eye"></span>
										</button>
									</span>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="u-w-37per px-2">
						<label class="muted float-right col-form-label u-text-small-bold">
							{\App\Language::translate('LBL_ENCRYPTION_VECTOR', $QUALIFIED_MODULE)}
						</label>
					</td>
					<td class="border-left-0">
						<div class="form-row px-3">
							<div class="col-5 px-0">
								<div class="input-group ">
									<input type="password" name="vector" id="vector" class="form-control" {' '}
										{if !$ENCRYPT->isEmpty('method') && $MAP_LENGTH_VECTORS_METHODS[$ENCRYPT->get('method')] === 0}disabled="disabled" {/if}{' '}
										data-validation-engine="{if $ENCRYPT->isEmpty('method')}validate[required]{else}validate[required,maxSize[{$MAP_LENGTH_VECTORS_METHODS[$ENCRYPT->get('method')]}],minSize[{$MAP_LENGTH_VECTORS_METHODS[$ENCRYPT->get('method')]}]]{/if}" {' '}
										value="{\App\Purifier::encodeHtml((string) $ENCRYPT->get('vector'))}">
									<span class="input-group-append">
										<button class="btn btn-outline-secondary previewPassword" type="button" data-id="vector">
											<span class="fas fa-eye"></span>
										</button>
									</span>
								</div>
							</div>
							<div class="js-password-alert alert alert-info show mb-0 ml-4 py-1 float-right d-none" role="alert" data-js="container|class:d-none">
								<span class="mdi mdi-alert mr-2"></span>
								{\App\Language::translateArgs('LBL_PASSWORD_LENGTH_IS',$QUALIFIED_MODULE,"<span class='js-password-length' data-js='text'></span>")}
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="c-form__action-panel">
			<button type="submit" class="btn btn-success">
				<span class="fas fa-check mr-2"></span><strong>{App\Language::translate('LBL_SAVE')}</strong>
			</button>
		</div>
	</form>
	<!-- /tpl-Settings-Password-EncryptionSettingsTab -->
{/strip}
