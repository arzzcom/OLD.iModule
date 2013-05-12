<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var EmailStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/email/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:'lists',
			totalProperty:'totalCount',
			fields:[{name:"idx",type:"int"},"sender","receiver","subject","send_date","result","read_time"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"desc"},
		baseParams:{action:"list"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"발신기록",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				border:false,
				layout:"fit",
				autoScroll:true,
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"idx",
						hidden:true,
						hideable:false
					},{
						header:"보낸사람",
						dataIndex:"sender",
						width:140,
						sortable:false
					},{
						header:"받는사람",
						dataIndex:"receiver",
						width:180,
						sortable:false
					},{
						header:"제목",
						dataIndex:"subject",
						width:300,
						sortable:false
					},{
						header:"발신시간",
						dataIndex:"send_date",
						width:120,
						sortable:false
					},{
						header:"결과",
						dataIndex:"result",
						width:40,
						sortable:false,
						renderer:function(value) {
							if (value == "TRUE") return '<span class="blue">성공</span>';
							else return '<span class="red">실패</span>';
						}
					},{
						header:"수신확인",
						dataIndex:"read_time",
						width:120,
						sortable:false,
						renderer:function(value) {
							if (value) return '<span class="blue">'+value+'</span>';
						}
					},
					new Ext.grid.CheckboxSelectionModel()
				]),
				store:EmailStore,
				bbar:new Ext.PagingToolbar({
					pageSize:30,
					store:EmailStore,
					displayInfo:true,
					displayMsg:"{0} - {1} of {2}",
					emptyMsg:"데이터없음"
				})
			})
		]
	});

	EmailStore.load({params:{start:0,limit:30}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>