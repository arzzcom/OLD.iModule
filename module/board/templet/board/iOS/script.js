function loaded() {
	var link = document.getElementsByTagName("a");
	for (var i=0, loop=link.length;i<loop;i++) {
		link[i].onclick = function() {
			if (this.getAttribute("target") != null) {
				eval(this.target+".location.href = '"+this.href+"';");
			} else {
				location.href = this.href;
			}
			return false;
		}
	}
	
	setTimeout(scrollTo,0,0,1);
}

document.addEventListener('DOMContentLoaded',loaded,false);