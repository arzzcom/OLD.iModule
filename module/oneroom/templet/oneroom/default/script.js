function ShowImageViewer(idx) {
	var viewer = document.getElementById("ImageViewer");
	var imageLayers = GetElementsByClassName("originimglayer","div");
	for (var i=0, loop=imageLayers.length;i<loop;i++) {
		imageLayers[i].style.display = "none";
	}
	document.getElementById("ImageViewerImage"+idx).style.display = "";
}

try {
	window.addEventListener('load',function(event){
		var viewer = document.getElementById("ImageViewer");
		if (viewer) {
			//var images = viewer.getElementsByClassName("originimg");
			var images = GetElementsByClassName("originimg","img");
			for (var i=0, loop=images.length;i<loop;i++) {
				var imageWidth = images[i].offsetWidth;
				var imageHeight = images[i].offsetHeight;
				
				if (images[i].offsetWidth > viewer.offsetWidth - 10) {
					images[i].style.width = (viewer.offsetWidth - 10)+"px";
					images[i].style.height = Math.round(imageHeight * (viewer.offsetWidth - 10) / imageWidth)+"px";
				}

				if (images[i].offsetHeight > viewer.offsetHeight - 10) {
					images[i].style.height = (viewer.offsetHeight - 10)+"px";
					images[i].style.width = Math.round(imageWidth * (viewer.offsetHeight - 10) / imageHeight)+"px";
				}
				
				images[i].style.marginTop = Math.round((viewer.offsetHeight - images[i].offsetHeight)/2)+"px";
				images[i].style.marginLeft = Math.round((viewer.offsetWidth - images[i].offsetWidth)/2)+"px";
				
				if (i != 0) images[i].parentNode.style.display = "none";
			}
			document.getElementById("ImageViewerLoader").style.display = "none";
		}
		
		var ItemContents = GetElementsByClassName("smartOutput","div");
		for (var i=0, loop=ItemContents.length;i<loop;i++) {
			var images = ItemContents[i].getElementsByTagName("img");
			for (var j=0, loopj=images.length;j<loopj;j++) {
				if (images[j].offsetWidth > ItemContents[i].offsetWidth) {
					images[j].style.width = (ItemContents[i].offsetWidth)+"px";
				}
			}
		}
	},false);
} catch(e) {
	window.attachEvent('onload',function(event) {
		var viewer = document.getElementById("ImageViewer");
		if (viewer) {
			var images = GetElementsByClassName("originimg","img");
			for (var i=0, loop=images.length;i<loop;i++) {
				var imageWidth = images[i].offsetWidth;
				var imageHeight = images[i].offsetHeight;
				
				if (images[i].offsetWidth > viewer.offsetWidth - 10) {
					images[i].style.width = (viewer.offsetWidth - 10)+"px";
					images[i].style.height = Math.round(imageHeight * (viewer.offsetWidth - 10) / imageWidth)+"px";
				}

				if (images[i].offsetHeight > viewer.offsetHeight - 10) {
					images[i].style.height = (viewer.offsetHeight - 10)+"px";
					images[i].style.width = Math.round(imageWidth * (viewer.offsetHeight - 10) / imageHeight)+"px";
				}
				
				images[i].style.marginTop = Math.round((viewer.offsetHeight - images[i].offsetHeight)/2)+"px";
				images[i].style.marginLeft = Math.round((viewer.offsetWidth - images[i].offsetWidth)/2)+"px";
				
				if (i != 0) images[i].parentNode.style.display = "none";
			}
			document.getElementById("ImageViewerLoader").style.display = "none";
		}
		
		var ItemContents = GetElementsByClassName("smartOutput","div");
		for (var i=0, loop=ItemContents.length;i<loop;i++) {
			var images = ItemContents[i].getElementsByTagName("img");
			for (var j=0, loopj=images.length;j<loopj;j++) {
				if (images[j].offsetWidth > ItemContents[i].offsetWidth) {
					images[j].style.width = (ItemContents[i].offsetWidth)+"px";
				}
			}
		}
	});
}