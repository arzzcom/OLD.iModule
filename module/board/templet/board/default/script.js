function SearchFormKeySelect(object) {
	var object = $(object);
	
	document.forms["ModuleBoardSearch"].key.value = object.attr("key");
	$(".searchbox > .key").text(object.text());
	$(".searchbox > .keylist").hide();
}