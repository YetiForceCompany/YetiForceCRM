{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-QuickDetail-Modal modal js-modal-data {if $LOCK_EXIT}static{/if}" tabindex="-1" data-js="data"
		 role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}"{/foreach}>
		<div class="modal-dialog {$MODAL_VIEW->modalSize}" role="document" style="width: 350px;">
			<div class="modal-content">
				{foreach item=MODEL from=$MODAL_CSS}
					<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}"/>
				{/foreach}
				{foreach item=MODEL from=$MODAL_SCRIPTS}
					<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
				{/foreach}
				<script type="text/javascript">app.registerModalController();</script>
				<div class="modal-body col-md-12 js-scrollbar" data-js="perfectscrollbar">
					{if $SHOW_CLOSE_BTN}
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					{/if}
					<div class="js-components" data-js="container">
						{include file=\App\Layout::getTemplatePath('QuickDetail/TabContent.tpl', $MODULE_NAME)}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
