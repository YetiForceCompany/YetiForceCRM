{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="breadCrumbs" >
		<input type='hidden' value="{$MODULE}" id='module' name='module'/>
		<input type="hidden" value="{$PARENT_MODULE}" id="parent" name='parent' />
		<input type='hidden' value="{$VIEW}" id='view' name='view'/>
		{assign var="BREADCRUMBS" value=Vtiger_Menu_Model::getBreadcrumbs()}
		{assign var=HOMEICON value='userIcon-my-home-page'}
		{if $BREADCRUMBS}
			<div class="breadcrumbsContainer">
				<h2 class="breadcrumbsLinks">
					<a href='/'>
						<span class="{$HOMEICON}"></span>
					</a>
					&nbsp;|&nbsp;
					{foreach key=key item=item from=$BREADCRUMBS name=breadcrumbs}
						{if $key != 0 && $ITEM_PREV}
							<span class="separator">&nbsp;{vglobal('breadcrumbs_separator')}&nbsp;</span>
						{/if}
						<span>{$item['name']}</span>
						{assign var="ITEM_PREV" value=$item['name']}
					{/foreach}
				</h2>
			</div>
		{/if}
		{assign var="MENUSCOLOR" value=Users_Colors_Model::getModulesColors(true)}
		{if $MENUSCOLOR}
			<div class="menusColorContainer">
				<style>
					{foreach item=item from=$MENUSCOLOR}
						.moduleColor_{$item.module}{
							color: {$item.color} !important;
						}
						.moduleIcon{$item.module}{
							background: {$item.color} !important;
						}
					{/foreach}
				</style>
			</div>
		{/if}
	</div>
{/strip}
