<?php
$year = array();
for ($i=date('Y');$i>1990;$i--) {
	$year[] = '["'.$i.'","'.$i.'년"]';
}
$month = array();
for ($i=1;$i<=12;$i++) {
	$month[] = '["'.sprintf('%02d',$i).'","'.sprintf('%02d',$i).'월"]';
}
?>
<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/status/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"daylog",date:"<?php echo date('Y-m-d'); ?>"}
		},
		remoteSort:true,
		sorters:[{property:"hour",direction:"ASC"}],
		autoLoad:true,
		pageSize:50,
		fields:["hour",{name:"visit",type:"int"},{name:"pageview",type:"int"}],
		listeners:{
			beforeload:{fn:function() {
				Ext.Msg.wait("데이터를 로딩중입니다.","잠시만 기다려주십시오.");
			}},
			load:{fn:function() {
				Ext.Msg.hide();
			}}
		}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"일별통계보기",
		layout:"fit",
		items:[
			new Ext.Panel({
				border:false,
				layout:"fit",
				tbar:[
					new Ext.Button({
						text:"이전일",
						icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_arrow_left.png",
						handler:function() {
							var day = new Date(Ext.getCmp("Date").getValue());
							var move = Ext.Date.add(day,Ext.Date.DAY,-1);
							Ext.getCmp("Date").setValue(Ext.Date.format(move,"Y-m-d"));
						}
					}),
					new Ext.form.DateField({
						id:"Date",
						format:"Y-m-d",
						width:90,
						value:"<?php echo date('Y-m-d'); ?>",
						listeners:{change:{fn:function(form) {
							store.getProxy().setExtraParam("date",form.getValue());
							store.loadPage(1);
						}}}
					}),
					new Ext.Button({
						text:"다음일",
						icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_arrow_right.png",
						handler:function() {
							var day = new Date(Ext.getCmp("Date").getValue());
							var move = Ext.Date.add(day,Ext.Date.DAY,1);
							Ext.getCmp("Date").setValue(Ext.Date.format(move,"Y-m-d"));
						}
					}),
					'-',
					new Ext.Button({
						text:"그래프저장",
						icon:"<?php echo $_ENV['dir']; ?>/module/banner/images/admin/icon_disk.png",
						handler:function() {
							Ext.Msg.show({title:"확인",msg:"현재 그래프를 이미지로 저장하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.getCmp("Chart"+Ext.getCmp("ListTab").getActiveTab().getId().replace("ListPanel","")).save({type:"image/png"});
								}
							}});
						}
					}),
					'->',
					{xtype:"tbtext",text:"막대그래프 : 페이지뷰 / 선그래프 : 방문수"}
				],
				items:[
					new Ext.chart.Chart({
						id:"Chart1",
						animate:true,
						store:store,
						axes:[{
							type:"Numeric",
							position:"right",
							title:"Visits"
						},{
							type:"Numeric",
							position:"left",
							title:"PageViews",
							grid:true
						},{
							type:"Category",
							position:"bottom",
							fields:"hour",
							title:"Hour",
							grid:true
						}],
						series:[{
							type:"column",
							axis:"right",
							xField:"hour",
							yField:"pageview",
							renderer:function(a,b,c,d) {
								c.fill = "#8F0E1B";
								c.opacity = 0.2;
								return c;
							},
							tips:{
								trackMouse:true,
								autoWidth:true,
								renderer:function(store,item) {
									this.update("<b>"+store.get("hour")+"시</b><br />방문수 : "+GetNumberFormat(store.get("visit"))+"명<br />페이지뷰 : "+GetNumberFormat(store.get("pageview"))+"회");
								}
							},
							label:{
								
							}
						},{
							type:"line",
							axis:"left",
							gutter:20,
							xField:"hour",
							yField:"visit",
							fill:true,
							style:{
								stroke:"#0E5391",
								fill:"#0E5391"
							},
							tips:{
								trackMouse:true,
								renderer:function(store,item) {
									this.update("<b>"+store.get("hour")+"시</b><br />방문수 : "+GetNumberFormat(store.get("visit"))+"명<br />페이지뷰 : "+GetNumberFormat(store.get("pageview"))+"회");
								}
							}
						}]
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>