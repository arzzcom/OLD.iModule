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
			url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"status",date:""}
		},
		remoteSort:true,
		sorters:[{property:"reg_date",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["date",{name:"post",type:"int"},{name:"ment",type:"int"},{name:"hit",type:"int"}],
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
		title:"게시판통계",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.Button({
						text:"이전달",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_arrow_left.png",
						handler:function() {
							var month = new Date();
							month.setFullYear(Ext.getCmp("Year").getValue());
							month.setMonth(Ext.getCmp("Month").getValue()-1);
							month.setDate(1);
							var move = Ext.Date.add(month,Ext.Date.MONTH,-1);
							Ext.getCmp("Year").setValue(Ext.Date.format(move,"Y"));
							Ext.getCmp("Month").setValue(Ext.Date.format(move,"m"));
							store.getProxy().setExtraParam("date",Ext.Date.format(move,"Y-m"));
							store.loadPage(1);
						}
					}),
					new Ext.form.ComboBox({
						id:"Year",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.ArrayStore({
							fields:["value","display"],
							data:[<?php echo implode(',',$year); ?>]
						}),
						width:70,
						editable:false,
						mode:"local",
						displayField:"display",
						valueField:"value",
						value:"<?php echo date('Y'); ?>"
					}),
					new Ext.form.ComboBox({
						id:"Month",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.ArrayStore({
							fields:["value","display"],
							data:[<?php echo implode(',',$month); ?>]
						}),
						width:60,
						editable:false,
						mode:"local",
						displayField:"display",
						valueField:"value",
						value:"<?php echo date('m'); ?>"
					}),
					new Ext.Button({
						text:"다음달",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_arrow_right.png",
						handler:function() {
							
						}
					}),
					'-',
					new Ext.Button({
						text:"그래프저장",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_disk.png",
						handler:function() {
							Ext.Msg.show({title:"확인",msg:"현재 그래프를 이미지로 저장하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.getCmp("Chart"+Ext.getCmp("ListTab").getActiveTab().getId().replace("ListPanel","")).save({type:"image/png"});
								}
							}});
						}
					})
				],
				items:[
					new Ext.Panel({
						id:"ListPanel1",
						title:"게시물등록통계",
						border:false,
						layout:"fit",
						items:[
							new Ext.chart.Chart({
								id:"Chart1",
								animate:true,
								store:store,
								axes:[{
									type:"Numeric",
									position:"left",
									grid:true
								},{
									type:"Category",
									position:"bottom",
									fields:"date",
									grid:true
								}],
								series:[{
									type:"line",
									axis:"left",
									gutter:20,
									xField:"date",
									yField:"post",
									fill:true,
									tips:{
										trackMouse:true,
										width:100,
										height:28,
										renderer:function(store,item) {
											this.update(store.get("date")+"일 : "+GetNumberFormat(store.get("post"))+"개");
										}
									}
								}]
							})
						]
					}),
					new Ext.Panel({
						id:"ListPanel2",
						title:"댓글등록통계",
						border:false,
						layout:"fit",
						items:[
							new Ext.chart.Chart({
								id:"Chart2",
								animate:true,
								store:store,
								axes:[{
									type:"Numeric",
									position:"left",
									grid:true
								},{
									type:"Category",
									position:"bottom",
									fields:"date",
									grid:true
								}],
								series:[{
									type:"line",
									axis:"left",
									gutter:20,
									xField:"date",
									yField:"ment",
									fill:true,
									tips:{
										trackMouse:true,
										width:100,
										height:28,
										renderer:function(store,item) {
											this.update(store.get("date")+"일 : "+GetNumberFormat(store.get("ment"))+"개");
										}
									}
								}]
							})
						]
					}),
					new Ext.Panel({
						id:"ListPanel3",
						title:"게시물조회통계",
						border:false,
						layout:"fit",
						items:[
							new Ext.chart.Chart({
								id:"Chart3",
								animate:true,
								store:store,
								axes:[{
									type:"Numeric",
									position:"left",
									grid:true
								},{
									type:"Category",
									position:"bottom",
									fields:"date",
									grid:true
								}],
								series:[{
									type:"line",
									axis:"left",
									gutter:20,
									xField:"date",
									yField:"hit",
									fill:true,
									tips:{
										trackMouse:true,
										width:100,
										height:28,
										renderer:function(store,item) {
											this.update(store.get("date")+"일 : "+GetNumberFormat(store.get("hit"))+"회");
										}
									}
								}]
							})
						]
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>