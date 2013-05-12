function CheckPoint(object) {
	var gift = Math.floor((object.value/100*90));
	document.forms["PointGift"].realPoint.value = GetNumberFormat(gift);
}