{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WooCommerce-EditConfigModal -->
	<div class="modal-body pb-0">
		{if !empty($INFO['info'])}
			<textarea rows="12" disabled>{$INFO['info']}</textarea>
			<ul class="list-group">
				{foreach from=$INFO['count'] key=KEY item=VALUE}
					<li class="list-group-item p-1">{$KEY} <strong class="badge badge-primary">{$VALUE}</strong></li>
				{/foreach}
			</ul>
		{/if}
		{if !empty($EXCEPTION)}
			<div class="alert alert-danger">
				<span class="fas fa-exclamation-triangle color-red-600 u-fs-5x mr-4 float-left"></span>
				{$EXCEPTION->getMessage()}
			</div>
		{/if}
	</div>
	<!-- /tpl-Settings-WooCommerce-EditConfigModal -->
{/strip}
