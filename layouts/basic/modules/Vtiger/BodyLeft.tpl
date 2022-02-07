{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<!-- tpl-Base-BodyLeft -->
	<div class="container-fluid c-menu__header">
		<div class="row">
			<div class="col-2 p-0">
				<a class="companyLogoContainer" href="index.php">
					<h1 class="sr-only">{$CURRENT_USER->get('roleName')}</h1>
					{if $CURRENT_USER->get('multiCompanyLogoUrl')}
						<img class="img-fluid logo" src="{$CURRENT_USER->get('multiCompanyLogoUrl')}"
							title="{$CURRENT_USER->get('roleName')}"
							alt="{$CURRENT_USER->get('roleName')}" />
					{else}
						<img class="img-fluid logo" src="{App\Layout::getPublicUrl('layouts/resources/Logo/logo')}" title="Logo" alt="Logo" />
					{/if}
				</a>
			</div>
			<div class="col-10 userDetails" data-user="{$CURRENT_USER->getId()}">
				<div class="row">
					<div class="col-10 p-0 userName">
						{assign var=USER_NAME_ARRAY value=explode(' ',$USER_MODEL->getDisplayName())}
						{foreach from=$USER_NAME_ARRAY item=NAME name=userNameIterator}
							{if $smarty.foreach.userNameIterator.iteration <= 2 && !empty({$NAME})}
								<p class="name p-0 m-0 u-text-ellipsis">{$NAME}</p>
							{/if}
						{/foreach}
						<p class="companyName p-0 m-0 u-text-ellipsis"
							title="{\App\Language::translate('LBL_ROLE', $QUALIFIED_MODULE)}">
							{\App\Language::translate($CURRENT_USER->get('roleName'))}
						</p>
					</div>
					<div class="col-2 p-0 text-center js-menu--pin {if !$USER_MODEL->get('leftpanelhide')} u-opacity-muted{/if}" data-show="{$USER_MODEL->get('leftpanelhide')}" data-js="click">
						<span class="fas fa-thumbtack u-cursor-pointer"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="js-menu--scroll c-menu__body" data-js="perfectscrollbar">
		{include file=\App\Layout::getTemplatePath('Menu.tpl', $MODULE)}
	</div>
	<!-- /tpl-Base-BodyLeft -->
{/strip}
