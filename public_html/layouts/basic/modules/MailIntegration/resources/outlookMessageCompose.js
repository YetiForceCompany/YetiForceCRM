Office.onReady(info => {
	if (info.host === Office.HostType.Outlook) {
		console.log(info);
	}
});
