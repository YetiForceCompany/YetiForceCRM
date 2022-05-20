{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-AppComponents-YetiForceDetailModal-->
	<div class="modal-body">
		<p class="text-center">
			<img class="u-h-120px" src="{App\Layout::getPublicUrl('layouts/resources/Logo/logo_hor.png')}" title="YetiForceCRM" alt="YetiForceCRM" />
		</p>
		<p class="text-center">
		<ul class="list-group col-6 m-auto">
			{if $USER_MODEL->isAdminUser()}
				<li class="list-group-item d-flex justify-content-between align-items-center">
					<span class="badge badge-primary mr-2 badge-pill">APP ID</span>
					<span>{\App\YetiForce\Register::getInstanceKey()}</span>
				</li>
			{/if}
			<li class="list-group-item d-flex justify-content-between align-items-center">
				<span class="badge badge-primary mr-2 badge-info">DEVICES ID</span>
				<span>{App\Session::get('fingerprint')}</span>
			</li>
		</ul>
		</p>
		<p>Copyright © YetiForce S.A. All rights reserved.</p>
		<p class="my-2">The Program is provided AS IS, without warranty. Licensed under
			<a href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt" class="ml-2" target="_blank" rel="noreferrer noopener">
				<strong>YetiForce Public License 5.0</strong>
			</a>.
		</p>
		<p>YetiForce is based on two systems - <strong>VtigerCRM</strong> and <strong>SugarCRM</strong>.<br /><br />
		</p>
		<div class="u-word-break">
			<p>
				<span class="badge badge-secondary mr-2">License</span>
				<a href="https://github.com/YetiForceCompany/YetiForceCRM/blob/developer/licenses/LicenseEN.txt" target="_blank" rel="noreferrer noopener">
					<strong>YetiForce Public License 5.0</strong>
				</a>
			</p>
			<p>
				<span class="badge badge-primary mr-2">WWW</span>
				<a href="https://yetiforce.com" target="_blank" rel="noreferrer noopener">
					<strong>https://yetiforce.com</strong>
				</a>
			</p>
			<p>
				<span class="badge badge-success mr-2">Code</span>
				<a href="https://github.com/YetiForceCompany/YetiForceCRM" target="_blank" rel="noreferrer noopener">
					<strong>https://github.com/YetiForceCompany/YetiForceCRM</strong>
				</a>
			</p>
			<p>
				<span class="badge badge-info mr-2">Documentation</span>
				<a href="https://doc.yetiforce.com" target="_blank" rel="noreferrer noopener">
					<strong>https://doc.yetiforce.com</strong>
				</a>
			</p>
			<p>
				<span class="badge badge-warning mr-2">Issues</span>
				<a href="https://github.com/YetiForceCompany/YetiForceCRM/issues" target="_blank" rel="noreferrer noopener">
					<strong>https://github.com/YetiForceCompany/YetiForceCRM/issues</strong>
				</a>
			</p>
		</div>
		<ul class="text-center list-inline">
			<li class="yetiforceDetailsLink list-inline-item mr-3">
				<a rel="noreferrer noopener" target="_blank" href="https://github.com/YetiForceCompany/YetiForceCRM">
					<span class="fab fa-github-square" title="Github"></span>
				</a>
			</li>
			<li class="yetiforceDetailsLink list-inline-item">
				<a rel="noreferrer noopener" target="_blank" href="https://doc.yetiforce.com">
					<span class="mdi mdi-book-open-page-variant" title="YetiForce Documentation"></span>
				</a>
			</li>
			<li class="yetiforceDetailsLink list-inline-item mr-3">
				<a rel="noreferrer noopener" target="_blank" href="https://www.linkedin.com/groups/8177576">
					<span class="fab fa-linkedin" title="LinkendIn"></span>
				</a>
			</li>
			<li class="yetiforceDetailsLink list-inline-item mr-3">
				<a rel="noreferrer noopener" target="_blank" href="https://www.youtube.com/c/YetiForceCRM">
					<span class="fab fa-youtube-square" title="YouTube"></span>
				</a>
			</li>
			<li class="yetiforceDetailsLink list-inline-item mr-3">
				<a rel="noreferrer noopener" target="_blank" href="https://twitter.com/YetiForceEN">
					<span class="fab fa-twitter-square" title="Twitter"></span>
				</a>
			</li>
			<li class="yetiforceDetailsLink list-inline-item mr-3">
				<a rel="noreferrer noopener" target="_blank" href="https://www.facebook.com/YetiForce-CRM-158646854306054/">
					<span class="fab fa-facebook-square" title="Facebook"></span>
				</a>
			</li>
		</ul>
	</div>
	<!-- /tpl-AppComponents-YetiForceDetailModal-->
{/strip}
