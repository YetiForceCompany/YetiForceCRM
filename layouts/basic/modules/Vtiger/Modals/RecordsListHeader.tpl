{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="tpl-Modals-RecordsListHeader modal js-modal-data {if $LOCK_EXIT}static{/if}" tabindex="-1" data-js="data"
	 role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}"{/foreach}>
<div class="modal-dialog {$MODAL_VIEW->modalSize}" role="document">
	<div class="modal-content">
	{foreach item=MODEL from=$MODAL_CSS}
		<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}"/>
	{/foreach}
	{foreach item=MODEL from=$MODAL_SCRIPTS}
		<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
	{/foreach}
	<script type="text/javascript">app.registerModalController();</script>
	<div class="modal-header">
		<div class="form-row col-12 p-0">
			<div class="col-12">
				<div class="modal-title">
					{if $MODAL_VIEW->modalIcon}
						<span class="{$MODAL_VIEW->modalIcon} mr-2"></span>
					{/if}
					{App\Language::translate($MODULE_NAME, $MODULE_NAME)}
					<button type="button" class="close" data-dismiss="modal" aria-label="{App\Language::translate('LBL_CANCEL')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			</div>
			<div class="col-12 ml-2 ml-sm-0 mt-2 form-row d-flex">
				{if $SWITCH}
					<div class="col-12 col-md-6 px-0 mb-2 mb-md-0 d-flex justify-content-center justify-content-md-start">
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
						&nbsp;<a href="#" class="js-popover-tooltip" data-js="popover" title="" data-placement="auto" data-content="{App\Language::translate('LBL_POPUP_NARROW_DOWN_RECORDS_LIST',$MODULE_NAME)}" data-original-title="{App\Language::translate('LBL_POPUP_SWITCH_BUTTON',$MODULE_NAME)}">
							<span class="fas fa-info-circle"></span>
						</a>
					</div>
				{/if}
				{if $MULTI_SELECT && !empty($LISTVIEW_ENTRIES)}
					<div class="col-12 col-md-6 px-0 mb-2 mb-md-0 d-flex justify-content-center justify-content-md-start">
						<button class="js-selected-rows btn btn-outline-secondary" data-js="click">
							<strong>
								<span class="fas fa-check mr-2"></span>{App\Language::translate('LBL_SELECT', $MODULE_NAME)}
							</strong>
						</button>
					</div>
				{/if}
				<div class="{if $MULTI_SELECT or $SWITCH} col-12 col-md-6 px-0 {else} m-auto {/if}">
					<div class="js-pagination-container d-flex justify-content-center justify-content-md-end" data-js="container">
						{include file=App\Layout::getTemplatePath('Pagination.tpl', $MODULE_NAME) VIEWNAME='recordsList'}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}