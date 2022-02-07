{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-Tabs-WebserviceApps -->
	{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceWebservicePremium')}
	{if $CHECK_ALERT}
		<div class="alert alert-warning mt-2 mb-1">
			<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
			{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')}
			<a class="btn btn-primary btn-sm ml-2" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceWebservicePremium&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
		</div>
	{/if}
	<div class="moduleBlocks">
		{assign var=WEBSERVICE_DATA value=$SELECTED_MODULE_MODEL->getFieldsForWebserviceApps($SERVER_ID)}
		{assign var=FIEL_TYPE_LABEL value=Settings_LayoutEditor_Field_Model::$fieldTypeLabel}
		{foreach key=BLOCK_LABEL_KEY item=BLOCK_MODEL from=$BLOCKS}
			{assign var=FIELDS_LIST value=$BLOCK_MODEL->getLayoutBlockActiveFields()}
			{assign var=BLOCK_ID value=$BLOCK_MODEL->get('id')}
			{$ALL_BLOCK_LABELS[$BLOCK_ID] = $BLOCK_LABEL_KEY}
			<div id="block_{$BLOCK_ID}" class="editFieldsTable block_{$BLOCK_ID} mb-2 border1px" style="border-radius: 4px;background: white;" data-js="container">
				<div class="layoutBlockHeader d-flex flex-wrap justify-content-between m-0 p-1 pt-1 w-100">
					<div class="blockLabel ml-3 u-white-space-nowrap">
						<strong class="align-middle js-block-label" title="{$BLOCK_LABEL_KEY}" data-js="container">
							{if !empty($BLOCK_MODEL->get('icon'))}<span class="{$BLOCK_MODEL->get('icon')} mr-2"></span>{/if}
							{App\Language::translate($BLOCK_LABEL_KEY, $SELECTED_MODULE_NAME)}
						</strong>
					</div>
				</div>
				<div class="blockFieldsList row m-0 p-1 u-min-height-50">
					{for $LOOP=0 to 1}
						<ul class="col-md-6 mb-1 px-1 list-unstyled" data-js="container">
							{foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist}
								{if $smarty.foreach.fieldlist.index % 2 eq $LOOP}
									{if isset($WEBSERVICE_FIELDS[$FIELD_MODEL->get('id')])}
										{assign var=WEBSERVICE_FIELD value=$WEBSERVICE_FIELDS[$FIELD_MODEL->get('id')]}
									{else}
										{assign var=WEBSERVICE_FIELD value=[]}
									{/if}
									<li {if isset($WEBSERVICE_DATA[$FIELD_MODEL->get('id')])}class="u-bg-gray" {/if}>
										<div class="opacity ml-0 border1px" data-field-id="{$FIELD_MODEL->get('id')}">
											<div class="px-2 py-1">
												<div class="col-12 pr-0 js-field-container fieldContainer"
													style="word-wrap: break-word;">
													<span class="fieldLabel">
														{assign var=ICON value=$FIELD_MODEL->getIcon()}
														{if isset($ICON['name'])}<span class="{$ICON['name']} mr-2"></span>{/if}
														{App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}
														{if $FIELD_MODEL->isMandatory()}
															<span class="redColor">*</span>
														{/if}
														<span class="ml-3 badge badge-secondary">{$FIELD_MODEL->getName()}</span>
														{if isset($FIEL_TYPE_LABEL[$FIELD_MODEL->getUIType()])}
															<span class="ml-3 badge badge-info">{App\Language::translate($FIEL_TYPE_LABEL[$FIELD_MODEL->getUIType()], $QUALIFIED_MODULE)}</span>
														{/if}
													</span>
													<span class="float-right actions">
														<button class="btn btn-success btn-xs js-edit-field-api ml-2" data-wa="{$SERVER_ID}" data-field-id="{$FIELD_MODEL->get('id')}" title="{App\Language::translate('BTN_WEBSERVICE_APP_EDIT', $QUALIFIED_MODULE)}">
															<span class="yfi yfi-full-editing-view"></span>
														</button>
													</span>
												</div>
											</div>
										</div>
									</li>
								{/if}
							{/foreach}
						</ul>
					{/for}
				</div>
			</div>
		{/foreach}
	</div>
	<!-- /tpl-Settings-LayoutEditor-Tabs-WebserviceApps -->
{/strip}
