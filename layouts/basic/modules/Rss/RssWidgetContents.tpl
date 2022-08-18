{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="modal-header">
		<h5 class="modal-title">{\App\Language::translate('LBL_CHANGE_RSS_CHANNEL', $MODULE)}</h5>
		<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="recordNamesList">
		<div class="row">
			<div class="col-md-12">
				<ul class="nav">
					{foreach item=recordsModel from=$RSS_SOURCES}
						<li class="nav-item">
							<a href="#" class="nav-link rssLink" data-id={$recordsModel->getId()} data-url="{App\Purifier::decodeHtml($recordsModel->get('rssurl'))}" title="{App\Purifier::decodeHtml($recordsModel->getName())}">{App\Purifier::decodeHtml($recordsModel->getName())}</a>
						</li>
					{foreachelse}
						<li class="nav-item text-center">{\App\Language::translate('LBL_NO_RECORDS', $MODULE)}</li>
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
{/strip}
