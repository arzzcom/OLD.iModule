function setScroll() {
	window.scrollTo(0,0);
}

function loaded() {
	var link = document.getElementsByTagName("a");
	for (var i=0, loop=link.length;i<loop;i++) {
		link[i].onclick = function() {
			location.href = this.href;
			return false;
		}
	}
	
	var toolbar = document.getElementById("toolbar");
	var menubar = document.getElementById("menubar");

	var offsetSize = screen.height;
	if (toolbar) offsetSize = offsetSize - toolbar.offsetHeight;
	if (menubar) offsetSize = offsetSize - menubar.offsetHeight;
	
	if (document.getElementById("content").offsetHeight < offsetSize+60) document.getElementById("content").style.height = (offsetSize+60)+"px";
	setTimeout(setScroll,100);
}

document.addEventListener('DOMContentLoaded',loaded,false);