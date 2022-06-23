{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="verticalScroll">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<h5 class="mt-2">{\App\Language::translate('Roundcube config', $MODULE)}</h5>
		{if Settings_ModuleManager_Library_Model::checkLibrary('roundcube')}
			<div class="alert alert-danger" role="alert">
				<div>
					<h4>{\App\Language::translateArgs('ERR_NO_REQUIRED_LIBRARY_FEATURES_DOWNLOAD', 'Settings:Vtiger','roundcube','<a href="index.php?module=ModuleManager&parent=Settings&view=List">'|cat:\App\Language::translate("VTLIB_LBL_MODULE_MANAGER",'Settings:Base')|cat:'</a>')}</h4>
				</div>
			</div>
		{elseif !\App\Module::isModuleActive('OSSMail')}
			<div class="alert alert-danger" role="alert">
				<div>
					<h4>{\App\Language::translate('ERR_NO_MODULE_IS_INACTIVE', $QUALIFIED_MODULE)}</h4>
				</div>
			</div>
		{else}
			<form class="roundcubeConfig">
				<div class="col-md-12 marginTop20">
					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="parent" value="Settings">
					<input type="hidden" name="action" value="Save">
					{foreach key=FIELD_NAME item=FIELD_DETAILS from=$RECORD_MODEL->getForm()}
						<div class="row marginBottom10px">
							<div class="row col-md-4">
								<label class="muted ">{if $FIELD_DETAILS['required'] === 1}
										<span class="redColor">*</span>
									{/if}{\App\Language::translate($FIELD_DETAILS['label'], $MODULE)}</label></td>
							</div>
							<div class="col-md-8">
								{if $FIELD_DETAILS['fieldType'] === 'picklist'}
									<div class=" row col-sm-12">
										<select class="select2 form-control" name="{$FIELD_NAME}">
											{foreach item=ELEMENT from=$FIELD_DETAILS['value']}
												<option value="{$ELEMENT}" {if $ELEMENT == $RECORD_MODEL->get($FIELD_NAME)} selected {/if}>
													{if $FIELD_NAME !== 'language'}
														{\App\Language::translate($FIELD_NAME|cat:'_'|cat:$ELEMENT, $MODULE)}
													{else}
														{$ELEMENT}
													{/if}
												</option>
											{/foreach}
										</select>
									</div>
								{else if $FIELD_DETAILS['fieldType'] === 'multipicklist'}
									<div class="row col-md-12">
										<select class="form-control" name="{$FIELD_NAME}" multiple="multiple"
											{if $FIELD_DETAILS['required'] === 1}data-validation-engine="validate[required]" {/if}>
											{foreach item=ITEM key=KEY from=$RECORD_MODEL->get($FIELD_NAME)}
												<option value="{\App\Purifier::encodeHtml($KEY)}" selected>{\App\Purifier::encodeHtml(\App\Purifier::encodeHtml($KEY))}</option>
											{/foreach}
										</select>
									</div>
								{else if $FIELD_DETAILS['fieldType'] === 'checkbox'}
									<div class=" row col-sm-12">
										<input type="hidden" name="{$FIELD_NAME}" value="false" />
										<input type="checkbox" name="{$FIELD_NAME}"
											value="true" {if $RECORD_MODEL->get($FIELD_NAME) == 'true'} checked {/if} />
									</div>
								{else}
									<div class="row col-sm-12">
										<input class="form-control" type="text" name="{$FIELD_NAME}"
											{if $FIELD_DETAILS['required'] === 1}data-validation-engine="validate[required]" {/if}
											value="{\App\Purifier::encodeHtml($RECORD_MODEL->get($FIELD_NAME))}" />
									</div>
								{/if}
							</div>
						</div>
					{/foreach}

				</div>
				<div class="c-form__action-panel">
					<button class="btn btn-success saveButton" type="submit" title="">
						<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong></button>
				</div>
		</div>
		</form>
	{/if}
{/strip}
