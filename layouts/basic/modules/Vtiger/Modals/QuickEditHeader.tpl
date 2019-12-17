{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Modals-QuickEditHeader -->
<div class=" modal js-modal-data {if $LOCK_EXIT}static{/if}" tabindex="-1" data-js="data"
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
			<form class="form-horizontal recordEditView" name="QuickEdit" method="post" action="index.php">
				<div class="modal-header{if isset($MODAL_VIEW->headerClass)} {$MODAL_VIEW->headerClass}{/if}">
					<div class="col-xl-9 col-12">
						<h5 class="modal-title form-row text-center text-xl-left mb-2 mb-xl-0">
							<span class="col-12">
								<span class="userIcon-{$MODULE_NAME} mx-1"></span>
								<strong class="mr-1">{\App\Language::translate('LBL_QUICK_EDIT', $MODULE_NAME)}:</strong>
								<strong class="text-uppercase">
									{\App\Language::translate("SINGLE_{$MODULE_NAME}", $MODULE_NAME)}
								</strong>
							</span>
						</h5>
					</div>
					<div class="col-xl-3 col-12 text-center text-xl-right">
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
