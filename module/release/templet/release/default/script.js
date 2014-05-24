function SearchFormKeySelect(object) {
	var object = $(object);
	
	document.forms["ModuleReleaseSearch"].key.value = object.attr("key");
	$(".searchbox > .key").text(object.text());
	$(".searchbox > .keylist").hide();
}