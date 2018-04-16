{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="tpl- col-12 col-sm-12 col-md-4 pr-0">
		<div class="u-w-fit float-sm-right">
			{if $CUSTOM_FIELDS_HEADER}
				{foreach from=$CUSTOM_FIELDS_HEADER item=ROW}
					<div class="badge badge-info d-flex my-1"
						 {if $ROW['action']}onclick="{\App\Purifier::encodeHtml($ROW['action'])}"{/if}>
						<div class="w-50 text-right">
							{$ROW['title']}
						</div>
						<div class="w-50 text-left">
								<span class="badge">
									{$ROW['badge']}
								</span>
						</div>
					</div>
				{/foreach}
			{/if}
			{if $FIELDS_HEADER}
				{foreach from=$FIELDS_HEADER key=LABEL item=VALUE}
					{if !empty($VALUE['value'])}
						<div class="badge badge-info d-flex my-1 pr-1">
							<div class="w-50 text-right pr-1">
								{\App\Language::translate($LABEL, $MODULE_NAME)}:
							</div>
							<div class="w-50 text-left text-white u-text-ellipsis">
										{$VALUE['value']}
							</div>
						</div>
					{/if}
				{/foreach}
			{/if}
		</div>
	</div>
{/strip}
