<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["refererurl","visit_time","ip","keyword"]
		}),
		remoteSort:true,
		sortInfo:{field:"visit_time",direction:"DESC"},
		baseParams:{action:"status",get:"referer",date:"<?php echo date('Y-m-d'); ?>"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"유입경로",
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
						header:"유입경로",
						dataIndex:"refererurl",
						sortable:true,
						width:500,
						renderer:function(value,p,record) {
							var sHTML = "";
							if (record.data.keyword) sHTML+= '<span class="skyblue">['+record.data.keyword+']</span> ';
							sHTML+= value;
							
							return sHTML;
						}
					},{
						header:"유입시간",
						dataIndex:"visit_time",
						sortable:true,
						width:120,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"유입아이피",
						dataIndex:"ip",
						width:100,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					}
				]),
				store:store,
				bbar:new Ext.PagingToolbar({
					pageSize:100,
					store:store,
					displayInfo:true,
					displayMsg:'{0} - {1} of {2}',
					emptyMsg:"데이터없음"
				}),
				listeners:{rowdblclick:{fn:function(grid,row) {
					var referer = grid.getStore().getAt(row).get("refererurl");
					window.open(referer);
				}}}
			})
		]
	});

	store.load({params:{start:0,limit:100}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>