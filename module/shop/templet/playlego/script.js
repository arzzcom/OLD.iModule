var OptionID = 1;

function ShopAddOption() {
	var list = document.getElementById("OptionList");
	var option = document.getElementById("OptionList0");

	var newOption = document.createElement("div");
	newOption.setAttribute("id","OptionList"+OptionID);
	newOption.className = "option";
	newOption.innerHTML = option.innerHTML;

	newOption.getElementsByTagName("img")[0].setAttribute("OptionID",OptionID);
	newOption.getElementsByTagName("img")[1].setAttribute("OptionID",OptionID);

	newOption.getElementsByTagName("img")[2].style.display = "";
	newOption.getElementsByTagName("img")[2].setAttribute("OptionID",OptionID);
	newOption.getElementsByTagName("img")[3].style.display = "none";
	newOption.getElementsByTagName("img")[3].setAttribute("OptionID",OptionID);

	list.appendChild(newOption);
	OptionID++;
}

function ShopDelOption(object) {
	if (object.getAttribute("OptionID")) {
		var list = document.getElementById("OptionList");

		list.removeChild(document.getElementById("OptionList"+object.getAttribute("OptionID")));
	}
}

function ShopAddEA(object) {
	var ea = document.getElementById("OptionList"+object.getAttribute("OptionID")).getElementsByTagName("input")[0];
	if (!ea.value) ea.value = 0;
	ea.value = parseInt(ea.value)+1;
}

function ShopDelEA(object) {
	var ea = document.getElementById("OptionList"+object.getAttribute("OptionID")).getElementsByTagName("input")[0];
	if (!ea.value) ea.value = 0;

	if (ea.value > 0) ea.value = parseInt(ea.value)-1;
}