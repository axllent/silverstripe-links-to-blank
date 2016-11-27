function _l2b(){
	var a = document.getElementsByTagName("a");
	for(i = 0; i<a.length; i++) {
		if(
            !a[i].target && (a[i].href.indexOf(location.host) == -1 && a[i].href.match(/^https?\:\/\//i))
			|| a[i].href.match(/\.(pdf|docx?|pp(s|tx?)|xlsx?|zip|gz|bz2|(r|t)ar|7z)$/i)
        ) {
				a[i].target = "_blank";
				if (a[i].rel && !a[i].rel.match(/noopener/i)) {
                    a[i].rel += " noopener";
                } else {
                    a[i].rel = "noopener";
                }
        }
    }
}

var w = window;
w.addEventListener ? w.addEventListener("load",_l2b,!1) : w.attachEvent && w.attachEvent("onload",_l2b);
