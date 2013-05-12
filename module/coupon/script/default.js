function BuyCoupon(code,title,point,is_buy) {
	if (is_buy == "TRUE") {
		if (confirm("이미 보유중인 쿠폰입니다. "+title+"을 구매하시겠습니까?\n쿠폰구매시 "+point+"포인트가 차감됩니다.") == true) {
			buyFrame.location.href = ENV.dir+"/module/coupon/exec/Coupon.do.php?action=buy&code="+code;
		}
	} else {
		if (confirm(title+"을 구매하시겠습니까?\n쿠폰구매시 "+point+"포인트가 차감됩니다.") == true) {
			buyFrame.location.href = ENV.dir+"/module/coupon/exec/Coupon.do.php?action=buy&code="+code;
		}
	}
}