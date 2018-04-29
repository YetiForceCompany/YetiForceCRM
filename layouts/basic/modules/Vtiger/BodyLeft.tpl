{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="container-fluid userDetailsContainer">
		<div class="row padding0">
			<div class="col-md-2 noSpaces">
				<a class="companyLogoContainer" href="index.php">
					<img class="img-fluid logo" src="{$COMPANY_LOGO->get('imageUrl')}" title="{$COMPANY_DETAILS->get('name')}" alt="{$COMPANY_LOGO->get('alt')}" />
					<span class="sr-only">{$COMPANY_DETAILS->get('name')}</span>
				</a>
			</div>
			<div class="col-md-10 userDetails">
				<div class="col-12 noSpaces userName">
					{assign var=USER_NAME_ARRAY value=explode(' ',$USER_MODEL->getDisplayName())}
					{foreach from=$USER_NAME_ARRAY item=NAME name=userNameIterator}
						{if $smarty.foreach.userNameIterator.iteration <= 2 && !empty({$NAME})}
							<p class="noSpaces name u-text-ellipsis">{$NAME}</p>
						{/if}
					{/foreach}
					<p class="companyName noSpaces u-text-ellipsis">{$COMPANY_DETAILS->get('name')}</p>
				</div>
			</div>
		</div>
	</div>
	<div class="menuContainer">
		{include file=\App\Layout::getTemplatePath('Menu.tpl', $MODULE)}
	</div>
{/strip}

