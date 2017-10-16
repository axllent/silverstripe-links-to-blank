function _l2b() {
	var l = document.getElementsByTagName("a");
	var len = l.length;
	for (i=0; i<len; ++i) {
		var a = l[i];
		if (a.href && !a.target && (a.href.indexOf(location.host) == -1 && a.href.match(/^https?\:\/\//i)) ||
			a.href.match(/\.(pdf|docx?|pp(s|tx?)|xlsx?|zip|gz|bz2|(r|t)ar|7z)$/i)
		) {
			a.target = "_blank";
			a.rel += a.rel ? !a.rel.match(/noopener/i) ? " noopener" : '' : 'noopener';
		}
	}
}

var w = window;
w.addEventListener ? w.addEventListener("load",_l2b,!1) : w.attachEvent && w.attachEvent("onload",_l2b);
