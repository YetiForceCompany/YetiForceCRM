{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="tpl-Detail-HeaderFields col-12 col-sm-12 col-md-4 pr-0">
		<div class="u-w-max-320px float-sm-right">
			{if $CUSTOM_FIELDS_HEADER}
				{foreach from=$CUSTOM_FIELDS_HEADER item=ROW}
					<div class="badge badge-info d-flex my-1"
						 {if $ROW['action']}onclick="{\App\Purifier::encodeHtml($ROW['action'])}"{/if}>
						<div class="pr-2 u-text-ellipsis">
							{$ROW['title']}
						</div>
						<div class="u-text-ellipsis">
							{$ROW['badge']}
						</div>
					</div>
				{/foreach}
			{/if}
			{if $FIELDS_HEADER}
				{foreach from=$FIELDS_HEADER key=LABEL item=VALUE}
					{if !empty($VALUE['value'])}
						<div class="badge badge-info d-flex mt-1">
							<div class="pr-2 u-text-ellipsis">
								{\App\Language::translate($LABEL, $MODULE_NAME)}:
							</div>
							<div class="u-text-ellipsis">
								{$VALUE['value']}
							</div>
						</div>
					{/if}
				{/foreach}
			{/if}
		</div>
	</div>
{/strip}
