<script type="text/javascript">
new Ext.form.FormPanel({
	id:"ConfigForm",
	border:false,
	fieldDefaults:{labelWidth:100,labelAlign:"right",anchor:"100%",allowBlank:false},
	bodyPadding:"10 10 5 10",
	items:[
		new Ext.form.FieldSet({
			title:"매물등록설정",
			items:[
				new Ext.form.FieldContainer({
					fieldLabel:"매물등록포인트",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"register_point",
							width:80,
							value:1000
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;포인트 (매물등록시 차감할 포인트를 설정합니다.)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"매물게시기간",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"open_time",
							width:80,
							value:60
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;일 (매물공개시점부터 매물이 게시될 기간을 설정합니다. 0:무제한)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"매물공개제한",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"open_limit",
							width:80,
							value:0
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;개 (1인당 공개가능한 매물갯수를 설정합니다. 0:무제한)"
						})
					]
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"네이버맵API설정",
			items:[
				new Ext.form.FieldContainer({
					fieldLabel:"APIKEY",
					layout:"hbox",
					items:[
						new Ext.form.TextField({
							name:"mapkey",
							width:240,
							maxLength:40,
							allowBlank:true
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;네이버에서 발급받은 네이버맵 API키를 입력하세요."
						})
					]
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"프리미엄매물설정",
			items:[
				new Ext.form.ComboBox({
					fieldLabel:"등록방법",
					name:"premium_method",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.ArrayStore({
						fields:["value","display"],
						data:[["admin","관리자만 등록가능(기본 프리미엄매물관리에서 등록된 매물만 허용"],["auction","최대매물갯수만큼의 공간을 매월 경매를 통해 등록권한을 부여(최대매물갯수 무제한설정 불가능)"],["slot","슬롯아이템을 구매하여 사용(활성화된 슬롯수가 최대매물갯수를 초과하면 구매불가)"],["point","매물등록당 포인트를 지불하고 등록 (최대매물갯수를 초과하면 등록불가)"]]
					}),
					editable:false,
					mode:"local",
					displayField:"display",
					valueField:"value",
					value:"slot",
					listeners:{select:{fn:function(form) {
						if (form.getValue() == "point") {
							Ext.getCmp("ConfigForm").getForm().findField("premium_point").enable();
							Ext.getCmp("ConfigForm").getForm().findField("premium_time").enable();
						} else {
							Ext.getCmp("ConfigForm").getForm().findField("premium_point").disable();
							Ext.getCmp("ConfigForm").getForm().findField("premium_time").disable();
						}
						
						if (form.getValue() == "auction") {
							Ext.getCmp("ConfigForm").getForm().findField("premium_limit").setMinValue(1);
						} else {
							Ext.getCmp("ConfigForm").getForm().findField("premium_limit").setMinValue(0);
						}
					}}}
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"최대매물갯수",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"premium_limit",
							width:80,
							value:10,
							minValue:0
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;개 (최대로 등록받을 매물갯수, 웹페이지에 보이는 갯수와는 무관, 0:무제한)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"등록포인트",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"premium_point",
							width:80,
							value:1000,
							disabled:true
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;포인트 (등록방법이 포인트지불방식일 경우 등록당 포인트, 0:무료)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"노출기간",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"premium_time",
							width:80,
							minValue:1,
							value:30,
							disabled:true
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;일 (등록방법이 포인트지불방식일 경우 등록후 노출기간)"
						})
					]
				}),
				new Ext.form.ComboBox({
					fieldLabel:"정렬방법",
					name:"premium_sort",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.ArrayStore({
						fields:["value","display"],
						data:[["random","프리미엄매물스킨에 지정한 갯수한도내에서 등록된 프리미엄매물을 랜덤하게 정렬"],["sort","순차정렬 (등록방법이 경매일 경우 높은가격순, 나머지방법에서는 등록순서순(스킨에 따라 일부매물은 노출되지 않을 수 있음)"]]
					}),
					editable:false,
					mode:"local",
					displayField:"display",
					valueField:"value",
					value:"random"
				}),
				new Ext.form.Checkbox({
					fieldLabel:"우선노출적용",
					name:"premium_searching",
					value:"on",
					boxLabel:"사용자의 검색조건에 따라 검색조건에 가장 부합하는 매물을 우선적으로 노출"
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"지역추천매물설정",
			items:[
				new Ext.form.ComboBox({
					fieldLabel:"등록방법",
					name:"regionitem_method",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.ArrayStore({
						fields:["value","display"],
						data:[["admin","관리자만 등록가능(기본 지역추천매물관리에서 등록된 매물만 허용"],["auction","최대매물갯수만큼의 공간을 매월 경매를 통해 등록권한을 부여(최대매물갯수 무제한설정 불가능)"],["slot","슬롯아이템을 구매하여 사용(활성화된 슬롯수가 최대매물갯수를 초과하면 구매불가)"],["point","매물등록당 포인트를 지불하고 등록 (최대매물갯수를 초과하면 등록불가)"]]
					}),
					editable:false,
					mode:"local",
					displayField:"display",
					valueField:"value",
					value:"slot",
					listeners:{select:{fn:function(form) {
						if (form.getValue() == "point") {
							Ext.getCmp("ConfigForm").getForm().findField("regionitem_point").enable();
							Ext.getCmp("ConfigForm").getForm().findField("regionitem_time").enable();
						} else {
							Ext.getCmp("ConfigForm").getForm().findField("regionitem_point").disable();
							Ext.getCmp("ConfigForm").getForm().findField("regionitem_time").disable();
						}
						
						if (form.getValue() == "auction") {
							Ext.getCmp("ConfigForm").getForm().findField("regionitem_limit").setMinValue(1);
						} else {
							Ext.getCmp("ConfigForm").getForm().findField("regionitem_limit").setMinValue(0);
						}
					}}}
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"최대매물갯수",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"regionitem_limit",
							width:80,
							value:10,
							minValue:0
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;개 (최대로 등록받을 매물갯수, 웹페이지에 보이는 갯수와는 무관, 0:무제한)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"등록포인트",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"regionitem_point",
							width:80,
							value:1000,
							disabled:true
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;포인트 (등록방법이 포인트지불방식일 경우 등록당 포인트, 0:무료)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"노출기간",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"regionitem_time",
							width:80,
							minValue:1,
							value:30,
							disabled:true
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;일 (등록방법이 포인트지불방식일 경우 등록후 노출기간)"
						})
					]
				}),
				new Ext.form.ComboBox({
					fieldLabel:"정렬방법",
					name:"regionitem_sort",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.ArrayStore({
						fields:["value","display"],
						data:[["random","지역추천매물스킨에 지정한 갯수한도내에서 등록된 지역추천매물을 랜덤하게 정렬"],["sort","순차정렬 (등록방법이 경매일 경우 높은가격순, 나머지방법에서는 등록순서순(스킨에 따라 일부매물은 노출되지 않을 수 있음)"]]
					}),
					editable:false,
					mode:"local",
					displayField:"display",
					valueField:"value",
					value:"random"
				}),
				new Ext.form.Checkbox({
					fieldLabel:"우선노출적용",
					name:"regionitem_searching",
					value:"on",
					boxLabel:"사용자의 검색조건에 따라 검색조건에 가장 부합하는 매물을 우선적으로 노출"
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"지역전문가설정",
			items:[
				new Ext.form.ComboBox({
					fieldLabel:"등록방법",
					name:"prodealer_method",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.ArrayStore({
						fields:["value","display"],
						data:[["admin","관리자만 등록가능(기본 지역전문가관리에서 등록된 사람만 허용"],["auction","최대등록명수만큼의 공간을 매월 경매를 통해 등록권한을 부여(최대등록인원수 무제한설정 불가능)"],["point","지역전문가 등록시 포인트를 지불하고 등록 (최대등록인원수를 초과하면 등록불가)"]]
					}),
					editable:false,
					mode:"local",
					displayField:"display",
					valueField:"value",
					value:"auction",
					listeners:{select:{fn:function(form) {
						if (form.getValue() == "admin") {
							Ext.getCmp("ConfigForm").getForm().findField("prodealer_limit").disable();
						} else {
							Ext.getCmp("ConfigForm").getForm().findField("prodealer_limit").enable();
						}
						
						if (form.getValue() == "point") {
							Ext.getCmp("ConfigForm").getForm().findField("prodealer_point").enable();
							Ext.getCmp("ConfigForm").getForm().findField("prodealer_time").enable();
						} else {
							Ext.getCmp("ConfigForm").getForm().findField("prodealer_point").disable();
							Ext.getCmp("ConfigForm").getForm().findField("prodealer_time").disable();
						}
						
						if (form.getValue() == "auction") {
							Ext.getCmp("ConfigForm").getForm().findField("prodealer_limit").setMinValue(1);
						} else {
							Ext.getCmp("ConfigForm").getForm().findField("prodealer_limit").setMinValue(0);
						}
					}}}
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"최대등록명수",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"prodealer_limit",
							width:60,
							minValue:1,
							value:<?php echo isset($config['prodealer_limit']) == true ? $config['prodealer_limit'] : '10'; ?>
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;명 (최대로 등록받을 인원수, 웹페이지에 보이는 인원과는 무관, 0:무제한)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"등록포인트",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"prodealer_point",
							width:80,
							value:1000,
							disabled:true
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;포인트 (등록방법이 포인트지불방식일 경우 등록당 포인트, 0:무료)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"노출기간",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"prodealer_time",
							width:80,
							minValue:1,
							value:30,
							disabled:true
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;일 (등록방법이 포인트지불방식일 경우 등록후 노출기간)"
						})
					]
				})
			]
		}),
		new Ext.form.FieldSet({
			title:"경매설정 (프리미엄 / 지역전문가 등록방법이 경매방식일 경우 아래 경매설정에 따라 경매가 진행됩니다.)",
			items:[
				new Ext.form.FieldContainer({
					fieldLabel:"최소입찰포인트",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"auction_min",
							width:80,
							value:1000
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;포인트 (프리미엄매물 공간 경매시 최소입찰포인트를 설정합니다.)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"입찰참가포인트",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							fieldLabel:"",
							name:"auction_point",
							width:80,
							value:0
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;포인트 (매 입찰시마다 입찰자가 입찰액과 별도로 지불해야하는 포인트, 0:무료)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"입찰참여제한",
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"auction_limit",
							width:80,
							value:0
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;회/월 (매달 입찰가능한 횟수, 0:무제한)"
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:"경매기간",
					layout:"hbox",
					items:[
						new Ext.form.ComboBox({
							name:"auction_start",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.ArrayStore({
								fields:["date","display"],
								data:[<?php for ($i=1;$i<=20;$i++) { if ($i != 1) echo ','; echo '["'.$i.'","매달 '.$i.'일"]'; } ?>]
							}),
							width:80,
							editable:false,
							mode:"local",
							displayField:"display",
							valueField:"date",
							value:"1"
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;부터&nbsp;"
						}),
						new Ext.form.ComboBox({
							name:"auction_end",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.ArrayStore({
								fields:["date","display"],
								data:[<?php for ($i=10;$i<=25;$i++) { if ($i != 10) echo ','; echo '["'.$i.'","매달 '.$i.'일"]'; } ?>]
							}),
							width:80,
							editable:false,
							mode:"local",
							displayField:"display",
							valueField:"date",
							value:"25"
						}),
						new Ext.form.DisplayField({
							value:"&nbsp;까지"
						})
					]
				})
			]
		})
	]
});
</script>