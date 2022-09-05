{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WidgetsManagement-ManageWidgets -->
	<div class="modal-body">
		<form>
			<div class="form-group row">
				<input type="hidden" name="dashboardBlockId" value="{$DASHBOARD_BLOCK_ID}">
				<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}">
				<div class="col-sm-12">
					<div class="row">
						<div class="col-sm-6">
							<div class="row">
								<div class="col-sm-4 col-form-label ">
									<span>{\App\Language::translate('LBL_SELECT_ACTION', $QUALIFIED_MODULE)}</span>
									<span class="redColor">*</span>
								</div>
								<div class="col-sm-8 mt-1">
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="actionOption" id="copy" value="copy" checked>
										<label class="form-check-label" for="copy">{App\Language::translate('LBL_COPY', $QUALIFIED_MODULE)}</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="actionOption" id="move" value="move">
										<label class="form-check-label" for="move">{App\Language::translate('LBL_MOVE', $QUALIFIED_MODULE)}</label>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="row">
								<div class="col-sm-4 col-form-label text-right">
									<span>{\App\Language::translate('LBL_CHOISE_AUTHORIZED', $QUALIFIED_MODULE)}</span>
									<span class="redColor">*</span>
								</div>
								<div class="col-sm-8 controls">
									<select class="authorized form-control mb-0 js-authorized select2" name="authorized">
										<optgroup label="{\App\Language::translate('LBL_ROLES', $QUALIFIED_MODULE)}">
											{foreach from=$ALL_AUTHORIZATION item=AUTHORIZED key=AUTHORIZED_CODE}
												{if $AUTHORIZED != $AUTHORIZED_CODE}
													<option value="{$AUTHORIZED_CODE}"
														data-label="{$AUTHORIZED->get('rolename')}">{\App\Language::translate($AUTHORIZED->get('rolename'),$QUALIFIED_MODULE)}</option>
												{/if}
											{/foreach}
										</optgroup>
										{if count($ALL_SERVERS)}
											<optgroup label="{\App\Language::translate('WebserviceApps', 'Settings:WebserviceApps')}">
												{foreach from=$ALL_SERVERS item=SERVER key=ID}
													{if $AUTHORIZED !== $ID}
														<option value="{$ID}">{\App\Purifier::encodeHTML($SERVER['name'])}</option>
													{/if}
												{/foreach}
											</optgroup>
										{/if}
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-12 mt-3">
					<div class="row">
						{if empty($WIDGETS[$DASHBOARD_BLOCK_ID])}
							{assign var=WIDGETS_AUTHORIZATION value=[]}
						{else}
							{assign var=WIDGETS_AUTHORIZATION value=$WIDGETS[$DASHBOARD_BLOCK_ID]}
						{/if}
						{foreach item=WIDGET_MODEL from=$WIDGETS_AUTHORIZATION name=fieldlist}
							<div class="col-sm-6 mt-1">
								<input name="widgetLinkId" type="checkbox" value="{$WIDGET_MODEL->get('id')}">
								<span class="fieldLabel ml-3">{$WIDGET_MODEL->getTranslatedTitle()}</span>
							</div>
						{/foreach}
					</div>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-WidgetsManagement-ManageWidgets -->
{/strip}
