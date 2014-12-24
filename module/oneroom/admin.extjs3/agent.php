<?php
$mOneroom = new ModuleOneroom();
?>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/uploader/script/AzUploader.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript">
var AttachFileDelete = function(file) {
	if (confirm("해당 파일을 삭제하시겠습니까?") == true) {
		var data = file.split("|");
		var fileInput = document.getElementById("AttachImageForm").getElementsByTagName("input");
		for (var i=0, loop=fileInput.length;i<loop;i++) {
			if (fileInput[i].value == file) {
				fileInput[i].value = data[0];
				break;
			}
		}

		document.getElementById("AttachImage-"+data[0]).innerHTML = "";
		document.getElementById("AttachImage-"+data[0]).style.display = "none";
		
		var isSelectImage = false;
		var defaultImage = document.getElementById("AttachImageList").getElementsByTagName("input");
		for (var i=0, loop=defaultImage.length;i<loop;i++) {
			if (defaultImage[i].checked == true) {
				isSelectImage = true;
				break;
			}
		}
		if (isSelectImage == false) defaultImage[0].checked = true;
	}
}

var AttachFileSelectDefault = function(form) {
	var defaultImage = document.getElementById("AttachImageList").getElementsByTagName("input");
	for (var i=0, loop=defaultImage.length;i<loop;i++) {
		defaultImage[i].checked = false;
	}
	form.checked = true;
}

