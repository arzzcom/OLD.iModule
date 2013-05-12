function ShopAddCart() {
	var object = document.getElementById("OptionList");
	var option = object.getElementsByTagName("select");

	for (var i=0, loop=option.length;i<loop;i++) {
		alert(option[i].name);
	}
}