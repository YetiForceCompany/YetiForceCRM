<b>Before you create a new issue, please check out our [manual]
	(https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/how-to-report-bugs)
</b>
<br/>
<h4>Issue</h4>
Provide a more detailed introduction to the issue itself, and why you consider it to be a bug. Descriptions can be provided in English or Polish (remember to add [PL] for Polish in the title).
<br/>
<h4>Actual Behavior</h4>
Describe the result
<br/>
<h4>Expected Behavior</h4>
Describe what you would want the result to be
<br/>
<h4>How to trigger the error</h4>
If possible, please make a video using [ScreenToGif] (https://screentogif.codeplex.com/) or any other program used for recording actions from your desktop.
<br/>
1.<br/>
2.<br/>
3.<br/>

<h4>Configuration problems</h4>
{if $ERROR_SECURITY}
	<br/>
	<strong>Security errors:</strong>
	{\Settings_Github_Issues_Model::formatErrorsForIssue($ERROR_SECURITY)}
{/if}
{if $ERROR_STABILITY}
	<br/>
	<strong>Server stability errors:</strong>
	{\Settings_Github_Issues_Model::formatErrorsForIssue($ERROR_STABILITY)}
{/if}
{if $ERROR_ENVIRONMENT}
	<br/>
	<strong>Server environment errors:</strong>
	{\Settings_Github_Issues_Model::formatErrorsForIssue($ERROR_ENVIRONMENT)}
{/if}
{if $ERROR_DATABASE}
	<br/>
	<strong>Server database errors:</strong>
	{\Settings_Github_Issues_Model::formatErrorsForIssue($ERROR_DATABASE)}
{/if}
{if $ERROR_WRITE}
	<br/>
	<strong>Server write permissions errors:</strong>
	{\Settings_Github_Issues_Model::formatErrorsForIssue($ERROR_WRITE)}
{/if}
{if $ERROR_PERFORMANCE}
	<br/>
	<strong>Server performance errors:</strong>
	{\Settings_Github_Issues_Model::formatErrorsForIssue($ERROR_PERFORMANCE)}
{/if}
{if $ERROR_LIBRARIES}
	<br/>
	<strong>Library errors:</strong>
	{\Settings_Github_Issues_Model::formatErrorsForIssue($ERROR_LIBRARIES,true)}
{/if}
<br/>
<h4>PHP/Apache/Nginx/Browser/CRM Logs</h4>
Please include a part of logs which describes when the error occurred. The more info you provide, the quicker we will be able to solve your problem. Description how to enable logs can be found here: https://yetiforce.com/en/knowledge-base/documentation/developer-documentation/item/debugging Additionally, include a screenshot of your browser’s console (e.g. press F12 in Google Chrome).
ex. cache/logs/phpError.log, cache/logs/system.log

<h4>Your Environment</h4>
Describe the environment
<table>
	<thead>
	<tr>
		<th>Environment</th>
		<th>Version / Name</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>YetiForce</td>
		<td>{\App\Version::get()}</td>
	</tr>
	<tr>
		<td>Web server</td>
		<td>{$CONF_REPORT['environment']['serverSoftware']['www']}</td>
	</tr>
	<tr>
		<td>PHP</td>
		<td>{$PHP_VERSION}</td>
	</tr>
	<tr>
		<td>Browser</td>
		<td>{$BROWSER_INFO}</td>
	</tr>
	<tr>
		<td>Operating System</td>
		<td>{$CONF_REPORT['environment']['operatingSystem']['www']}</td>
	</tr>
	<tr>
		<td>Database</td>
		<td>{$CONF_REPORT['database']['driver']['www']} | {$CONF_REPORT['database']['serverVersion']['www']}
			| {$CONF_REPORT['database']['clientVersion']['www']}</td>
	</tr>
	</tbody>
</table>


Please check on your issue from time to time, in case we have questions or need some extra information.