ContentArea = function(viewport) {
	this.viewport = viewport;

	var store1 = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","title","auth_state","owner","total_worker","total_item_live","total_item_stack","total_item_done"]
		//	fields:["idx","region","agent","category","title","areasize","price_type","price"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"agent",agent:"0",dealer:"0",region1:"0",region2:"0",region3:"0",category1:"0",category2:"0",category3:"0",keyword:""},
		listeners:{load:{fn:function(store) {
			if (Ext.getCmp("ListTab").getActiveTab().getId() == "ListTab1") {
				if (store.baseParams.category1 != "0") Ext.getCmp("Category1").setValue(store.baseParams.category1);
				if (store.baseParams.agent != "0") Ext.getCmp("Agent").setValue(store.baseParams.agent);
				if (store.baseParams.keyword) Ext.getCmp("Keyword").setValue(store.baseParams.keyword);
			}
		}}}
	});
	
	var ItemFunction = function(idx) {
		new Ext.Window({
			id:"ItemWindow",
			title:(idx ? "매물수정" : "매물등록"),
			modal:true,
			width:800,
			height:550,
			resizable:false,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"ItemForm",
					border:false,
					autoScroll:true,
					labelWidth:100,
					labelAlign:"right",
					errorReader:new Ext.form.XmlErrorReader(),
					/*
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						signinField[group]
					),
					*/
					items:[
						new Ext.form.FieldSet({
							title:"매물기본정보",
							style:"margin:10px;",
							autoWidth:true,
							items:[
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"중개업소/담당자",
									width:500,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"agent",
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
											width:100,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"중개업소선택",
											allowBlank:false,
											listeners:{
												render:{fn:function(form) {
													form.getStore().load();
												}},
												select:{fn:function(form,selected) {
													if (form.getValue() == "0") {
														Ext.getCmp("ItemForm").getForm().findField("dealer").disable();
													} else {
														Ext.getCmp("ItemForm").getForm().findField("dealer").enable();
														Ext.getCmp("ItemForm").getForm().findField("dealer").store.baseParams.agent = form.getValue();
														Ext.getCmp("ItemForm").getForm().findField("dealer").store.load();
													}
												}}
											}
										}),
										new Ext.form.ComboBox({
											hiddenName:"dealer",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											disabled:true,
											allowBlank:false,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","name","sort"]
												}),
												remoteSort:false,
												sortInfo:{field:"sort",direction:"ASC"},
												baseParams:{"action":"dealer","agent":"-1","status":"ACTIVE"},
												listeners:{load:{fn:function() {
													Ext.getCmp("ItemForm").getForm().findField("dealer").setValue("");
													Ext.getCmp("ItemForm").getForm().findField("dealer").clearInvalid();
												}}}
											}),
											width:80,
											editable:false,
											mode:"local",
											displayField:"name",
											valueField:"idx",
											emptyText:"담당자"
										})
									]
								}),
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"카테고리",
									width:500,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"category1",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											allowBlank:false,
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
											width:100,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"1차카테고리",
											listeners:{
												render:{fn:function(form) {
													form.getStore().load();
												}},
												select:{fn:function(form,selected) {
													if (form.getValue() == "0") {
														Ext.getCmp("ItemForm").getForm().findField("category2").disable();
														Ext.getCmp("ItemForm").getForm().findField("category3").disable();
													} else {
														Ext.getCmp("ItemForm").getForm().findField("category2").enable();
														Ext.getCmp("ItemForm").getForm().findField("category2").store.baseParams.parent = form.getValue();
														Ext.getCmp("ItemForm").getForm().findField("category2").store.load();
													}
												}}
											}
										}),
										new Ext.form.ComboBox({
											hiddenName:"category2",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											disabled:true,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","title","sort"]
												}),
												remoteSort:false,
												sortInfo:{field:"sort",direction:"ASC"},
												baseParams:{"action":"category","prent":"-1","is_none":"true"},
												listeners:{load:{fn:function() {
													Ext.getCmp("ItemForm").getForm().findField("category2").setValue("");
													Ext.getCmp("ItemForm").getForm().findField("category2").clearInvalid();
												}}}
											}),
											width:100,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"2차카테고리",
											listeners:{
												select:{fn:function(form,selected) {
													if (form.getValue() == "0") {
														Ext.getCmp("ItemForm").getForm().findField("category3").disable();
													} else {
														Ext.getCmp("ItemForm").getForm().findField("category3").enable();
														Ext.getCmp("ItemForm").getForm().findField("category3").store.baseParams.parent = form.getValue();
														Ext.getCmp("ItemForm").getForm().findField("category3").store.load();
													}
												}}
											}
										}),
										new Ext.form.ComboBox({
											hiddenName:"category3",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											disabled:true,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","title","sort"]
												}),
												remoteSort:false,
												sortInfo:{field:"sort",direction:"ASC"},
												baseParams:{"action":"category","prent":"-1","is_none":"true"},
												listeners:{load:{fn:function() {
													Ext.getCmp("ItemForm").getForm().findField("category3").setValue("");
													Ext.getCmp("ItemForm").getForm().findField("category3").clearInvalid();
												}}}
											}),
											width:100,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"3차카테고리"
										})
									]
								}),
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"지역",
									width:500,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"region1",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											allowBlank:false,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","title","sort"]
												}),
												remoteSort:false,
												sortInfo:{field:"sort",direction:"ASC"},
												baseParams:{"action":"region"}
											}),
											width:100,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"1차지역",
											listeners:{
												render:{fn:function(form) {
													form.getStore().load();
												}},
												select:{fn:function(form,selected) {
													if (form.getValue() == "0") {
														Ext.getCmp("ItemForm").getForm().findField("region2").disable();
														Ext.getCmp("ItemForm").getForm().findField("region3").disable();
													} else {
														Ext.getCmp("ItemForm").getForm().findField("region2").enable();
														Ext.getCmp("ItemForm").getForm().findField("region2").store.baseParams.parent = form.getValue();
														Ext.getCmp("ItemForm").getForm().findField("region2").store.load();
													}
												}}
											}
										}),
										new Ext.form.ComboBox({
											hiddenName:"region2",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											disabled:true,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","title","sort"]
												}),
												remoteSort:false,
												sortInfo:{field:"sort",direction:"ASC"},
												baseParams:{"action":"region","prent":"-1","is_none":"true"},
												listeners:{load:{fn:function() {
													Ext.getCmp("ItemForm").getForm().findField("region2").setValue("");
													Ext.getCmp("ItemForm").getForm().findField("region2").clearInvalid();
												}}}
											}),
											width:100,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"2차지역",
											listeners:{
												select:{fn:function(form,selected) {
													if (form.getValue() == "0") {
														Ext.getCmp("ItemForm").getForm().findField("region3").disable();
													} else {
														Ext.getCmp("ItemForm").getForm().findField("region3").enable();
														Ext.getCmp("ItemForm").getForm().findField("region3").store.baseParams.parent = form.getValue();
														Ext.getCmp("ItemForm").getForm().findField("region3").store.load();
													}
												}}
											}
										}),
										new Ext.form.ComboBox({
											hiddenName:"region3",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											disabled:true,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","title","sort"]
												}),
												remoteSort:false,
												sortInfo:{field:"sort",direction:"ASC"},
												baseParams:{"action":"region","prent":"-1","is_none":"true"},
												listeners:{load:{fn:function() {
													Ext.getCmp("ItemForm").getForm().findField("region3").setValue("");
													Ext.getCmp("ItemForm").getForm().findField("region3").clearInvalid();
												}}}
											}),
											width:100,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"3차지역"
										})
									]
								}),
								new Ext.form.TextField({
									fieldLabel:"매물명",
									name:"title",
									width:500,
									allowBlank:false
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"매물가격정보",
							style:"margin:10px;",
							autoWidth:true,
							items:[
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"가격구분",
									width:500,
									items:[
										new Ext.form.Checkbox({
											name:"is_rent_month",
											boxLabel:"월세(보증금+월세)",
											listeners:{check:{fn:function(form) {
												if (form.checked == true) {
													if (Ext.getCmp("ItemForm").getForm().findField("is_rent_short").checked == true) {
														Ext.Msg.show({title:"에러",msg:"월세와 단기임대는 함께 설정할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
														form.setValue(false);
														return;
													}
													Ext.getCmp("isRentMonthForm").expand();
													Ext.getCmp("ItemForm").getForm().findField("price_rent_deposit").enable();
													Ext.getCmp("ItemForm").getForm().findField("price_rent_month").enable();
												} else {
													Ext.getCmp("isRentMonthForm").collapse();
													Ext.getCmp("ItemForm").getForm().findField("price_rent_deposit").disable();
													Ext.getCmp("ItemForm").getForm().findField("price_rent_month").disable();
												}
											}}}
										}),
										new Ext.form.Checkbox({
											name:"is_rent_all",
											boxLabel:"전세",
											listeners:{check:{fn:function(form) {
												if (form.checked == true) {
													Ext.getCmp("isRentAllForm").expand();
													Ext.getCmp("ItemForm").getForm().findField("price_rent_all").enable();
												} else {
													Ext.getCmp("isRentAllForm").collapse();
													Ext.getCmp("ItemForm").getForm().findField("price_rent_all").disable();
												}
											}}}
										}),
										new Ext.form.Checkbox({
											name:"is_buy",
											boxLabel:"매매",
											listeners:{check:{fn:function(form) {
												if (form.checked == true) {
													Ext.getCmp("isBuyForm").expand();
													Ext.getCmp("ItemForm").getForm().findField("price_buy").enable();
												} else {
													Ext.getCmp("isBuyForm").collapse();
													Ext.getCmp("ItemForm").getForm().findField("price_buy").disable();
												}
											}}}
										}),
										new Ext.form.Checkbox({
											name:"is_rent_short",
											boxLabel:"단기임대(월세)",
											listeners:{check:{fn:function(form) {
												if (form.checked == true) {
													if (Ext.getCmp("ItemForm").getForm().findField("is_rent_month").checked == true) {
														Ext.Msg.show({title:"에러",msg:"월세와 단기임대는 함께 설정할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
														form.setValue(false);
														return;
													}
													Ext.getCmp("isRentShortForm").expand();
													Ext.getCmp("ItemForm").getForm().findField("price_rent_short").enable();
												} else {
													Ext.getCmp("isRentShortForm").collapse();
													Ext.getCmp("ItemForm").getForm().findField("price_rent_short").disable();
												}
											}}}
										})
									]
								}),
								new Ext.Panel({
									id:"isRentMonthForm",
									border:false,
									layout:"form",
									items:[
										new Ext.form.CompositeField({
											labelAlign:"right",
											fieldLabel:"월세가격",
											width:500,
											items:[
												new Ext.form.TextField({
													name:"price_rent_deposit",
													width:80,
													style:"text-align:right;",
													allowBlank:false,
													disabled:true,
													enableKeyEvents:true,
													listeners:{
														keydown:{fn:PressNumberOnly},
														blur:{fn:BlurNumberFormat},
														focus:{fn:FocusNumberOnly}
													}
												}),
												new Ext.form.DisplayField({
													html:"만원(보증금) / "
												}),
												new Ext.form.TextField({
													name:"price_rent_month",
													width:50,
													style:"text-align:right;",
													allowBlank:false,
													disabled:true,
													enableKeyEvents:true,
													listeners:{
														keydown:{fn:PressNumberOnly},
														blur:{fn:BlurNumberFormat},
														focus:{fn:FocusNumberOnly}
													}
												}),
												new Ext.form.DisplayField({
													html:"만원(월세)"
												})
											]
										})
									],
									listeners:{render:{fn:function(panel) {
										panel.collapse();
									}}}
								}),
								new Ext.Panel({
									id:"isRentAllForm",
									border:false,
									layout:"form",
									items:[
										new Ext.form.CompositeField({
											labelAlign:"right",
											fieldLabel:"전세가격",
											width:500,
											items:[
												new Ext.form.TextField({
													name:"price_rent_all",
													width:80,
													style:"text-align:right;",
													allowBlank:false,
													disabled:true,
													enableKeyEvents:true,
													listeners:{
														keydown:{fn:PressNumberOnly},
														blur:{fn:BlurNumberFormat},
														focus:{fn:FocusNumberOnly}
													}
												}),
												new Ext.form.DisplayField({
													html:"만원"
												})
											]
										})
									],
									listeners:{render:{fn:function(panel) {
										panel.collapse();
									}}}
								}),
								new Ext.Panel({
									id:"isBuyForm",
									border:false,
									layout:"form",
									items:[
										new Ext.form.CompositeField({
											labelAlign:"right",
											fieldLabel:"매매가격",
											width:500,
											items:[
												new Ext.form.TextField({
													name:"price_buy",
													width:80,
													style:"text-align:right;",
													allowBlank:false,
													disabled:true,
													enableKeyEvents:true,
													listeners:{
														keydown:{fn:PressNumberOnly},
														blur:{fn:BlurNumberFormat},
														focus:{fn:FocusNumberOnly}
													}
												}),
												new Ext.form.DisplayField({
													html:"만원"
												})
											]
										})
									],
									listeners:{render:{fn:function(panel) {
										panel.collapse();
									}}}
								}),
								new Ext.Panel({
									id:"isRentShortForm",
									border:false,
									layout:"form",
									items:[
										new Ext.form.CompositeField({
											labelAlign:"right",
											fieldLabel:"단기임대가격",
											width:500,
											items:[
												new Ext.form.TextField({
													name:"price_rent_short",
													width:50,
													style:"text-align:right;",
													allowBlank:false,
													disabled:true,
													enableKeyEvents:true,
													listeners:{
														keydown:{fn:PressNumberOnly},
														blur:{fn:BlurNumberFormat},
														focus:{fn:FocusNumberOnly}
													}
												}),
												new Ext.form.DisplayField({
													html:"만원"
												})
											]
										})
									],
									listeners:{render:{fn:function(panel) {
										panel.collapse();
									}}}
								}),
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"관리비",
									width:500,
									items:[
										new Ext.form.TextField({
											name:"price_maintenance",
											width:50,
											style:"text-align:right;",
											enableKeyEvents:true,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:BlurNumberFormat},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.DisplayField({
											html:"만원"
										})
									]
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"매물상세정보",
							style:"margin:10px;",
							autoWidth:true,
							items:[
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"층/전체층",
									width:500,
									items:[
										new Ext.form.NumberField({
											name:"floor1",
											style:"text-align:right;",
											allowBlank:false,
											width:50
										}),
										new Ext.form.DisplayField({
											html:"층 / "
										}),
										new Ext.form.NumberField({
											name:"floor2",
											style:"text-align:right;",
											allowBlank:false,
											width:50
										}),
										new Ext.form.DisplayField({
											html:"층(전체)"
										}),
										new Ext.form.Checkbox({
											name:"is_under",
											boxLabel:"지하/반지하",
											listeners:{check:{fn:function(form) {
												if (form.checked == true) {
													Ext.getCmp("ItemForm").getForm().findField("floor1").disable();
													Ext.getCmp("ItemForm").getForm().findField("floor2").disable();
												} else {
													Ext.getCmp("ItemForm").getForm().findField("floor1").enable();
													Ext.getCmp("ItemForm").getForm().findField("floor2").enable();
												}
											}}}
										})
									]
								}),
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"방갯수",
									width:500,
									items:[
										new Ext.form.NumberField({
											name:"rooms",
											style:"text-align:right;",
											allowBlank:false,
											width:50
										}),
										new Ext.form.DisplayField({
											html:"개"
										}),
										new Ext.form.Checkbox({
											name:"is_double",
											boxLabel:"복층"
										}),
										new Ext.form.DisplayField({
											width:100,
											style:"text-align:right;",
											html:"주차공간: "
										}),
										new Ext.form.NumberField({
											name:"parkings",
											style:"text-align:right;",
											allowBlank:false,
											width:50
										}),
										new Ext.form.DisplayField({
											html:"대"
										})
									]
								}),
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"면적",
									width:500,
									items:[
										new Ext.form.TextField({
											name:"areasize1",
											width:50,
											style:"text-align:right;",
											allowBlank:false,
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:function(form) {
													var value = GetNumberFormat(Math.round(parseFloat(form.getValue().replace(",",""))*3.3058*100)/100);
													Ext.getCmp("ItemForm").getForm().findField("areasize2").setValue(value == 0 ? "" : value);
												}},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.DisplayField({
											html:"평 / "
										}),
										new Ext.form.NumberField({
											name:"areasize2",
											style:"text-align:right;",
											readOnly:true,
											width:70
										}),
										new Ext.form.DisplayField({
											html:"㎡ (평을 입력하면 자동으로 계산됩니다.)"
										})
									]
								}),
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"실면적",
									width:500,
									items:[
										new Ext.form.TextField({
											name:"real_areasize1",
											width:50,
											style:"text-align:right;",
											listeners:{
												keydown:{fn:PressNumberOnly},
												blur:{fn:function(form) {
													var value = GetNumberFormat(Math.round(parseFloat(form.getValue().replace(",",""))*3.3058*100)/100);
													Ext.getCmp("ItemForm").getForm().findField("real_areasize2").setValue(value == 0 ? "" : value);
												}},
												focus:{fn:FocusNumberOnly}
											}
										}),
										new Ext.form.DisplayField({
											html:"평 / "
										}),
										new Ext.form.NumberField({
											name:"real_areasize2",
											style:"text-align:right;",
											readOnly:true,
											width:70
										}),
										new Ext.form.DisplayField({
											html:"㎡ (실면적을 입력하지 않을경우 면적과 같은 정보로 입력됩니다.)"
										})
									]
								}),
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"준공연도",
									width:500,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"build_year",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											store:new Ext.data.SimpleStore({
												fields:["year","display"],
												data:[<?php for ($i=1950;$i<date('Y');$i++) { echo '["'.$i.'","'.$i.'년"],'; } ?>["<?php echo date('Y'); ?>","<?php echo date('Y'); ?>년"]]
											}),
											width:80,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"year",
											value:"<?php echo date('Y'); ?>"
										})
									]
								}),
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"입주가능일",
									width:500,
									items:[
										new Ext.form.DateField({
											disabled:true,
											name:"movein_date",
											format:"Y-m-d"
										}),
										new Ext.form.Checkbox({
											boxLabel:"즉시 입주가능",
											name:"movein_date_now",
											checked:true,
											listeners:{check:{fn:function(form) {
												if (form.checked == true) {
													Ext.getCmp("ItemForm").getForm().findField("movein_date").disable();
												} else {
													Ext.getCmp("ItemForm").getForm().findField("movein_date").enable();
													Ext.getCmp("ItemForm").getForm().findField("movein_date").setValue("<?php echo date('Y-m-d'); ?>");
												}
											}}}
										})
									]
								})
							]
						}),
						FormAddressFieldSet("ItemForm"),
						new Ext.form.FieldSet({
							title:"주변정보",
							style:"margin:10px;",
							autoWidth:true,
							items:[
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"지하철역",
									width:500,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"subway1",
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
												baseParams:{"action":"database","subaction":"subway","is_none":"true"}
											}),
											width:100,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"노선선택",
											listeners:{
												render:{fn:function(form) {
													form.getStore().load();
												}},
												select:{fn:function(form,selected) {
													if (form.getValue() == "0") {
														Ext.getCmp("ItemForm").getForm().findField("subway2").disable();
														Ext.getCmp("ItemForm").getForm().findField("subway_distance").disable();
													} else {
														Ext.getCmp("ItemForm").getForm().findField("subway2").enable();
														Ext.getCmp("ItemForm").getForm().findField("subway_distance").enable();
														Ext.getCmp("ItemForm").getForm().findField("subway2").store.baseParams.parent = form.getValue();
														Ext.getCmp("ItemForm").getForm().findField("subway2").store.load();
													}
												}}
											}
										}),
										new Ext.form.ComboBox({
											hiddenName:"subway2",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											disabled:true,
											allowBlank:false,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","title","sort"]
												}),
												remoteSort:false,
												sortInfo:{field:"sort",direction:"ASC"},
												baseParams:{"action":"database","subaction":"subway","parent":"-1"},
												listeners:{load:{fn:function() {
													Ext.getCmp("ItemForm").getForm().findField("subway2").setValue("");
													Ext.getCmp("ItemForm").getForm().findField("subway2").clearInvalid();
												}}}
											}),
											width:80,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"지하철역명"
										}),
										new Ext.form.ComboBox({
											hiddenName:"subway_distance",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											disabled:true,
											allowBlank:false,
											store:new Ext.data.SimpleStore({
												fields:["value","display"],
												data:[["5","5분거리"],["10","10분거리"],["15","15분거리"],["30","30분거리"]]
											}),
											width:80,
											editable:false,
											mode:"local",
											displayField:"display",
											valueField:"value",
											emptyText:"거리선택"
										})
									]
								}),
								new Ext.form.CompositeField({
									labelAlign:"right",
									fieldLabel:"대학교",
									width:500,
									items:[
										new Ext.form.ComboBox({
											hiddenName:"university1",
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
												baseParams:{"action":"database","subaction":"university","is_none":"true"}
											}),
											width:100,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"지역선택",
											listeners:{
												render:{fn:function(form) {
													form.getStore().load();
												}},
												select:{fn:function(form,selected) {
													if (form.getValue() == "0") {
														Ext.getCmp("ItemForm").getForm().findField("university2").disable();
													} else {
														Ext.getCmp("ItemForm").getForm().findField("university2").enable();
														Ext.getCmp("ItemForm").getForm().findField("university2").store.baseParams.parent = form.getValue();
														Ext.getCmp("ItemForm").getForm().findField("university2").store.load();
													}
												}}
											}
										}),
										new Ext.form.ComboBox({
											hiddenName:"university2",
											typeAhead:true,
											triggerAction:"all",
											lazyRender:true,
											disabled:true,
											allowBlank:false,
											store:new Ext.data.Store({
												proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
												reader:new Ext.data.JsonReader({
													root:"lists",
													totalProperty:"totalCount",
													fields:["idx","title","sort"]
												}),
												remoteSort:false,
												sortInfo:{field:"sort",direction:"ASC"},
												baseParams:{"action":"database","subaction":"university","parent":"-1"},
												listeners:{load:{fn:function() {
													Ext.getCmp("ItemForm").getForm().findField("university2").setValue("");
													Ext.getCmp("ItemForm").getForm().findField("university2").clearInvalid();
												}}}
											}),
											width:80,
											editable:false,
											mode:"local",
											displayField:"title",
											valueField:"idx",
											emptyText:"대학교명"
										})
									]
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"옵션정보",
							style:"margin:10px;",
							autoWidth:true,
							items:[
								<?php
								$options = $mDB->DBfetchs($mOneroom->table['option'],array('idx','title'),"where `parent`=0",'sort,asc');
								for ($i=0, $loop=sizeof($options);$i<$loop;$i++) {
								?>
								new Ext.form.CheckboxGroup({
									fieldLabel:"<?php echo $options[$i]['title']; ?>",
									columns:6,
									items:[
										<?php
										$selects = $mDB->DBfetchs($mOneroom->table['option'],array('idx','title'),"where `parent`={$options[$i]['idx']}",'sort,asc');
										for ($j=0, $loopj=sizeof($selects);$j<$loopj;$j++) {
										?>
										new Ext.form.Checkbox({
											name:"options_<?php echo $selects[$j]['idx']; ?>",
											boxLabel:"<?php echo $selects[$j]['title']; ?>"
										})<?php echo $j == $loopj-1 ? '' : ','; ?>
										<?php } ?>
									]
								})<?php echo $i == $loop-1 ? '' : ','; ?>
								<?php } ?>
							]
						}),
						new Ext.form.FieldSet({
							title:"이미지첨부",
							style:"margin:10px;",
							layout:"fit",
							autoWidth:true,
							items:[
								new Ext.Panel({
									id:"AttachImageButtonPanel",
									autoHeight:true,
									border:false,
									html:'<div id="AttachImageForm"></div><div id="AttachImageList"></div><div style="clear:both;" id="AttachImageButton"></div>'
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"상세설명",
							style:"margin:10px;",
							layout:"fit",
							autoWidth:true,
							items:[
								new Ext.Panel({
									layout:"fit",
									autoHeight:true,
									border:false,
									items:[
										new Ext.form.TextArea({
											id:"wysiwyg",
											name:"detail",
											width:550,
											height:250,
											allowBlank:<?php echo $field[$i]['option'] == 'NOT NULL' ? 'false' : 'true'; ?>,
											listeners:{render:{fn:function() {
												nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"wysiwyg",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?mode=simple",fCreator:"createSEditorInIFrame"});
											}}}
										})
									]
								}),
								new Ext.Panel({
									border:false,
									style:"margin-top:5px;",
									html:'<div id="uploader-area"></div><div id="uploader-image"></div><div id="uploader-file"></div>'
								})
							]
						})
					],
					listeners:{actioncomplete:{fn:function(form,action) {
						if (action.type == "submit") {
							Ext.Msg.show({title:"안내",msg:"성공적으로 등록하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
								Ext.getCmp("ListTab").getActiveTab().getStore().reload();
								Ext.getCmp("ItemWindow").close();
							}});
						}
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_tick.png",
					text:"저장하기",
					handler:function() {
						if (Ext.getCmp("ItemForm").getForm().isValid() == false) {
							Ext.Msg.show({title:"에러",msg:"필수입력항목중 빠진항목이 있습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							return;
						}
						if (Ext.getCmp("ItemForm").getForm().findField("is_rent_month").checked == false && Ext.getCmp("ItemForm").getForm().findField("is_rent_all").checked == false && Ext.getCmp("ItemForm").getForm().findField("is_buy").checked == false && Ext.getCmp("ItemForm").getForm().findField("is_rent_short").checked == false) {
							Ext.Msg.show({title:"에러",msg:"가격구분은 반드시 한개 이상 체크하셔야 합니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							return;
						}
						oEditors.getById["wysiwyg"].exec("UPDATE_IR_FIELD",[]);
						if (idx) {
							Ext.getCmp("ItemForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=item&do=modify&idx="+idx,waitMsg:"데이터를 수정중입니다."});
						} else {
							Ext.getCmp("ItemForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php?action=item&do=add",waitMsg:"데이터를 저장중입니다."});
						}
					}
				})
			],
			listeners:{show:{fn:function() {
				new AzUploader({
					id:"AttachImage",
					autoRender:false,
					autoLoad:(idx ? true : false),
					allowType:"jpg,jpeg,gif,png",
					flashURL:"<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf",
					uploadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileUpload.do.php?type=attach",
					loadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileLoad.do.php?type=HTML&wysiwyg=wysiwyg-&repto="+idx,
					buttonURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_file_button.gif",
					width:75,
					height:20,
					moduleDir:"<?php echo $_ENV['dir']; ?>/module/oneroom",
					formElement:Ext.getCmp("ItemForm").getForm().el.dom,
					maxFileSize:0,
					maxTotalSize:100,
					listeners:{
						beforeLoad:AzUploaderBeforeLoad,
						onSelect:AzUploaderOnSelect,
						onProgress:AzUploaderOnProgress,
						onComplete:AzUploaderOnComplete,
						onLoad:AzUploaderOnLoad,
						onUpload:function(uploader,file) {
							var image = file.server.split("|");
							var objectImage = document.getElementById("AttachImageList");
							var objectForm = document.getElementById("AttachImageForm");
							var list = document.createElement("div");
							list.style.float = "left";
							list.innerHTML = '<div id="AttachImage-'+image[0]+'" style="margin:0px 5px 5px 0px;"><img src="'+image[4]+'" style="width:120px; border:2px solid #CCCCCC; margin:0px 0px 5px 0px;" /><div><table cellpadding="0" cellspacing="0" style="width:124px;" class="layoutfixed"><col width="15" /><col width="100%" /><col width="20" /><tr style="height:18px;"><td colspan="2" class="bold">'+GetFileSize(image[3])+'</td><td class="right"><img src="<?php echo $_ENV['dir']; ?>/images/common/btn_file_delete.gif" alt="삭제" onclick="AttachFileDelete(\''+file.server+'\')" class="pointer" /></td></tr><tr><td><input type="checkbox" name="image[]" value="'+image[0]+'" onclick="AttachFileSelectDefault(this)" /></td><td colspan="2">대표이미지로 설정</td></tr></table></div></div>';
							objectForm.innerHTML+= '<input type="hidden" name="attach[]" value="'+file.server+'" />';
							objectImage.appendChild(list);
							
							var isSelectImage = false;
							var defaultImage = document.getElementById("AttachImageList").getElementsByTagName("input");
							for (var i=0, loop=defaultImage.length;i<loop;i++) {
								if (defaultImage[i].checked == true) {
									isSelectImage = true;
									break;
								}
							}
							if (isSelectImage == false) defaultImage[0].checked = true;
						},
						onError:AzUploaderOnError
					}
				}).render("AttachImageButton");
				
				new AzUploader({
					id:"uploader",
					autoRender:false,
					autoLoad:(idx ? true : false),
					flashURL:"<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf",
					uploadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileUpload.do.php?type=wysiwyg",
					loadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileLoad.do.php?type=wysiwyg&repto="+idx,
					buttonURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_file_button.gif",
					width:75,
					height:20,
					moduleDir:"<?php echo $_ENV['dir']; ?>/module/oneroom",
					wysiwygElement:"wysiwyg",
					formElement:Ext.getCmp("ItemForm").getForm().el.dom,
					maxFileSize:0,
					maxTotalSize:100,
					listeners:{
						beforeLoad:AzUploaderBeforeLoad,
						onSelect:AzUploaderOnSelect,
						onProgress:AzUploaderOnProgress,
						onComplete:AzUploaderOnComplete,
						onLoad:AzUploaderOnLoad,
						onUpload:AzUploaderOnUpload,
						onError:AzUploaderOnError
					}
				}).render("uploader-area");
					
				Ext.getCmp("AttachImageButtonPanel").doLayout();
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"중계업소관리",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
				/*	new Ext.form.ComboBox({
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
								Ext.getCmp("ListTab").getActiveTab().getStore().baseParams.category1 = form.getValue();
								Ext.getCmp("ListTab").getActiveTab().getStore().load({params:{start:0,limit:50}});
							}}
						}
					}),
					' ',*/
					/*new Ext.form.ComboBox({
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
								Ext.getCmp("ListTab").getActiveTab().getStore().baseParams.agent = form.getValue();
								Ext.getCmp("ListTab").getActiveTab().getStore().load({params:{start:0,limit:50}});
							}}
						}
					}),
					'-',*/
					new Ext.form.TextField({
						id:"Keyword",
						width:100,
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
							Ext.getCmp("ListTab").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("Keyword").getValue();
							Ext.getCmp("ListTab").getActiveTab().getStore().load({params:{start:0,limit:50}});
						}
					}),/*
					new Ext.Button({
						text:"상세검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier_zoom_in.png",
						handler:function() {
							new Ext.Window({
								id:"DetailSearchWindow",
								title:"상세검색",
								width:500,
								modal:true,
								resizable:false,
								layout:"fit",
								autoHeight:true,
								items:[
									new Ext.form.FormPanel({
										id:"DetailSearchForm",
										labelAlign:"right",
										labelWidth:85,
										border:false,
										autoHeight:true,
										style:"padding:10px; background:#FFFFFF;",
										items:[
											new Ext.form.CompositeField({
												labelAlign:"right",
												fieldLabel:"중개업소/담당자",
												width:500,
												items:[
													new Ext.form.ComboBox({
														hiddenName:"agent",
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
														width:100,
														editable:false,
														mode:"local",
														displayField:"title",
														valueField:"idx",
														emptyText:"중개업소선택",
														allowBlank:false,
														listeners:{
															render:{fn:function(form) {
																form.getStore().load();
															}},
															select:{fn:function(form,selected) {
																if (form.getValue() == "0") {
																	Ext.getCmp("DetailSearchForm").getForm().findField("dealer").disable();
																} else {
																	Ext.getCmp("DetailSearchForm").getForm().findField("dealer").enable();
																	Ext.getCmp("DetailSearchForm").getForm().findField("dealer").store.baseParams.agent = form.getValue();
																	Ext.getCmp("DetailSearchForm").getForm().findField("dealer").store.load();
																}
															}}
														}
													}),
													new Ext.form.ComboBox({
														hiddenName:"dealer",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														disabled:true,
														allowBlank:false,
														store:new Ext.data.Store({
															proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
															reader:new Ext.data.JsonReader({
																root:"lists",
																totalProperty:"totalCount",
																fields:["idx","name","sort"]
															}),
															remoteSort:false,
															sortInfo:{field:"sort",direction:"ASC"},
															baseParams:{"action":"dealer","agent":"-1","status":"ACTIVE"},
															listeners:{load:{fn:function() {
																Ext.getCmp("DetailSearchForm").getForm().findField("dealer").setValue("");
																Ext.getCmp("DetailSearchForm").getForm().findField("dealer").clearInvalid();
															}}}
														}),
														width:80,
														editable:false,
														mode:"local",
														displayField:"name",
														valueField:"idx",
														emptyText:"담당자"
													})
												]
											}),
											new Ext.form.CompositeField({
												labelAlign:"right",
												fieldLabel:"카테고리",
												width:500,
												items:[
													new Ext.form.ComboBox({
														hiddenName:"category1",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														allowBlank:false,
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
														width:100,
														editable:false,
														mode:"local",
														displayField:"title",
														valueField:"idx",
														emptyText:"1차카테고리",
														listeners:{
															render:{fn:function(form) {
																form.getStore().load();
															}},
															select:{fn:function(form,selected) {
																if (form.getValue() == "0") {
																	Ext.getCmp("DetailSearchForm").getForm().findField("category2").disable();
																	Ext.getCmp("DetailSearchForm").getForm().findField("category3").disable();
																} else {
																	Ext.getCmp("DetailSearchForm").getForm().findField("category2").enable();
																	Ext.getCmp("DetailSearchForm").getForm().findField("category2").store.baseParams.parent = form.getValue();
																	Ext.getCmp("DetailSearchForm").getForm().findField("category2").store.load();
																}
															}}
														}
													}),
													new Ext.form.ComboBox({
														hiddenName:"category2",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														disabled:true,
														store:new Ext.data.Store({
															proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
															reader:new Ext.data.JsonReader({
																root:"lists",
																totalProperty:"totalCount",
																fields:["idx","title","sort"]
															}),
															remoteSort:false,
															sortInfo:{field:"sort",direction:"ASC"},
															baseParams:{"action":"category","prent":"-1","is_none":"true"},
															listeners:{load:{fn:function() {
																Ext.getCmp("DetailSearchForm").getForm().findField("category2").setValue("");
																Ext.getCmp("DetailSearchForm").getForm().findField("category2").clearInvalid();
															}}}
														}),
														width:100,
														editable:false,
														mode:"local",
														displayField:"title",
														valueField:"idx",
														emptyText:"2차카테고리",
														listeners:{
															select:{fn:function(form,selected) {
																if (form.getValue() == "0") {
																	Ext.getCmp("DetailSearchForm").getForm().findField("category3").disable();
																} else {
																	Ext.getCmp("DetailSearchForm").getForm().findField("category3").enable();
																	Ext.getCmp("DetailSearchForm").getForm().findField("category3").store.baseParams.parent = form.getValue();
																	Ext.getCmp("DetailSearchForm").getForm().findField("category3").store.load();
																}
															}}
														}
													}),
													new Ext.form.ComboBox({
														hiddenName:"category3",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														disabled:true,
														store:new Ext.data.Store({
															proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
															reader:new Ext.data.JsonReader({
																root:"lists",
																totalProperty:"totalCount",
																fields:["idx","title","sort"]
															}),
															remoteSort:false,
															sortInfo:{field:"sort",direction:"ASC"},
															baseParams:{"action":"category","prent":"-1","is_none":"true"},
															listeners:{load:{fn:function() {
																Ext.getCmp("DetailSearchForm").getForm().findField("category3").setValue("");
																Ext.getCmp("DetailSearchForm").getForm().findField("category3").clearInvalid();
															}}}
														}),
														width:100,
														editable:false,
														mode:"local",
														displayField:"title",
														valueField:"idx",
														emptyText:"3차카테고리"
													})
												]
											}),
											new Ext.form.CompositeField({
												labelAlign:"right",
												fieldLabel:"지역",
												width:500,
												items:[
													new Ext.form.ComboBox({
														hiddenName:"region1",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														allowBlank:false,
														store:new Ext.data.Store({
															proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
															reader:new Ext.data.JsonReader({
																root:"lists",
																totalProperty:"totalCount",
																fields:["idx","title","sort"]
															}),
															remoteSort:false,
															sortInfo:{field:"sort",direction:"ASC"},
															baseParams:{"action":"region"}
														}),
														width:100,
														editable:false,
														mode:"local",
														displayField:"title",
														valueField:"idx",
														emptyText:"1차지역",
														listeners:{
															render:{fn:function(form) {
																form.getStore().load();
															}},
															select:{fn:function(form,selected) {
																if (form.getValue() == "0") {
																	Ext.getCmp("DetailSearchForm").getForm().findField("region2").disable();
																	Ext.getCmp("DetailSearchForm").getForm().findField("region3").disable();
																} else {
																	Ext.getCmp("DetailSearchForm").getForm().findField("region2").enable();
																	Ext.getCmp("DetailSearchForm").getForm().findField("region2").store.baseParams.parent = form.getValue();
																	Ext.getCmp("DetailSearchForm").getForm().findField("region2").store.load();
																}
															}}
														}
													}),
													new Ext.form.ComboBox({
														hiddenName:"region2",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														disabled:true,
														store:new Ext.data.Store({
															proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
															reader:new Ext.data.JsonReader({
																root:"lists",
																totalProperty:"totalCount",
																fields:["idx","title","sort"]
															}),
															remoteSort:false,
															sortInfo:{field:"sort",direction:"ASC"},
															baseParams:{"action":"region","prent":"-1","is_none":"true"},
															listeners:{load:{fn:function() {
																Ext.getCmp("DetailSearchForm").getForm().findField("region2").setValue("");
																Ext.getCmp("DetailSearchForm").getForm().findField("region2").clearInvalid();
															}}}
														}),
														width:100,
														editable:false,
														mode:"local",
														displayField:"title",
														valueField:"idx",
														emptyText:"2차지역",
														listeners:{
															select:{fn:function(form,selected) {
																if (form.getValue() == "0") {
																	Ext.getCmp("DetailSearchForm").getForm().findField("region3").disable();
																} else {
																	Ext.getCmp("DetailSearchForm").getForm().findField("region3").enable();
																	Ext.getCmp("DetailSearchForm").getForm().findField("region3").store.baseParams.parent = form.getValue();
																	Ext.getCmp("DetailSearchForm").getForm().findField("region3").store.load();
																}
															}}
														}
													}),
													new Ext.form.ComboBox({
														hiddenName:"region3",
														typeAhead:true,
														triggerAction:"all",
														lazyRender:true,
														disabled:true,
														store:new Ext.data.Store({
															proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
															reader:new Ext.data.JsonReader({
																root:"lists",
																totalProperty:"totalCount",
																fields:["idx","title","sort"]
															}),
															remoteSort:false,
															sortInfo:{field:"sort",direction:"ASC"},
															baseParams:{"action":"region","prent":"-1","is_none":"true"},
															listeners:{load:{fn:function() {
																Ext.getCmp("DetailSearchForm").getForm().findField("region3").setValue("");
																Ext.getCmp("DetailSearchForm").getForm().findField("region3").clearInvalid();
															}}}
														}),
														width:100,
														editable:false,
														mode:"local",
														displayField:"title",
														valueField:"idx",
														emptyText:"3차지역"
													})
												]
											}),
											new Ext.form.TextField({
												name:"keyword",
												fieldLabel:"검색어",
												width:350
											})
										]
									})
								],
								buttons:[
									new Ext.Button({
										text:"검색",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier.png",
										handler:function() {
											var searchForm = Ext.getCmp("DetailSearchForm").getForm();
											var store = Ext.getCmp("ListTab").getActiveTab().getStore();
											store.baseParams.agent = searchForm.findField("agent").getValue();
											store.baseParams.dealer = searchForm.findField("dealer").getValue();
											
											store.baseParams.region1 = searchForm.findField("region1").getValue();
											store.baseParams.region2 = searchForm.findField("region2").getValue();
											store.baseParams.region3 = searchForm.findField("region3").getValue();
											
											store.baseParams.category1 = searchForm.findField("category1").getValue();
											store.baseParams.category2 = searchForm.findField("category2").getValue();
											store.baseParams.category3 = searchForm.findField("category3").getValue();
											
											store.baseParams.keyword = searchForm.findField("keyword").getValue();
											
											store.load({params:{start:0,limit:50}});
										}
									}),
									new Ext.Button({
										text:"취소",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_cross.png",
										handler:function() {
											Ext.getCmp("DetailSearchWindow").close();
										}
									})
								],
								listeners:{show:{fn:function() {
								}}}
							}).show();
						}
					}),*//*
					new Ext.Button({
						text:"검색취소",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier_zoom_out.png",
						handler:function() {
							Ext.getCmp("Category1").setVale("");
							Ext.getCmp("Category2").setValue("");
							Ext.getCmp("Keyword").setValue("");
							Ext.getCmp("ListTab").getActiveTab().getStore().baseParams = {action:"item",agent:"0",dealer:"0",region1:"0",region2:"0",region3:"0",category1:"0",category2:"0",category3:"0",keyword:""};
							Ext.getCmp("ListTab").getActiveTab().getStore().reload();
						}
					}),*/
					'-',
					new Ext.Button({
						text:"업체등록",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_building_add.png",
						handler:function() {
							ItemFunction();
						}
					})/*,
					new Ext.Button({
						text:"매물삭제",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_building_delete.png",
						handler:function() {
						}
					})*/ // 메뉴로 바꿀것
				],
				items:[
					new Ext.grid.GridPanel({
						title:"전체매물",
						id:"ListTab1",
						layout:"fit",
						border:false,
						autoScroll:true,
						cm:new Ext.grid.ColumnModel([
							
							{
								header:"업체명",
								dataIndex:"title",
								sortable:true,
								width:100
							},/*{
								header:"사업자등록 확인",
								dataIndex:"temp",
								sortable:true,
								width:100
							},/*{
								header:"업체주소",
							//	dataIndex:"region",
								sortable:false,
								width:100
							},							{
								header:"사업자번호",
							//	dataIndex:"agent",
								sortable:false,
								width:100
							},*/ // 메뉴에서 상세정보로 바꿀것
							{
								header:"대표성명",
								dataIndex:"owner",
								sortable:false,
								width:100
							},{
								header:"연락처",
							//	dataIndex:"title",
								sortable:false,
								width:100,
							/*	renderer:function(value,p,record) {
									return '<span class="blue">['+record.data.category+']</span> '+value;
								}*/
							},{
								header:"총 사원수",
							//	dataIndex:"dd",
								sortable:false,
								width:80,
							/*	renderer:function(value,p,record) {
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
								}*/
							},{
								header:"현재 등록매물수",
							//	dataIndex:"areasize",
								sortable:true,
								width:100,
							/*	renderer:function(value,p,record) {
									var temp = value.split(",");
									return temp[0]+"평/"+temp[1]+"평";
								}*/
							},{
								header:"누적 등록매물수",
							//	dataIndex:"areasize",
								sortable:true,
								width:100,
							/*	renderer:function(value,p,record) {
									var temp = value.split(",");
									return temp[0]+"평/"+temp[1]+"평";
								}*/
							},{
								header:"누적 완료매물수",
							//	dataIndex:"areasize",
								sortable:true,
								width:100,
							/*	renderer:function(value,p,record) {
									var temp = value.split(",");
									return temp[0]+"평/"+temp[1]+"평";
								}*/
							}
							
						]),
						store:store1,
						sm:new Ext.grid.CheckboxSelectionModel(),
						bbar:new Ext.PagingToolbar({
							pageSize:50,
							store:store1,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						listeners:{
							render:{fn:function(grid) {
								grid.getStore().load({params:{start:0,limit:50}});
							}},
							rowdblclick:{fn:function(grid,idx,e) {
								var data = grid.getStore().getAt(idx);
								ItemFunction(data.get("idx"));
							}},
							rowcontextmenu:{fn:function(grid,idx,e) {
								var menu = new Ext.menu.Menu();
								var data = grid.getStore().getAt(idx);
								e.stopEvent();
								menu.showAt(e.getXY());
							}}
						}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					
				}}}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>