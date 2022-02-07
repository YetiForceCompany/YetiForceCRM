{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Modals-RecordsListHeader modal js-modal-data {if $LOCK_EXIT}static{/if}" tabindex="-1" data-js="data"
		role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}" {/foreach}>
		<div class="modal-dialog {$MODAL_VIEW->modalSize}" role="document">
			<div class="modal-content">
				{foreach item=MODEL from=$MODAL_CSS}
					<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}" />
				{/foreach}
				{foreach item=MODEL from=$MODAL_SCRIPTS}
					<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
				{/foreach}
				<div class="modal-header d-flex justify-content-between flex-wrap flex-lg-nowrap">
					<h5 class="modal-title mr-2 my-auto text-nowrap">
						{if $MODAL_VIEW->modalIcon}
							<span class="{$MODAL_VIEW->modalIcon} mr-2"></span>
						{/if}
						{App\Language::translate($MODULE_NAME, $MODULE_NAME)}
					</h5>
					<div class="d-flex justify-content-center justify-content-sm-between w-100 mt-2 mt-lg-0 mx-lg-2 order-3 order-lg-0 flex-wrap">
						<div class="mb-2 mb-sm-0 btn-toolbar u-w-sm-down-100">
							{if !empty($SWITCH)}
								<div class="mr-2 mb-2 mb-sm-0 {if isset($MODAL_PARAMS['hideSwitch']) && $MODAL_PARAMS['hideSwitch'] eq 'true'} d-none {/if}">
									<div class="btn-group btn-group-toggle" data-toggle="buttons">
										<label class="btn btn-outline-primary active">
											<input class="js-hierarchy-records" data-js="value|change" type="radio" name="hierarchyRecords" value="{$RELATED_PARENT_ID}" checked="">
											{$SWITCH_ON_TEXT}
										</label>
										<label class="btn btn-outline-primary">
											<input class="js-hierarchy-records" data-js="value|change" type="radio" name="hierarchyRecords" value="0">
											{App\Language::translate('LBL_ALL',$MODULE_NAME)}
										</label>
									</div>
									<a href="#" class="js-popover-tooltip" data-js="popover" title="" data-placement="auto"
										data-content="{App\Language::translate('LBL_POPUP_NARROW_DOWN_RECORDS_LIST',$MODULE_NAME)}"
										data-original-title="{App\Language::translate('LBL_POPUP_SWITCH_BUTTON',$MODULE_NAME)}">
										<span class="fas fa-info-circle ml-1"></span>
									</a>
								</div>
							{/if}
							{if $MULTI_SELECT && !empty($LISTVIEW_ENTRIES)}
								<button class="js-selected-rows btn btn-outline-secondary c-btn-block-sm-down" data-js="click">
									<strong>
										<span class="fas fa-check mr-2"></span>{App\Language::translate('LBL_SELECT', $MODULE_NAME)}
									</strong>
								</button>
							{/if}
						</div>
						<div class="customFilterMainSpan mb-2 mb-sm-0">
							{if !empty($CUSTOM_VIEWS)}
								<select id="customFilter" class="col-md-12">
									<option value="0" {if empty($CV_ID)} selected="selected" {/if}>
										&nbsp;{\App\Language::translate('LBL_SELECT')}
									</option>
									{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
										<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
											{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
												<option id="filterOptionId_{$CUSTOM_VIEW->getId()}"
													value="{$CUSTOM_VIEW->getId()}"
													class="filterOptionId_{$CUSTOM_VIEW->getId()}"
													data-id="{$CUSTOM_VIEW->getId()}"
													{if $CV_ID eq $CUSTOM_VIEW->getId()} selected="selected" {/if}>
													&nbsp;{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $CUSTOM_VIEW->getModule()->getName())}{if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ] {/if}
												</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
								<span class="filterImage">
									<span class="fas fa-filter"></span>
								</span>
							{else}
								<input type="hidden" value="0" id="customFilter" />
							{/if}
						</div>
						<div class="js-pagination-container"
							data-js="container">
							{include file=App\Layout::getTemplatePath('Pagination.tpl', $MODULE_NAME) VIEWNAME='recordsList'}
						</div>
					</div>
					<button type="button" class="close" data-dismiss="modal"
						aria-label="{App\Language::translate('LBL_CANCEL')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
{/strip}
