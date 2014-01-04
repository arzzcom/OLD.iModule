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

$(document).ready(function() {
	var contents = $(".viewContent");
	var images = $(".showImage");
	
	images.load(function() {
		if (contents.innerWidth()-16 < $(this).width()) {
			$(this).css("width",contents.innerWidth()-16);
			$(this).css("cursor","pointer");
			$(this).parent().css("lineHeight",1);
			$(this).parent().append($("<div>").css("fontSize","12px").css("color","gray").html("이미지 클릭시 원래크기로 볼 수 있습니다."));
			$(this).click(function() {
				window.open($(this).attr("src"));
			});
			$(this).click(function() {
				window.open($(this).attr("src"));
				return false;
			});
		}
	});;
});