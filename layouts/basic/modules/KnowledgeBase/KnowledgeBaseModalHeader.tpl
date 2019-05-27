{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-KnowledgeBase-ModalHeader -->
<div class="modal js-modal-data quasar-modal " tabindex="-1" data-js="data"
	 role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}"{/foreach}>
	<div class="quasar-reset h-100 w-100" role="document">
		<div class="modal-content">
		<div class="q-bar row no-wrap items-center q-bar--standard q-bar--light">
		<span class="userIcon-KnowledgeBase mr-2"></span>
		{\App\Language::translateSingularModuleName($MODULE_NAME)}
			<div class="q-space"></div>
			<button tabindex="0" type="button" data-dismiss="modal" class="q-btn inline q-btn-item non-selectable q-btn--flat q-btn--rectangle q-focusable q-hoverable q-btn--dense">
				<div class="q-btn__content text-center col items-center q-anchor--skip justify-center row">
					<i aria-hidden="true" class="mdi mdi-close q-icon"></i>
				</div>
			</button>
		</div>
			{foreach item=MODEL from=$MODAL_CSS}
				<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}"/>
			{/foreach}
			{foreach item=MODEL from=$MODAL_SCRIPTS}
				<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
			{/foreach}
			<script type="text/javascript">app.registerModalController();</script>
	<!-- /tpl-KnowledgeBase-ModalHeader -->
			{/strip}
