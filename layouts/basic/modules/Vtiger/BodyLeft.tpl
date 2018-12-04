{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<!-- tpl-Base-BodyLeft -->
	<div class="container-fluid c-menu__header">
		<div class="row">
			<div class="col-2 p-0">
				<a class="companyLogoContainer" href="index.php">
					<h1 class="sr-only">{$COMPANY_DETAILS->get('name')}</h1>
					<img class="img-fluid logo" src="{$COMPANY_LOGO->get('imageUrl')}"
						 title="{$COMPANY_DETAILS->get('name')}" alt="{$COMPANY_LOGO->get('alt')}"/>
				</a>
			</div>
			<div class="col-10 userDetails">
				<div class="row">
					<div class="col-10 p-0 userName">
						{assign var=USER_NAME_ARRAY value=explode(' ',$USER_MODEL->getDisplayName())}
						{foreach from=$USER_NAME_ARRAY item=NAME name=userNameIterator}
							{if $smarty.foreach.userNameIterator.iteration <= 2 && !empty({$NAME})}
								<p class="name p-0 m-0 u-text-ellipsis">{$NAME}</p>
							{/if}
						{/foreach}
						<p class="companyName p-0 m-0 u-text-ellipsis" title="{\App\Language::translate('LBL_ROLE')}">
							{\App\Language::translate(\App\User::getCurrentUserModel()->getRoleInstance()->getName())}
						</p>
					</div>
					<div class="col-2 p-0 text-center js-menu--pin {if !$USER_MODEL->get('leftpanelhide')} u-opacity-muted{/if}"
						 data-show="{$USER_MODEL->get('leftpanelhide')}" data-js="click">
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

