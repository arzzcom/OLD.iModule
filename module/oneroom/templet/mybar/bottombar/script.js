function BottomMyBarToggle(type) {
	if (type == "History") {
		if (document.getElementById("BottomMyBar"+type+"Area").style.display == "none") {
			document.getElementById("BottomMyBar"+type).className = "menuon";
			document.getElementById("BottomMyBar"+type+"Area").style.display = "";
		} else {
			document.getElementById("BottomMyBar"+type).className = "menuoff";
			document.getElementById("BottomMyBar"+type+"Area").style.display = "none";
		}
	} else if (type == "Favority") {
		document.getElementById("BottomMyBarHistory").className = "menuoff";
		document.getElementById("BottomMyBarHistoryArea").style.display = "none";
	}
}