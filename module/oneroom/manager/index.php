<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mOneroom = new ModuleOneroom();

if ($mMember->IsLogged() == false) {
	exit(REQUIRE_ONCE './login.php');
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>매물관리시스템</title>
<link rel="stylesheet" type="text/css" href="<?php echo $_ENV['dir']; ?>/css/extjs4.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $_ENV['dir']; ?>/css/extjs4.desktop.css" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/php2js.php"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs4.js"></script>

<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs4.extend.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs4.desktop.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/uploader/script/AzUploader.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>

<script type="text/javascript">
Ext.require(['*']);
</script>

<script type="text/javascript">
var ManagerModules = new Array();
var ManagerShortcuts = new Array();
Ext.Loader.setPath({
	"Ext.ux.desktop":"script",
	MyDesktop:""
});

var ItemForm = function(idx,grid) {
	new Ext.Window({
		id:"ItemFormWindow",
		title:(idx ? "매물수정" : "매물등록"),
		modal:true,
		width:800,
		height:550,
		resizable:false,
		maximizable:true,
		layout:"fit",
		items:[
			new Ext.form.FormPanel({
				id:"ItemFormPanel",
				border:false,
				autoScroll:true,
				bodyPadding:"10 10 5 10",
				fieldDefaults:{labelWidth:100,labelAlign:"right",anchor:"100%",allowBlank:false},
				items:[
					new Ext.form.FieldSet({
						title:"매물기본정보",
						items:[
							new Ext.form.FieldContainer({
								labelAlign:"right",
								fieldLabel:"카테고리",
								layout:"hbox",
								items:[
									new Ext.form.ComboBox({
										name:"category1",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"category"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("category1").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("category1").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("category1").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("category1").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("category1").getValue());
												}
											}}}
										}),
										width:100,
										editable:false,
										mode:"local",
										displayField:"title",
										valueField:"idx",
										emptyText:"1차카테고리",
										style:{marginRight:"5px"},
										listeners:{
											select:{fn:function(form,selected) {
												if (form.getValue() == "0") {
													Ext.getCmp("ItemFormPanel").getForm().findField("category2").disable();
													Ext.getCmp("ItemFormPanel").getForm().findField("category3").disable();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("category2").enable();
													Ext.getCmp("ItemFormPanel").getForm().findField("category2").getStore().getProxy().setExtraParam("parent",form.getValue());
													Ext.getCmp("ItemFormPanel").getForm().findField("category2").getStore().load();
												}
											}}
										}
									}),
									new Ext.form.ComboBox({
										name:"category2",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										disabled:true,
										allowBlank:true,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"category","prent":"-1","is_none":"true"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("category2").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("category2").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("category2").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("category2").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("category2").getValue());
												}
											}}}
										}),
										width:100,
										editable:false,
										mode:"local",
										displayField:"title",
										valueField:"idx",
										emptyText:"2차카테고리",
										style:{marginRight:"5px"},
										listeners:{
											select:{fn:function(form,selected) {
												if (form.getValue() == "0") {
													Ext.getCmp("ItemFormPanel").getForm().findField("category3").disable();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("category3").enable();
													Ext.getCmp("ItemFormPanel").getForm().findField("category3").getStore().getProxy().setExtraParam("parent",form.getValue());
													Ext.getCmp("ItemFormPanel").getForm().findField("category3").getStore().load();
												}
											}}
										}
									}),
									new Ext.form.ComboBox({
										name:"category3",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										disabled:true,
										allowBlank:true,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"category","prent":"-1","is_none":"true"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("category3").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("category3").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("category3").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("category3").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("category3").getValue());
												}
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
							new Ext.form.FieldContainer({
								fieldLabel:"지역",
								layout:"hbox",
								items:[
									new Ext.form.ComboBox({
										name:"region1",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"region"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("region1").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("region1").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("region1").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("region1").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("region1").getValue());
												}
											}}}
										}),
										width:100,
										editable:false,
										mode:"local",
										displayField:"title",
										valueField:"idx",
										emptyText:"1차지역",
										style:{marginRight:"5px"},
										listeners:{
											render:{fn:function(form) {
												form.getStore().load();
											}},
											select:{fn:function(form,selected) {
												if (form.getValue() == "0") {
													Ext.getCmp("ItemFormPanel").getForm().findField("region2").disable();
													Ext.getCmp("ItemFormPanel").getForm().findField("region3").disable();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("region2").enable();
													Ext.getCmp("ItemFormPanel").getForm().findField("region2").getStore().getProxy().setExtraParam("parent",form.getValue());
													Ext.getCmp("ItemFormPanel").getForm().findField("region2").getStore().load();
												}
											}}
										}
									}),
									new Ext.form.ComboBox({
										name:"region2",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										disabled:true,
										allowBlank:true,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"region","prent":"-1","is_none":"true"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("region2").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("region2").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("region2").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("region2").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("region2").getValue());
												}
											}}}
										}),
										width:100,
										editable:false,
										mode:"local",
										displayField:"title",
										valueField:"idx",
										emptyText:"2차지역",
										style:{marginRight:"5px"},
										listeners:{
											select:{fn:function(form,selected) {
												if (form.getValue() == "0") {
													Ext.getCmp("ItemFormPanel").getForm().findField("region3").disable();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("region3").enable();
													Ext.getCmp("ItemFormPanel").getForm().findField("region3").getStore().getProxy().setExtraParam("parent",form.getValue());
													Ext.getCmp("ItemFormPanel").getForm().findField("region3").getStore().load();
												}
											}}
										}
									}),
									new Ext.form.ComboBox({
										name:"region3",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										disabled:true,
										allowBlank:true,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"region","prent":"-1","is_none":"true"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("region3").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("region3").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("region3").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("region3").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("region3").getValue());
												}
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
								name:"title"
							})
						]
					}),
					new Ext.form.FieldSet({
						title:"매물가격정보",
						items:[
							new Ext.form.FieldContainer({
								fieldLabel:"가격구분",
								layout:"hbox",
								items:[
									new Ext.form.Checkbox({
										name:"is_rent_month",
										boxLabel:"월세(보증금+월세)",
										flex:1,
										listeners:{change:{fn:function(form) {
											if (form.checked == true) {
												if (Ext.getCmp("ItemFormPanel").getForm().findField("is_rent_short").checked == true) {
													Ext.Msg.show({title:"에러",msg:"월세와 단기임대는 함께 설정할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
													form.setValue(false);
													return;
												}
												Ext.getCmp("ItemFormIsRentMonthForm").show();
											} else {
												Ext.getCmp("ItemFormIsRentMonthForm").hide();
											}
										}}}
									}),
									new Ext.form.Checkbox({
										name:"is_rent_all",
										boxLabel:"전세",
										flex:1,
										listeners:{change:{fn:function(form) {
											if (form.checked == true) {
												Ext.getCmp("ItemFormIsRentAllForm").show();
											} else {
												Ext.getCmp("ItemFormIsRentAllForm").hide();
											}
										}}}
									}),
									new Ext.form.Checkbox({
										name:"is_buy",
										boxLabel:"매매",
										flex:1,
										listeners:{change:{fn:function(form) {
											if (form.checked == true) {
												Ext.getCmp("ItemFormIsBuyForm").show();
											} else {
												Ext.getCmp("ItemFormIsBuyForm").hide();
											}
										}}}
									}),
									new Ext.form.Checkbox({
										name:"is_rent_short",
										boxLabel:"단기임대(월세)",
										flex:1,
										listeners:{change:{fn:function(form) {
											if (form.checked == true) {
												if (Ext.getCmp("ItemFormPanel").getForm().findField("is_rent_month").checked == true) {
													Ext.Msg.show({title:"에러",msg:"월세와 단기임대는 함께 설정할 수 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
													form.setValue(false);
													return;
												}
												Ext.getCmp("ItemFormIsRentShortForm").show();
											} else {
												Ext.getCmp("ItemFormIsRentShortForm").hide();
											}
										}}}
									})
								]
							}),
							new Ext.form.FieldContainer({
								id:"ItemFormIsRentMonthForm",
								fieldLabel:"월세가격",
								layout:"hbox",
								items:[
									new Ext.form.NumberField({
										name:"price_rent_deposit",
										width:80,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										disabled:true
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;만원(보증금) /&nbsp;"
									}),
									new Ext.form.NumberField({
										name:"price_rent_month",
										width:50,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										disabled:true
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;만원(월세)"
									})
								],
								listeners:{
									render:{fn:function(panel) {
										panel.hide();
									}},
									show:{fn:function() {
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_deposit").enable();
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_month").enable();
									}},
									hide:{fn:function() {
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_deposit").disable();
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_month").disable();
									}}
								}
							}),
							new Ext.form.FieldContainer({
								id:"ItemFormIsRentAllForm",
								fieldLabel:"전세가격",
								layout:"hbox",
								items:[
									new Ext.form.NumberField({
										name:"price_rent_all",
										width:80,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										disabled:true
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;만원"
									})
								],
								listeners:{
									render:{fn:function(panel) {
										panel.hide();
									}},
									show:{fn:function() {
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_all").enable();
									}},
									hide:{fn:function() {
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_all").disable();
									}}
								}
							}),
							new Ext.form.FieldContainer({
								id:"ItemFormIsBuyForm",
								fieldLabel:"매매가격",
								layout:"hbox",
								items:[
									new Ext.form.TextField({
										name:"price_buy",
										width:80,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										disabled:true
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;만원"
									})
								],
								listeners:{
									render:{fn:function(panel) {
										panel.hide();
									}},
									show:{fn:function() {
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_all").enable();
									}},
									hide:{fn:function() {
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_all").disable();
									}}
								}
							}),
							new Ext.form.FieldContainer({
								id:"ItemFormIsRentShortForm",
								fieldLabel:"단기임대가격",
								layout:"hbox",
								items:[
									new Ext.form.TextField({
										name:"price_rent_short",
										width:70,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										disabled:true
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;만원"
									})
								],
								listeners:{
									render:{fn:function(panel) {
										panel.hide();
									}},
									show:{fn:function() {
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_all").enable();
									}},
									hide:{fn:function() {
										Ext.getCmp("ItemFormPanel").getForm().findField("price_rent_all").disable();
									}}
								}
							}),
							new Ext.form.FieldContainer({
								fieldLabel:"관리비",
								layout:"hbox",
								items:[
									new Ext.form.NumberField({
										name:"price_maintenance",
										width:70,
										decimalPrecision:0,
										mouseWheelEnabled:false
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;만원"
									})
								]
							})
						]
					}),
					new Ext.form.FieldSet({
						title:"매물상세정보",
						items:[
							new Ext.form.FieldContainer({
								fieldLabel:"층/전체층",
								layout:"hbox",
								items:[
									new Ext.form.NumberField({
										name:"floor1",
										width:50,
										decimalPrecision:0,
										mouseWheelEnabled:false
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;층 /&nbsp;"
									}),
									new Ext.form.NumberField({
										name:"floor2",
										width:50,
										decimalPrecision:0,
										mouseWheelEnabled:false
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;층(전체)&nbsp;"
									}),
									new Ext.form.Checkbox({
										name:"is_under",
										boxLabel:"지하/반지하",
										listeners:{change:{fn:function(form) {
											if (form.checked == true) {
												Ext.getCmp("ItemFormPanel").getForm().findField("floor1").disable();
												Ext.getCmp("ItemFormPanel").getForm().findField("floor2").disable();
											} else {
												Ext.getCmp("ItemFormPanel").getForm().findField("floor1").enable();
												Ext.getCmp("ItemFormPanel").getForm().findField("floor2").enable();
											}
										}}}
									})
								]
							}),
							new Ext.form.FieldContainer({
								fieldLabel:"방갯수",
								layout:"hbox",
								items:[
									new Ext.form.NumberField({
										name:"rooms",
										width:50,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										value:1
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;개&nbsp;"
									}),
									new Ext.form.Checkbox({
										name:"is_double",
										boxLabel:"복층"
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;/ 주차공간 :&nbsp;"
									}),
									new Ext.form.NumberField({
										name:"parkings",
										width:50,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										value:0
									}),
									new Ext.form.DisplayField({
										html:"대"
									})
								]
							}),
							new Ext.form.FieldContainer({
								fieldLabel:"면적",
								layout:"hbox",
								items:[
									new Ext.form.NumberField({
										name:"areasize",
										width:50,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										listeners:{change:{fn:function(form) {
											Ext.getCmp("ItemFormPanel").getForm().findField("areasize2").setValue(form.getValue()*3.3058);
										}}}
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;평 /&nbsp;"
									}),
									new Ext.form.NumberField({
										name:"areasize2",
										readOnly:true,
										width:70
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;㎡ (평을 입력하면 자동으로 계산됩니다.)"
									})
								]
							}),
							new Ext.form.FieldContainer({
								fieldLabel:"실면적",
								layout:"hbox",
								items:[
									new Ext.form.NumberField({
										name:"real_areasize",
										width:50,
										allowBlank:true,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										listeners:{change:{fn:function(form) {
											Ext.getCmp("ItemFormPanel").getForm().findField("real_areasize2").setValue(form.getValue()*3.3058);
										}}}
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;평 /&nbsp;"
									}),
									new Ext.form.NumberField({
										name:"real_areasize2",
										readOnly:true,
										allowBlank:true,
										width:70
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;㎡ (평을 입력하면 자동으로 계산됩니다.)"
									})
								]
							}),
							new Ext.form.FieldContainer({
								fieldLabel:"준공연도",
								layout:"hbox",
								items:[
									new Ext.form.NumberField({
										name:"build_year",
										width:60,
										decimalPrecision:0,
										mouseWheelEnabled:false,
										allowBlank:true,
										value:"<?php echo date('Y'); ?>"
									}),
									new Ext.form.DisplayField({
										value:"&nbsp;년"
									})
								]
							}),
							new Ext.form.FieldContainer({
								fieldLabel:"입주가능일",
								layout:"hbox",
								items:[
									new Ext.form.DateField({
										width:100,
										disabled:true,
										name:"movein_date",
										format:"Y-m-d",
										style:{marginRight:"5px"}
									}),
									new Ext.form.Checkbox({
										boxLabel:"즉시 입주가능",
										name:"movein_date_now",
										checked:true,
										listeners:{change:{fn:function(form) {
											if (form.checked == true) {
												Ext.getCmp("ItemFormPanel").getForm().findField("movein_date").disable();
											} else {
												Ext.getCmp("ItemFormPanel").getForm().findField("movein_date").enable();
												Ext.getCmp("ItemFormPanel").getForm().findField("movein_date").setValue("<?php echo date('Y-m-d'); ?>");
											}
										}}}
									})
								]
							})
						]
					}),
					FormAddressFieldSet("ItemFormPanel","매물주소정보",true),
					new Ext.form.FieldSet({
						title:"주변정보",
						items:[
							new Ext.form.FieldContainer({
								fieldLabel:"지하철역",
								layout:"hbox",
								items:[
									new Ext.form.ComboBox({
										name:"subway1",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										allowBlank:true,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"database","subaction":"subway","is_none":"true"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("subway1").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("subway1").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("subway1").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("subway1").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("subway1").getValue());
												}
											}}}
										}),
										width:100,
										editable:false,
										mode:"local",
										displayField:"title",
										valueField:"idx",
										emptyText:"노선선택",
										style:{marginRight:"5px"},
										listeners:{
											select:{fn:function(form,selected) {
												if (form.getValue() == "0") {
													Ext.getCmp("ItemFormPanel").getForm().findField("subway2").disable();
													Ext.getCmp("ItemFormPanel").getForm().findField("subway_distance").disable();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("subway2").enable();
													Ext.getCmp("ItemFormPanel").getForm().findField("subway_distance").enable();
													Ext.getCmp("ItemFormPanel").getForm().findField("subway2").getStore().getProxy().setExtraParam("parent",form.getValue());
													Ext.getCmp("ItemFormPanel").getForm().findField("subway2").getStore().load();
												}
											}}
										}
									}),
									new Ext.form.ComboBox({
										name:"subway2",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										disabled:true,
										allowBlank:false,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"database","subaction":"subway","parent":"-1"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("subway2").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("subway2").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("subway2").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("subway2").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("subway2").getValue());
												}
											}}}
										}),
										width:100,
										editable:false,
										mode:"local",
										displayField:"title",
										valueField:"idx",
										emptyText:"지하철역명",
										style:{marginRight:"5px"}
									}),
									new Ext.form.ComboBox({
										name:"subway_distance",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										disabled:true,
										allowBlank:false,
										store:new Ext.data.ArrayStore({
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
							new Ext.form.FieldContainer({
								fieldLabel:"대학교",
								layout:"hbox",
								items:[
									new Ext.form.ComboBox({
										name:"university1",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										allowBlank:true,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"database","subaction":"university","is_none":"true"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("university1").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("university1").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("university1").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("university1").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("university1").getValue());
												}
											}}}
										}),
										width:100,
										editable:false,
										mode:"local",
										displayField:"title",
										valueField:"idx",
										emptyText:"지역선택",
										style:{marginRight:"5px"},
										listeners:{
											select:{fn:function(form,selected) {
												if (form.getValue() == "0") {
													Ext.getCmp("ItemFormPanel").getForm().findField("university2").disable();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("university2").enable();
													Ext.getCmp("ItemFormPanel").getForm().findField("university2").getStore().getProxy().setExtraParam("parent",form.getValue());
													Ext.getCmp("ItemFormPanel").getForm().findField("university2").getStore().load();
												}
											}}
										}
									}),
									new Ext.form.ComboBox({
										name:"university2",
										typeAhead:true,
										triggerAction:"all",
										lazyRender:true,
										disabled:true,
										allowBlank:false,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{"action":"database","subaction":"university","parent":"-1"}
											},
											sorters:[{property:"sort",direction:"ASC"}],
											autoLoad:true,
											fields:["idx","title","sort"],
											liteners:{load:{fn:function(store) {
												if (store.find("idx",Ext.getCmp("ItemFormPanel").getForm().findField("university2").getValue(),0,false,false,true) == -1) {
													Ext.getCmp("ItemFormPanel").getForm().findField("university2").setValue("");
													Ext.getCmp("ItemFormPanel").getForm().findField("university2").clearInvalid();
												} else {
													Ext.getCmp("ItemFormPanel").getForm().findField("university2").setValue(Ext.getCmp("ItemFormPanel").getForm().findField("university2").getValue());
												}
											}}}
										}),
										width:150,
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
						items:[
							<?php
							$options = $mDB->DBfetchs($mOneroom->table['option'],array('idx','title'),"where `parent`=0",'sort,asc');
							for ($i=0, $loop=sizeof($options);$i<$loop;$i++) {
							?>
							new Ext.form.CheckboxGroup({
								fieldLabel:"<?php echo $options[$i]['title']; ?>",
								columns:6,
								allowBlank:true,
								items:[
									<?php
									$selects = $mDB->DBfetchs($mOneroom->table['option'],array('idx','title'),"where `parent`={$options[$i]['idx']}",'sort,asc');
									for ($j=0, $loopj=sizeof($selects);$j<$loopj;$j++) {
									?>
									new Ext.form.Checkbox({
										id:"options_<?php echo $selects[$j]['idx']; ?>",
										name:"options_<?php echo $selects[$j]['idx']; ?>",
										allowBlank:true,
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
						items:[
							new Ext.form.FileUploadField({
								name:"image",
								fieldLabel:"대표이미지",
								buttonText:"",
								buttonConfig:{icon:"<?php echo $_ENV['dir']; ?>/images/common/icon_disk.png"},
								allowBlank:(idx ? true : false),
								emptyText:(idx ? "대표이미지를 수정하시려면 새로운 이미지를 선택하여 주십시오." : "목록에 보일 대표이미지를 선택하여 주십시오.")
							}),
							new Ext.form.DisplayField({
								fieldLabel:"추가이미지",
								value:'<div id="ItemFormAttach-area"></div>'
							}),
							new Ext.Panel({
								id:"ItemFormAttachPanel",
								padding:"0 0 5 105",
								autoHeight:true,
								border:false,
								layout:"fit",
								html:'<div id="ItemFormAttach-image"></div><div id="ItemFormAttach-file"></div>'
							})
						]
					}),
					new Ext.form.FieldSet({
						title:"상세설명",
						autoHeight:true,
						items:[
							new Ext.form.TextArea({
								id:"ItemFormWysiwyg",
								name:"detail",
								height:500,
								listeners:{render:{fn:function() {
									nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"ItemFormWysiwyg-inputEl",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?resize=false",fCreator:"createSEditorInIFrame"});
								}}}
							}),
							new Ext.Panel({
								id:"ItemFormUploaderPanel",
								border:false,
								padding:"5 0 5 0",
								html:'<div id="ItemFormUploader-area"></div><div id="ItemFormUploader-image"></div><div id="ItemFormUploader-file"></div>'
							})
						]
					})
				]
			})
		],
		buttons:[
			new Ext.Toolbar.TextItem({
				text:"나의 현재 포인트 : 계산중...",
				hidden:(idx ? true : false),
				listeners:{render:{fn:function(button) {
					if (idx) return;
					Ext.Ajax.request({
						url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
						success:function(response) {
							var data = Ext.JSON.decode(response.responseText);
								button.setText("나의 현재 포인트 : "+GetNumberFormat(data.point)+"포인트");
						},
						failure:function() {
						},
						headers:{},
						params:{"action":"mypoint"}
					});
				}}}
			}),
			new Ext.Toolbar.TextItem({
				text:"등록비용 : 계산중...",
				hidden:(idx ? true : false),
				listeners:{render:{fn:function(button) {
					if (idx) return;
					Ext.Ajax.request({
						url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
						success:function(response) {
							var data = Ext.JSON.decode(response.responseText);
							if (data.point == "0") {
								button.setText("등록비용 : 무료");
							} else {
								if (parseInt(data.point) > parseInt(data.mypoint)) {
									Ext.Msg.show({title:"에러",msg:"회원님의 포인트가 부족합니다.<br />매물을 등록하기위해서는 "+GetNumberFormat(data.point)+"포인트가 필요합니다.<br />포인트를 구매 후 다시 시도해주시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR,fn:function() {
										Ext.getCmp("ItemFormWindow").close();
									}});
								}
								button.setText("등록비용 : "+GetNumberFormat(data.point)+"포인트");
							}
						},
						failure:function() {
						},
						headers:{},
						params:{"action":"item","get":"register_point"}
					});
				}}}
			}),
			'->',
			new Ext.Button({
				text:"확인",
				handler:function() {
					if (Ext.getCmp("ItemFormPanel").getForm().findField("is_rent_month").checked == false && Ext.getCmp("ItemFormPanel").getForm().findField("is_rent_all").checked == false && Ext.getCmp("ItemFormPanel").getForm().findField("is_buy").checked == false && Ext.getCmp("ItemFormPanel").getForm().findField("is_rent_short").checked == false) {
						Ext.Msg.show({title:"에러",msg:"가격구분은 반드시 한개 이상 체크하셔야 합니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
						return;
					}
					oEditors.getById["ItemFormWysiwyg-inputEl"].exec("UPDATE_IR_FIELD",[]);
					
					if (Ext.getCmp("ItemFormPanel").getForm().findField("detail").getValue() == "<br>" || Ext.getCmp("ItemFormPanel").getForm().findField("detail").getValue() == "") {
						Ext.Msg.show({title:"에러",msg:"상세설명을 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
						return;
					}
					
					Ext.getCmp("ItemFormPanel").getForm().submit({
						url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php?action=item&do="+(idx ? "modify&idx="+idx : "add"),
						submitEmptyText:false,
						waitTitle:"잠시만 기다려주십시오.",
						waitMsg:"매물을 "+(idx ? "수정" : "등록")+"하고 있습니다.",
						success:function(form,action) {
							Ext.Msg.show({title:"안내",msg:"성공적으로 매물을 "+(idx ? "수정" : "등록")+"하였습니다.<br />매물은 공개설정을 따로 해야 홈페이지에 노출됩니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
								if (button == "ok") {
									grid.getStore().reload();
									Ext.getCmp("ItemFormWindow").close();
									Ext.getCmp("ItemCountOpen").fireEvent("render",Ext.getCmp("ItemCountOpen"));
									Ext.getCmp("ItemCountClose").fireEvent("render",Ext.getCmp("ItemCountClose"));
									Ext.getCmp("ItemCountRemain").fireEvent("render",Ext.getCmp("ItemCountRemain"));
								}
							}});
						},
						failure:function(form,action) {
							if (action.result) {
								if (action.result.message) {
									Ext.Msg.show({title:"에러",msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return;
								}
								if (action.result.errors.image) {
									Ext.Msg.show({title:"에러",msg:action.result.errors.image,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									return;
								}
							}
							Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						}
					});
				}
			}),
			new Ext.Button({
				text:"취소",
				handler:function() {
					Ext.getCmp("ItemFormWindow").close();
				}
			})
		],
		listeners:{show:{fn:function() {
			new AzUploader({
				id:"ItemFormAttach",
				autoRender:false,
				autoLoad:(idx ? true : false),
				allowType:"jpg,jpeg,gif,png",
				flashURL:"<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf",
				uploadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileUpload.do.php?type=attach",
				loadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileLoad.do.php?type=attach&repto="+idx,
				buttonURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_file_button.gif",
				width:75,
				height:20,
				moduleDir:"<?php echo $_ENV['dir']; ?>/module/oneroom",
				formElement:"ItemFormPanel",
				panelElement:"ItemFormAttachPanel",
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
			}).render("ItemFormAttach-area");
			Ext.getCmp("ItemFormAttachPanel").doLayout();

			new AzUploader({
				id:"ItemFormUploader",
				autoRender:false,
				autoLoad:(idx ? true : false),
				flashURL:"<?php echo $_ENV['dir']; ?>/module/uploader/flash/AzUploader.swf",
				uploadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileUpload.do.php?type=wysiwyg",
				loadURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/FileLoad.do.php?type=wysiwyg&repto="+idx,
				buttonURL:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_file_button.gif",
				width:75,
				height:20,
				moduleDir:"<?php echo $_ENV['dir']; ?>/module/oneroom",
				wysiwygElement:"ItemFormWysiwyg-inputEl",
				panelElement:"ItemFormUploaderPanel",
				formElement:"ItemFormPanel",
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
			}).render("ItemFormUploader-area");
			Ext.getCmp("ItemFormUploaderPanel").doLayout();
			
			if (idx) {
				Ext.getCmp("ItemFormPanel").getForm().load({
					url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php?action=item&get=data&idx="+idx,
					submitEmptyText:false,
					waitTitle:"잠시만 기다려주십시오.",
					waitMsg:"데이터를 로딩중입니다.",
					success:function(form,action) {
						if (action.result.data.category1) form.findField("category1").fireEvent("select",form.findField("category1"));
						if (action.result.data.category2) form.findField("category2").fireEvent("select",form.findField("category2"));
						if (action.result.data.region1) form.findField("region1").fireEvent("select",form.findField("region1"));
						if (action.result.data.region2) form.findField("region2").fireEvent("select",form.findField("region2"));
						
						if (action.result.data.university1) form.findField("university1").fireEvent("select",form.findField("university1"));
						
						if (action.result.data.subway1) form.findField("subway1").fireEvent("select",form.findField("subway1"));
						if (action.result.data.subway2) form.findField("subway2").fireEvent("select",form.findField("subway2"));
						
						try {
							oEditors.getById["ItemFormWysiwyg-inputEl"].exec("PASTE_HTML",[form.findField("detail").getValue()]);
						} catch (e) {
							
						}
					},
					failure:function(form,action) {
						Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
					}
				});
			}
		}}}
	}).show();
}
</script>

<?php
REQUIRE_ONCE './manager.myinfo.php';
REQUIRE_ONCE './manager.item.php';
if ($mOneroom->GetConfig('premium_method') == 'slot') {
	REQUIRE_ONCE './manager.premium.slot.php';
} elseif ($mOneroom->GetConfig('premium_method') == 'auction') {
	REQUIRE_ONCE './manager.premium.auction.php';
} elseif ($mOneroom->GetConfig('premium_method') == 'point') {
	REQUIRE_ONCE './manager.premium.point.php';
}

if ($mOneroom->GetConfig('regionitem_method') == 'slot') {
	REQUIRE_ONCE './manager.regionitem.slot.php';
} elseif ($mOneroom->GetConfig('regionitem_method') == 'auction') {
	REQUIRE_ONCE './manager.regionitem.auction.php';
} elseif ($mOneroom->GetConfig('regionitem_method') == 'point') {
	REQUIRE_ONCE './manager.regionitem.point.php';
}

if ($mOneroom->GetConfig('prodealer_method') == 'auction') {
	REQUIRE_ONCE './manager.prodealer.auction.php';
}
REQUIRE_ONCE './manager.point.php';
?>

<script type="text/javascript">
Ext.define("MyDesktop.App",{
	extend:"Ext.ux.desktop.App",
	requires:[
		"Ext.window.MessageBox",
		"Ext.ux.desktop.ShortcutModel"
	],
	init:function() {
		this.callParent();
	},
	getModules:function(){
		return ManagerModules;
	},
	getDesktopConfig:function () {
		var me = this, ret = me.callParent();
		return Ext.apply(ret, {
			contextMenuItems:[
				{ text:"Change Settings", handler:me.onSettings, scope:me }
			],
			shortcuts:Ext.create("Ext.data.Store", {
				model:"Ext.ux.desktop.ShortcutModel",
				data:ManagerShortcuts
			}),
			wallpaper:"./images/wallpaper.jpg",
			wallpaperStretch:true
		});
	},

	getStartConfig:function() {
		var me = this, ret = me.callParent();

		return Ext.apply(ret, {
			title:"<?php echo $member["user_id"]; ?>",
			iconCls:"admin",
			height:300,
			toolConfig:{
				width:100,
				items:[{
					text:"로그아웃",
					iconCls:"logout",
					handler:function() {
						Ext.Msg.show({title:"안내",msg:"로그아웃 하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
							if (button == "yes") {
								Ext.Msg.wait("로그아웃중입니다. 잠시만 기다려주십시오.","잠시만 기다려주십시오.");
								Ext.Ajax.request({
									url:"/exec/launcher.do.php?action=logout_manager",
									success:function(response) {
										var data = Ext.JSON.decode(response.responseText);
										if (data.success == true) {
											Ext.Msg.show({title:"안내",msg:"성공적으로 로그아웃되었습니다.<br />매니져창을 닫으시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
												Launcher.ManagerLogout(button == "yes");
											}});
										} else {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										}
									},
									failure:function() {
										Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
									},
									params:{"action":"logout_manager"}
								});
							}
						}});
					}
				}]
			}
		});
	},

	getTaskbarConfig:function () {
		var ret = this.callParent();

		return Ext.apply(ret,{/*
			quickStart:[
				{name:"관리자계정 설정",iconCls:"accordion", module:"acc-win"},
				{ name:"Grid Window", iconCls:"icon-grid", module:"grid-win" }
			],*/
			startBtnIcon:"<?php echo $_ENV['dir']; ?>/images/common/icon_start.png",
			trayItems:[
				{xtype:"trayclock",flex:1}
			]
		});
	}
});


var myDesktopApp;
Ext.onReady(function () {
	myDesktopApp = new MyDesktop.App();
});
</script>

</head>

<body>
	<div style="position:absolute; top:20px; right:20px; width:250px; height:250px; font:0/0 arial;">

	</div>
</body>
</html>