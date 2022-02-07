{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="popupPageContainer">
		<div class="emailTemplatesContainer">
			<h3>{\App\Language::translate($MODULE,$QUALIFIED_MODULE)}</h3>
			<hr>
			<div style="padding:0 10px">
				<table class="table table-bordered table-sm">
					<thead>
						<tr class="listViewHeaders">
							<th>
								<a>{\App\Language::translate('LBL_TEMPLATE_NAME',$QUALIFIED_MODULE)}</a>
							</th>
							<th>
								<a>{\App\Language::translate('LBL_SUBJECT',$QUALIFIED_MODULE)}</a>
							</th>
							<th>
								<a>{\App\Language::translate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</a>
							</th>
						</tr>
					</thead>
					{foreach item=EMAIL_TEMPLATE from=$EMAIL_TEMPLATES}
						<tr class="listViewEntries" data-id="{$EMAIL_TEMPLATE->get('templateid')}" data-name="{$EMAIL_TEMPLATE->get('subject')}" data-info="{$EMAIL_TEMPLATE->get('body')}">
							<td><a class="u-cursor-pointer">{\App\Language::translate($EMAIL_TEMPLATE->get('templatename',$QUALIFIED_MODULE))}</a></td>
							<td><a class="u-cursor-pointer">{\App\Language::translate($EMAIL_TEMPLATE->get('subject',$QUALIFIED_MODULE))}</a></td>
							<td>{\App\Language::translate($EMAIL_TEMPLATE->get('description',$QUALIFIED_MODULE))}</td>
						</tr>
					{/foreach}
				</table>
			</div>
		</div>
		<input type="hidden" class="triggerEventName" value="{$smarty.request.triggerEventName}" />
	</div>
{/strip}
