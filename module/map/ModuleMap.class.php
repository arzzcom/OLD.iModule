<?php
class ModuleMap extends Module {
	protected $mapID;
	protected $mapObject;
	protected $mapHTML;
	
	function __construct($property=array()) {
		$this->mapID = 'NaverMap.'.time().'.'.rand(1000000,999999);
		$this->mapObject = 'oNaverMap'.time().rand(1000000,999999);
		$this->mapHTML = '';
		parent::__construct('map');
		
		$this->mapHTML.= '<script type="text/javascript" src="http://openapi.map.naver.com/openapi/naverMap.naver?ver=2.0&key='.$this->module['apikey'].'"></script>';
		$this->mapHTML.= '<div id="'.$this->mapID.'" style="width:100%; height:100%;"></div>';
		$this->mapHTML.= '<script type="text/javascript">';
		$this->mapHTML.= 'var '.$this->mapObject.' = new nhn.api.map.Map(document.getElementById("'.$this->mapID.'"), {';
		foreach ($property as $key=>$value) $this->mapHTML.= $key.':'.$value.',';
		$this->mapHTML.= 'iModule:true';
		$this->mapHTML.= '});';
	}
	
	function SetMapTypeButton($mode,$position='top:10,right:10') {
		if ($mode == true) {
			$this->mapHTML.= $this->mapObject.'.addControl(new nhn.api.map.MapTypeBtn({position:{'.$position.'}}));';
		}
	}
	
	function SetZoomControl($mode,$position='bottom:25,right:10') {
		if ($mode == true) {
			$this->mapHTML.= $this->mapObject.'.addControl(new nhn.api.map.ZoomControl({position:{'.$position.'}}));';
		}
	}
	
	function SetCenter($point) {
		$this->mapHTML.= $this->mapObject.'.setCenter(new nhn.api.map.LatLng('.$point.'));';
	}
	
	function SetDefaultMarker($point,$title='') {
		$this->SetMarker('http://static.naver.com/maps2/icons/pin_spot2.png','28,37','14,37',$point,$title);
	}
	
	function SetMarker($icon,$size,$offset,$point,$title) {
		$this->mapHTML.= $this->mapObject.'.addOverlay(new nhn.api.map.Marker(new nhn.api.map.Icon("'.$icon.'", new nhn.api.map.Size('.$size.'), new nhn.api.map.Size('.$offset.')),{point:new nhn.api.map.LatLng('.$point.')}));';
	}
	
	function SetAddress($address) {
		$mCrawler = new Crawler();
		$XMLData = $mCrawler->GetURLString('http://openapi.map.naver.com/api/geocode.php?key='.$this->module['apikey'].'&encoding=utf-8&coord=latlng&query='.urlencode(str_replace(' ','',$address)));

		$xml = new SimpleXMLElement($XMLData);
		if ($xml->total == 0) {
			$temp = explode(' ',$address);
			array_pop($temp);
			if (sizeof($temp) > 0) return $this->SetAddress(implode(' ',$temp));
		} else {
			$this->SetCenter($xml->item->point->y.','.$xml->item->point->x);
			$this->SetDefaultMarker($xml->item->point->y.','.$xml->item->point->x,$address);
		}
	}
	
	function PrintMap() {
		$this->mapHTML.= '</script>';
		echo $this->mapHTML;
	}
	
	function GetMap() {
		$this->mapHTML.= '</script>';
		return $this->mapHTML;
	}
}
?>