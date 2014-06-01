$(document).ready(function() {
	if ($("#iBoardGalleryContainer").length > 0) {
		$("#iBoardGalleryContainer").masonry({itemSelector:".listItem"});
		$("#iBoardGalleryContainer").imagesLoaded(function() {
			$("#iBoardGalleryContainer").masonry();
		}).progress(function(imgLoad,image) {
			if ($(image.img).attr("isThumbnail") == "TRUE") {
				var item = $(image.img).parent();
				item.removeClass("listLoading");
				if (image.isLoaded == false) {
					item.addClass("listBroken");
				} else {
					item.addClass("listLoaded");
					$(image.img).show();
					$("#iBoardGalleryContainer").masonry();
				}
			}
		});
	}
});
/*
var item = document.createElement('li');
  item.className = 'is-loading';
  var img = document.createElement('img');
  var size = Math.random() * 3 + 1;
  var width = Math.random() * 110 + 100;
  width = Math.round( width * size );
  var height = Math.round( 140 * size );
  var rando = Math.ceil( Math.random() * 1000 );
  // 10% chance of broken image src
  // random parameter to prevent cached images
  img.src = rando < 100 ? '//foo/broken-' + rando + '.jpg' :
    // use lorempixel for great random images
    '//lorempixel.com/' + width + '/' + height + '/' + '?' + rando;
  item.appendChild( img );
  return item;
*/
function SearchOptionCheck(id) {
	var thisKey = document.forms["ModuleBoardSearch"].key.value;
	var option = id.replace("checkbox_","");

	document.getElementById("checkbox_"+thisKey).className = "checkboxoff";
	document.getElementById(id).className = "checkboxon";

	document.forms["ModuleBoardSearch"].key.value = option;
}