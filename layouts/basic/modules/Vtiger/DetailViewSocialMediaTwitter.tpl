{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Twitter-Index">
		TWITTER:<br>
		{foreach from=$TWITTER_ACCOUNT item=TWITTER}
			{$TWITTER}
			<br>
		{/foreach}
		<div></div>

		{foreach from=SocialMedia_Module_Model::getAllRecords($TWITTER_ACCOUNT) item=RECORD_TWITTER}
			{$RECORD_TWITTER->get('message')}
			<br>
		{/foreach}
	</div>
{/strip}
