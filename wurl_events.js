
function wurl_postinit() {
		if (window.attachEvent)
				window.attachEvent("message", WurlApi.wurl_receive, false);
		else
				window.addEventListener("message", WurlApi.wurl_receive, false);
}

