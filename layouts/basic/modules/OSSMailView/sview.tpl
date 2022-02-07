{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="SendEmailFormStep2" id="emailPreview" name="emailPreview">
		<div>
			<form class="form-horizontal emailPreview">
				<div class="row pb-1">
					<span class="col-md-12 row">
						<span class="col-1 text-right">
							<span class="text-muted">{\App\Language::translate('From',$MODULENAME)}</span>
						</span>
						<span class="col-11">
							<span id="emailPreview_From" class="">{$FROM}</span>
							<span style="display: none;" id="_mailopen_date" class="row">{$SENT}</span>
						</span>
					</span>
				</div>
				<div class="row pb-1">
					<span class="col-md-12 row">
						<span class="col-1 text-right">
							<span class="text-muted">{\App\Language::translate('To',$MODULENAME)}</span>
						</span>
						<span class="col-11">
							<span id="emailPreview_To" class="">{assign var=TO_EMAILS value=","|implode:$TO}{$TO_EMAILS}</span>
						</span>
					</span>
				</div>
				{if !empty($CC)}
					<div class="row pb-1">
						<span class="col-md-12 row">
							<span class="col-1 text-right">
								<span class="text-muted">{\App\Language::translate('CC',$MODULENAME)}</span>
							</span>
							<span class="col-11">
								<span id="emailPreview_Cc">
									{$CC}
								</span>
							</span>
						</span>
					</div>
				{/if}
				{if !empty($BCC)}
					<div class="row pb-1">
						<span class="col-md-12 row">
							<span class="col-1 text-right">
								<span class="text-muted">{\App\Language::translate('BCC',$MODULENAME)}</span>
							</span>
							<span class="col-11">
								<span id="emailPreview_Bcc">
									{$BCC}
								</span>
							</span>
						</span>
					</div>
				{/if}
				<div class="row pb-1">
					<span class="col-md-12 row">
						<span class="col-1 text-right">
							<span class="text-muted">{\App\Language::translate('Subject',$MODULENAME)}</span>
						</span>
						<span class="col-11">
							<span id="emailPreview_Subject" class="">
								{$SUBJECT}
							</span>
						</span>
					</span>
				</div>
				{if !empty($ATTACHMENTS)}
					<div class="row pb-1">
						<span class="col-md-12 row">
							<span class="col-1 text-right">
								<span class="text-muted">{\App\Language::translate('Attachments_Exist',$MODULENAME)}</span>
							</span>
							<span class="col-11">
								<span id="emailPreview_attachment" class="row">
									{foreach item=ATTACHMENT from=$ATTACHMENTS}
										<a class="mr-1" href="file.php?module=OSSMailView&action=DownloadFile&record={$RECORD}&attachment={$ATTACHMENT['id']}">{$ATTACHMENT['file']}</a>
									{/foreach}
								</span>
							</span>
						</span>
					</div>
				{/if}
				<div class="row pb-1">
					<span class="col-md-12 row">
						<span class="col-1 text-right">
							<span class="text-muted">{\App\Language::translate('Content',$MODULENAME)}</span>
						</span>
						<span class="col-11">

						</span>
					</span>
				</div>
				<div>
					{$CONTENT}
				</div>
			</form>
		</div>
	</div>
{/strip}
{literal}
	<script>

	</script>
{/literal}
