function SearchOptionCheck(id) {
	var thisKey = document.forms["ModuleBoardSearch"].key.value;
	var option = id.replace("checkbox_","");

	document.getElementById("checkbox_"+thisKey).className = "checkboxoff";
	document.getElementById(id).className = "checkboxon";

	document.forms["ModuleBoardSearch"].key.value = option;
}