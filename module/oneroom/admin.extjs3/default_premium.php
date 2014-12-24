<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;
	
	var ItemStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","region","agent","category","title","areasize","price_type","price"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"item",agent:"0",dealer:"0",region1:"0",region2:"0",region3:"0",category1:"0",category2:"0",category3:"0",keyword:""}
	});
	
	var DefaultPremiumStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","region","agent","category","title","areasize","price_type","price","reg_date"]
		}),
		remoteSort:true,
		sortInfo:{field:"reg_date",direction:"DESC"},
		baseParams:{action:"premium",get:"item",premiumno:"-1"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"기본프리미엄매물관리",
		layout:"fit",
		items:[
			new Ext.Panel({
				layout:"hbox",
				border:false,
				layoutConfig:{align:"stretch"},
				items:[
					new Ext.grid.GridPanel({
						id:"ItemList",
						title:"전체매물",
						margins:"5 5 5 5",
						tbar:[
							new Ext.form.ComboBox({
								id:"Category1",
								typeAhead:true,
								triggerAction:"all",
								lazyRender:true,
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title","sort"]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{"action":"category"}
								}),
								width:90,
								editable:false,
								mode:"local",
								displayField:"title",
								valueField:"idx",
								emptyText:"1차카테고리",
								listeners:{
									render:{fn:function() {
										Ext.getCmp("Category1").getStore().load();
									}},
									select:{fn:function(form,selected) {
										Ext.getCmp("ItemList").getStore().baseParams.category1 = form.getValue();
										Ext.getCmp("ItemList").getStore().load({params:{start:0,limit:50}});
									}}
								}
							}),
							' ',
							new Ext.form.ComboBox({
								id:"Agent",
								typeAhead:true,
								triggerAction:"all",
								lazyRender:true,
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:["idx","title","sort"]
									}),
									remoteSort:false,
									sortInfo:{field:"sort",direction:"ASC"},
									baseParams:{"action":"agent"}
								}),
								width:90,
								editable:false,
								mode:"local",
								displayField:"title",
								valueField:"idx",
								emptyText:"중개업소",
								listeners:{
									render:{fn:function(form) {
										form.getStore().load();
									}},
									select:{fn:function(form,selected) {
										Ext.getCmp("ItemList").getStore().baseParams.agent = form.getValue();
										Ext.getCmp("ItemList").getStore().load({params:{start:0,limit:50}});
									}}
								}
							}),
							'-',
							new Ext.form.TextField({
								id:"Keyword",
								width:80,
								emptyText:"검색어 입력"
							}),
							' ',
							new Ext.Button({
								text:"검색",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier.png",
								handler:function() {
									if (!Ext.getCmp("Keyword").getValue()) {
										Ext.Msg.show({title:"에러",msg:"검색어를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
										return false;
									}
									Ext.getCmp("ItemList").getStore().baseParams.keyword = Ext.getCmp("Keyword").getValue();
									Ext.getCmp("ItemList").getStore().load({params:{start:0,limit:50}});
								}
							}),
							new Ext.Button({
								text:"검색취소",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier_zoom_out.png",
								handler:function() {
									Ext.getCmp("Category1").setValue("");
									Ext.getCmp("Agent").setValue("");
									Ext.getCmp("Keyword").setValue("");
									Ext.getCmp("ItemList").getStore().baseParams = {action:"item",agent:"0",dealer:"0",region1:"0",region2:"0",region3:"0",category1:"0",category2:"0",category3:"0",keyword:""};
									Ext.getCmp("ItemList").getStore().reload();
								}
							})
						],
						bbar:new Ext.PagingToolbar({
							pageSize:50,
							store:ItemStore,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								header:"지역",
								dataIndex:"region",
								sortable:false,
								width:100
							},{
								header:"중개업소/담당자",
								dataIndex:"agent",
								sortable:false,
								width:110
							},{
								header:"매물명",
								dataIndex:"title",
								sortable:true,
								width:250,
								renderer:function(value,p,record) {
									return '<span class="blue">['+record.data.category+']</span> '+value;
								}
							},{
								header:"가격정보",
								dataIndex:"price_type",
								sortable:false,
								width:140,
								renderer:function(value,p,record) {
									var priceType = record.data.price_type.split(",");
									var price = record.data.price.split(",");
									var sHTML = "";
									if (priceType[0] == "TRUE") {
										sHTML+= '<span style="background:#006EBF; color:#FFFFFF;">매</span> <span class="bold" style="color:#253DA6;">'+GetNumberFormat(price[0])+'</span> ';
									}
									
									if (priceType[1] == "TRUE") {
										sHTML+= '<span style="background:#F37101; color:#FFFFFF;">전</span> <span class="bold" style="color:#253DA6;">'+GetNumberFormat(price[1])+'</span> ';
									}
									
									if (priceType[2] == "TRUE") {
										sHTML+= '<span style="background:#2A9118; color:#FFFFFF;">월</span> <span class="bold" style="color:#253DA6;">'+GetNumberFormat(price[2])+'/'+GetNumberFormat(price[3])+'</span> ';
									}
									
									if (priceType[3] == "TRUE") {
										sHTML+= '<span style="background:#2A9118; color:#FFFFFF;">단</span> <span class="bold" style="color:#253DA6;">'+GetNumberFormat(price[3])+'</span> ';
									}
									return sHTML;
								}
							},{
								header:"면적/실면적",
								dataIndex:"areasize",
								sortable:true,
								width:80,
								renderer:function(value,p,record) {
									var temp = value.split(",");
									return temp[0]+"평/"+temp[1]+"평";
								}
							}
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:ItemStore,
						flex:1
					}),
					new Ext.grid.GridPanel({
						id:"PremiumList",
						title:"기본프리미엄매물",
						margins:"5 5 5 0",
						tbar:[
							new Ext.Button({
								text:"프리미엄매물추가",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_arrow_right.png",
								handler:function() {
									var checked = Ext.getCmp("ItemList").selModel.getSelections();
									if (checked == 0) {
										Ext.Msg.show({title:"에러",msg:"추가할 매물을 좌측목록에서 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										return;
									}
									
									var idxs = new Array();
									for (var i=0, loop=checked.length;i<loop;i++) {
										idxs.push(checked[i].get("idx"));
									}
									var idx = idxs.join(",");
									
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 추가하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											Ext.getCmp("PremiumList").getStore().reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
										},
										headers:{},
										params:{"action":"default_premium","do":"add","idx":idx}
									});
								}
							}),
							new Ext.Button({
								text:"프리미엄매물삭제",
								icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
								handler:function() {
									
								}
							})
						],
						bbar:new Ext.PagingToolbar({
							pageSize:50,
							store:DefaultPremiumStore,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.CheckboxSelectionModel(),
							{
								header:"지역",
								dataIndex:"region",
								sortable:false,
								width:100
							},{
								header:"중개업소/담당자",
								dataIndex:"agent",
								sortable:false,
								width:110
							},{
								header:"매물명",
								dataIndex:"title",
								sortable:true,
								width:250,
								renderer:function(value,p,record) {
									return '<span class="blue">['+record.data.category+']</span> '+value;
								}
							},{
								header:"가격정보",
								dataIndex:"price_type",
								sortable:false,
								width:140,
								renderer:function(value,p,record) {
									var priceType = record.data.price_type.split(",");
									var price = record.data.price.split(",");
									var sHTML = "";
									if (priceType[0] == "TRUE") {
										sHTML+= '<span style="background:#006EBF; color:#FFFFFF;">매</span> <span class="bold" style="color:#253DA6;">'+GetNumberFormat(price[0])+'</span> ';
									}
									
									if (priceType[1] == "TRUE") {
										sHTML+= '<span style="background:#F37101; color:#FFFFFF;">전</span> <span class="bold" style="color:#253DA6;">'+GetNumberFormat(price[1])+'</span> ';
									}
									
									if (priceType[2] == "TRUE") {
										sHTML+= '<span style="background:#2A9118; color:#FFFFFF;">월</span> <span class="bold" style="color:#253DA6;">'+GetNumberFormat(price[2])+'/'+GetNumberFormat(price[3])+'</span> ';
									}
									
									if (priceType[3] == "TRUE") {
										sHTML+= '<span style="background:#2A9118; color:#FFFFFF;">단</span> <span class="bold" style="color:#253DA6;">'+GetNumberFormat(price[3])+'</span> ';
									}
									return sHTML;
								}
							},{
								header:"면적/실면적",
								dataIndex:"areasize",
								sortable:true,
								width:80,
								renderer:function(value,p,record) {
									var temp = value.split(",");
									return temp[0]+"평/"+temp[1]+"평";
								}
							}
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:DefaultPremiumStore,
						flex:1
					})
				]
			})
		],
		listeners:{render:{fn:function() {
			Ext.getCmp("ItemList").getStore().load({params:{start:0,limit:50}});
			Ext.getCmp("PremiumList").getStore().load({params:{start:0,limit:50}});
		}}}
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>