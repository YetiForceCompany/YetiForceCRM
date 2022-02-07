{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Modals-QuickDetailModal modal js-modal-data {if $LOCK_EXIT}static{/if}" tabindex="-1" data-js="data"
		role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}" {/foreach}>
		<div class="modal-dialog {$MODAL_VIEW->modalSize}" role="document">
			<div class="modal-content">
				{foreach item=MODEL from=$MODAL_CSS}
					<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}" />
				{/foreach}
				{foreach item=MODEL from=$MODAL_SCRIPTS}
					<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
				{/foreach}
				<script type="text/javascript">
					app.registerModalController();
				</script>
				<div class="modal-body col-md-12 js-scrollbar" data-js="perfectscrollbar">
					<div class="float-right text-xl-right">
						{foreach item=LINK from=$LINKS}
							{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW=''}
						{/foreach}
						<button class="cancelLink btn btn-sm btn-danger" data-dismiss="modal" type="button" title="{\App\Language::translate('LBL_CLOSE')}">
							<span class="fas fa-times"></span>
						</button>
					</div>
					<div class="quickDetailContent">
						<div class="row">
							<div class="col-md-12">
								<div class="moduleIcon">
									<span class="o-detail__icon js-detail__icon yfm-{$MODULE_NAME}"></span>
								</div>
								<div class="paddingLeft5px">
									<h4 class="recordLabel u-text-ellipsis pushDown marginbottomZero" title='{$RECORD->getName()}'>
										<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
									</h4>
									{if $MODULE_NAME}
										<div class="paddingLeft5px">
											<span class="muted">
												{\App\Language::translate('Assigned To',$MODULE_NAME)}
												: {$RECORD->getDisplayValue('assigned_user_id')}
												{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
												{if $SHOWNERS != ''}
													<br />
													{\App\Language::translate('Share with users',$MODULE_NAME)} {$SHOWNERS}
												{/if}
											</span>
										</div>
									{/if}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								{foreach key=key item=WIDGET from=$WIDGETS}
									<div class="quickDetailWidget">
										{if !empty($WIDGET['title'])}
											{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId($WIDGET['widgetData']['id']|cat:_)}"}
											<div class="c-detail-widget js-detail-widget" data-name="{$WIDGET['title']}" {if isset($WIDGET['widgetData']['data']['relation_id'])} data-relation-id="{$WIDGET['widgetData']['data']['relation_id']}" {/if} data-type="{$WIDGET['widgetData']['type']}" data-id="{$WIDGET['widgetData']['id']}" data-js="container">
												<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['widgetData']['url'])}" data-name="{$WIDGET['title']}" data-type="{$WIDGET['widgetData']['type']}" data-id="{$WIDGET['widgetData']['id']}">
													<div class="c-detail-widget__header js-detail-widget-header collapsed border-bottom-0" data-js="container|value">
														<div class="c-detail-widget__header__container d-flex align-items-center py-1">
															<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse" data-target="#{$WIDGET_UID}-collapse" aria-expanded="true" aria-controls="{$WIDGET_UID}-collapse">
																<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
															</div>
															<div class="c-detail-widget__header__title">
																{if $WIDGET['widgetData']['label']}
																	{assign var="TITLE" value=\App\Language::translate($WIDGET['widgetData']['label'], $MODULE_NAME)}
																{else}
																	{assign var="TITLE" value=$WIDGET['title']}
																{/if}
																<h5 class="mb-0 text-truncate modCT_{\App\Module::getModuleName($WIDGET['widgetData']['tabid'])}">
																	{$TITLE}
																</h5>
															</div>
														</div>
													</div>
													<div class="c-detail-widget__content js-detail-widget-collapse collapse multi-collapse pt-0" id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET['widgetData']['id']}" aria-labelledby="{$WIDGET_UID}">
														<div>{$WIDGET['content']}</div>
													</div>
												</div>
											</div>
										{else}
											<div>{$WIDGET['content']}</div>
										{/if}
									</div>
								{/foreach}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
