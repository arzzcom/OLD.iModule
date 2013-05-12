<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["botname","botsite",{name:"visit",type:"int"},{name:"avgrevisit",type:"float"},"last_time","last_url"]
		}),
		remoteSort:false,
		sortInfo:{field:"visit",direction:"DESC"},
		baseParams:{action:"status",get:"log_bot",date:"<?php echo date('Y-m-d'); ?>"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"검색엔진봇로그",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_control_left.png",
						text:"이전일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",-1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);
							Ext.getCmp("ListPanel").getStore().baseParams.date = today;
							Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:100}});
						}
					}),
					' ',
					new Ext.form.DateField({
						id:"today",
						format:"Y-m-d",
						width:90,
						value:"<?php echo date('Y-m-d'); ?>",
						listeners:{select:{fn:function(form,date) {
							var today = new Date(date).format("Y-m-d");
							Ext.getCmp("ListPanel").getStore().baseParams.date = today;
							Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:100}});
						}}}
					}),
					' ',
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_control_right.png",
						iconAlign:"right",
						text:"다음일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);
							Ext.getCmp("ListPanel").getStore().baseParams.date = today;
							Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:100}});
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						header:"검색엔진 봇 이름",
						dataIndex:"botname",
						sortable:true,
						width:180
					},{
						header:"방문횟수",
						dataIndex:"visit",
						width:100,
						sortable:true,
						renderer:GridNumberFormat
					},{
						header:"평균재방문(초)",
						dataIndex:"avgrevisit",
						width:100,
						sortable:true,
						renderer:GridNumberFormat
					},{
						header:"마지막방문시간",
						dataIndex:"last_time",
						sortable:true,
						width:140,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"마지막방문페이지",
						dataIndex:"last_url",
						width:400,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					}
				]),
				store:store
			})
		]
	});

	store.load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>