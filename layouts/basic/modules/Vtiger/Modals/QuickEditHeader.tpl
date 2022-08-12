{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-QuickEditHeader -->
	<div class=" modal js-modal-data {if $LOCK_EXIT}static{/if}" tabindex="-1" data-js="data"
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
				<form class="form-horizontal recordEditView" name="QuickEdit" method="post" action="index.php" data-module-name="{$MODULE_NAME}">
					<div class="modal-header align-items-center form-row d-flex justify-content-between py-2{if isset($MODAL_VIEW->headerClass)} {$MODAL_VIEW->headerClass}{/if}">
						<div class="col-xl-6 col-12">
							<h5 class="modal-title form-row text-center text-xl-left mb-2 mb-xl-0">
								<span class="col-12">
									{if $MODAL_TITLE}
										<strong class="mr-1">{$MODAL_TITLE}</strong>
									{else}
										<span class="yfi yfi-quick-creation mx-1"></span>
										<strong class="mr-1">{\App\Language::translate('LBL_QUICK_EDIT', $MODULE_NAME)}:</strong>
									{/if}
									<span class="yfm-{$MODULE_NAME} mx-1"></span>
									<strong>
										{\App\Language::translate("SINGLE_{$MODULE_NAME}", $MODULE_NAME)}
									</strong>
								</span>
							</h5>
						</div>
						<div class="col-xl-6 col-12 text-center text-xl-right">
							{if \App\Privilege::isPermitted($MODULE_NAME, 'RecordCollector') && !empty($QUICKCREATE_LINKS['EDIT_VIEW_RECORD_COLLECTOR'])}
								{include file=\App\Layout::getTemplatePath('Edit/RecordCollectors.tpl', $MODULE) SHOW_BTN_LABEL=1 RECORD_COLLECTOR=$QUICKCREATE_LINKS['EDIT_VIEW_RECORD_COLLECTOR']}
							{/if}
							{if !empty($QUICKCREATE_LINKS['QUICKEDIT_VIEW_HEADER'])}
								{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKEDIT_VIEW_HEADER']}
									{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='quickcreateViewHeader' CLASS='display-block-md' TABINDEX=Vtiger_Field_Model::$tabIndexLastSeq}
								{/foreach}
							{/if}
							<button class="btn btn-success mr-1" type="submit" tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}" title="{\App\Language::translate('LBL_SAVE', $MODULE)}">
								<strong><span class="fas fa-check"></span></strong>
							</button>
							<button class="cancelLink btn btn-danger" tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}" data-dismiss="modal" type="button" title="{\App\Language::translate('LBL_CLOSE')}">
								<span class="fas fa-times"></span>
							</button>
						</div>
					</div>
					<!-- /tpl-Base-Modals-QuickEditHeader -->
{/strip}
