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
			url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"status",get:"day",date:"<?php echo date('Y-m-d'); ?>"}
		},
		remoteSort:true,
		sorters:[{property:"hour",direction:"ASC"}],
		autoLoad:true,
		pageSize:50,
		fields:["hour",{name:"view",type:"int"},{name:"hit",type:"int"},{name:"cview",type:"int"},{name:"chit",type:"int"}],
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
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_arrow_left.png",
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
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_arrow_right.png",
						handler:function() {
							var day = new Date(Ext.getCmp("Date").getValue());
							var move = Ext.Date.add(day,Ext.Date.DAY,1);
							Ext.getCmp("Date").setValue(Ext.Date.format(move,"Y-m-d"));
						}
					}),
					'-',
					new Ext.form.ComboBox({
						name:"type",
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{action:"item",get:"list",is_active:"TRUE"}
							},
							remoteSort:true,
							sorters:[{property:"idx",direction:"DESC"}],
							autoLoad:true,
							pageSize:50,
							fields:["idx","code","mno","master","is_active","type","point","paid_point","start_date","end_date","bannerpath","bannertext","url",{name:"percent",type:"float"}]
						}),
						editable:false,
						mode:"local",
						displayField:"display",
						valueField:"value",
						triggerAction:"all",
						emptyText:"광고별 보기",
						tpl:'<tpl for="."><div class="x-boundlist-item"><div style="color:blue;">[영역:{code}]</div><div><span style="color:#EF5600;">[#{idx}]</span> {url}</div></div></tpl>',
						displayTpl:'<tpl for=".">[#{idx}] {url}</tpl>',
						width:200,
						maxWidth:500,
						minWindth:400,
						listeners:{select:{fn:function(form,selected) {
							store.getProxy().setExtraParam("bno",selected.shift().data.idx);
							store.loadPage(1);
						}}}
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
					}),
					'->',
					{xtype:"tbtext",text:"막대그래프 : 클릭수 / 선그래프 : 노출수"}
				],
				items:[
					new Ext.chart.Chart({
						id:"Chart1",
						animate:true,
						store:store,
						axes:[{
							type:"Numeric",
							position:"right",
							title:"Hits"
						},{
							type:"Numeric",
							position:"left",
							title:"Views",
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
							yField:"hit",
							renderer:function(a,b,c,d) {
								c.fill = "#8F0E1B";
								c.opacity = 0.2;
								return c;
							},
							tips:{
								trackMouse:true,
								autoWidth:true,
								renderer:function(store,item) {
									this.update("<b>"+store.get("hour")+"시</b><br />"+GetNumberFormat(store.get("view"))+"회 노출<br />"+GetNumberFormat(store.get("hit"))+"회 클릭");
								}
							},
							label:{
								
							}
						},{
							type:"line",
							axis:"left",
							gutter:20,
							xField:"hour",
							yField:"view",
							fill:true,
							style:{
								stroke:"#0E5391",
								fill:"#0E5391"
							},
							tips:{
								trackMouse:true,
								renderer:function(store,item) {
									this.update("<b>"+store.get("hour")+"시</b><br />"+GetNumberFormat(store.get("view"))+"회 노출<br />"+GetNumberFormat(store.get("hit"))+"회 클릭");
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