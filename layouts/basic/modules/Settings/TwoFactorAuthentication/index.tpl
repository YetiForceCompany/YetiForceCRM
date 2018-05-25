{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-TwoFactorAuthentication-Index">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div>
			<form class="formTwoFactorAuthentication">
				<input type="hidden" name="parent" value="Settings">
				<input type="hidden" name="module" value="{$MODULE}">
				<input type="hidden" name="action" value="Save">
				<div class="form-group row">
					<label for="inputPassword"
						   class="col-12 col-lg-2 col-form-label">{\App\Language::translate('LBL_TOTP_AUTHY_MODE', $QUALIFIED_MODULE)}</label>
					<div class="col-12 col-lg-4">
						<select class="chzn-select" name="methods">
							<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
							{foreach from=$AVAILABLE_METHODS item=METHOD}
								{assign var=LBL_METHOD value="LBL_$METHOD"}
								<option value="{$METHOD}">{\App\Language::translate($LBL_METHOD, $QUALIFIED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label for="inputPassword"
						   class="col-12 col-lg-2 col-form-label">{\App\Language::translate('LBL_TOTP_AUTHY_MODE', $QUALIFIED_MODULE)}</label>
					<div class="col-12 col-lg-4">
						<select multiple="" name="users" class="select2 configField form-control" data-type="ldap"
								style="width: 100%;">
							{foreach key=KEY item=USER from=\App\Fields\Owner::getAllUsers()}
								<option value="{$USER['id']}" {if in_array($USER['id'], $USER_EXCEPTIONS)} selected {/if}>{$USER['fullName']}</option>
							{/foreach}
						</select>
					</div>
				</div>

				<div class="formActionsPanel">
					<button type="submit" class="btn btn-xs btn-success"><span
								class="fas fa-check"></span>&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_SAVE')}</strong>
					</button>
				</div>
			</form>
		</div>
	</div>
{/strip}
