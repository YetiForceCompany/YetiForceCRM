{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-OpenStreetMap-MapModalHeader -->
	<div class="modal js-modal-data {if $LOCK_EXIT}static" data-keyboard="false{/if}" tabindex="-1" data-js="data" role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}" {/foreach}>
		<div class="modal-dialog {$MODAL_VIEW->modalSize}" role="document">
			<div class="modal-content">
				{foreach item=MODEL from=$MODAL_CSS}
					<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}" />
				{/foreach}
				{foreach item=MODEL from=$MODAL_SCRIPTS}
					<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
				{/foreach}
				{if $REGISTER_EVENTS}
					<script type="text/javascript">
						app.registerModalController('{$MODAL_ID}');
					</script>
				{/if}
				<div class="modal-header{if isset($MODAL_VIEW->headerClass)} {$MODAL_VIEW->headerClass}{/if}">
					<h4 class="modal-title">
						{if $MODAL_VIEW->modalIcon}
							<span class="modal-header-icon {$MODAL_VIEW->modalIcon} mr-2"></span>
						{/if}
						{$MODAL_TITLE}
					</h4>
					<div class="col-md-10 mx-auto">
						<div class="input-group">
							<div class="input-group-prepend{if count($ADDRESS_PROVIDERS) eq 1} d-none{/if}">
								<select class="select2 js-select-operator" data-dropdown-auto-width="true" data-js="value">
									{foreach item=ROW from=$ADDRESS_PROVIDERS}
										<option value="{$ROW}" {if \App\Map\Address::getDefaultProvider() eq $ROW}selected{/if}>
											{\App\Language::translate('LBL_PROVIDER_'|cat:$ROW|upper, 'Settings:ApiAddress')}
										</option>
									{/foreach}
								</select>
							</div>
							<input type="text" class="js-search-address form-control" placeholder="{\App\Language::translate('LBL_SEARCH_ADDRESS_DESCRIPTION', $MODULE_NAME)}" />
							<input type="text" class="form-control u-max-w-150px js-radius" data-js="val" size="6" placeholder="{\App\Language::translate('LBL_IN_RADIUS', $MODULE_NAME)}" />
							<div class="input-group-append">
								<button class="btn btn-success input-group-btn js-search-btn" data-js="click">
									<span class="fas fa-search fa-fw mr-2"></span>{\App\Language::translate('LBL_SEARCH', $MODULE_NAME)}</span>
								</button>
							</div>
						</div>
					</div>
					<div class="col-md-1 mx-auto">
						<button class="btn btn-warning js-popover-tooltip js-my-location-btn" data-label="{\App\Purifier::encodeHtml(App\Language::translate('LBL_MY_LOCATION', $MODULE_NAME))}" data-content="{\App\Purifier::encodeHtml(App\Language::translate('LBL_SEARCH_MY_LOCATION', $MODULE_NAME))}">
							<span class="fas fa-location-crosshairs"></span>
						</button>
					</div>
					{if !$LOCK_EXIT}
						<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CANCEL')}">
							<span aria-hidden="true">&times;</span>
						</button>
					{/if}
				</div>
				<!-- /tpl-OpenStreetMap-MapModalHeader -->
{/strip}
