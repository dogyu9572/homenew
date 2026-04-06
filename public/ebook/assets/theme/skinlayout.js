
/** HTML 객체 생성 정보  skin01 **/
var eBookSkinLayout = {};
eBookSkinLayout.objects = [
	
	{ type:"window"		, id:"background"	},
	{ type:"window"		, id:"modal_wrap"	,   children:[		//모달 팝업
		{ type:"window"		, 'class':"bg_modal", title:"팝업배경"  },
		{ type:"window"		, 'class':"btn_closemodal", title:"닫기",  children:[
			{ type:"text"		, text:"닫기"	}
			]},
		]},
	
	// PC
	{ type:"window"		, id:"top_window"			, 'class':"mainobjects", visible:"pc"	, children:[
		{ type:"window"		, id:"top_center"			, 'class':"mainobjects", children:[
			{ type:"logo"			, id:"top_logo"		, src:"logo.png"		, title:"로고"	},

			{ type:"window"		,  'class':"hint--bottom menuitem langicon skin01_menu"	,  'data-text' : 'menu',  children:[
				{ type:"image"		, id:"top_menu_btn"		, 'class':"topobjects "	, src:"menu.svg"	,    title:"메뉴"			, click:"eFnc.wndToggle('.togglewnd')"		},
			]},
			{ type:"window"		,  'class':"hint--bottom menuitem langicon skin01_menu"	,  'data-text' : 'fullscreen',  children:[
				{ type:"image" 		, id:"fullscreen_btn"	, 'class':"topobjects "	, src:"fullscreen.svg"	, title:"전체화면"	, click:"eFnc.fullscreenToggle()&eFnc.wndHide('.togglewnd')"},
			]},

			{ type:"window"		, id:"menu_window"		, 'class':"mainobjects togglewnd"		, children:[
				{ type:"window"		, id:"arrow_up"		, 'class':"mainobjects" },
				
				{ type:"window"		, id:"menu_contents"	, 'class':"mainobjects"		, children:[
					{ type:"window"		, id:"menu_top"	 ,  children:[
						{ type:"home" 		, id:"home_btn"				, 'class':"menuobjects langicon "	,  'data-text'  : 'home', src:"home.svg"	, title:"홈"	},
						{ type:"image" 		, id:"fullscreen_btn"	, 'class':"menuobjects langicon"	,  'data-text'  : 'fullscreen', src:"fullscreen.svg"	, title:"전체화면"	, click:"eFnc.fullscreenToggle()&eFnc.wndHide('.togglewnd')"},
						{ type:"image"		, id:"btn_close"		, 'class':"topobjects langicon"	,  'data-text'  : 'close',  src:"close.svg"			, title:"닫기"			, click:"eFnc.wndToggle('.togglewnd')"		},
						]},
					{ type:"booklist"	, id:"booklist"	, 'class':"mainobjects langicon" , 'data-text'  : 'lastebook', title: "지난호 보기"	},
					{ type:"window"		, id:"search_window"		, 'class':"", children:[
						{ type:"input"		, id:"search_text",		'class':"", onKeypress : "javascript:if(event.keyCode==13) eFnc.searchByInput('#search_text')"	},
						{ type:"image"		, id:"search_text_btn"	, 'class':"menuobjects langicon",  'data-text'  : 'search', src:"search.svg"		, title:"검색"			, click:"eFnc.searchByInput('#search_text')"		},
						]},
					{ type:"window"		, id:"menu_tablelist_btn", 'class' : "menuitem"	, 'data-text' : 'contents' 	, click:"eFnc.wndToggle('#tablelist_window')&eFnc.wndHide('.togglewnd')"	,  children:[
						{ type:"image"		, 'class':"menuobjects", src:"list.svg"	, title:"목차"			},
						]},
					{ type:"window"		,  id:"menu_bookmark_btn"	, 'class' : "menuitem"		,  'data-text'  : 'bookmark' 	, click:"eFnc.wndToggle('#bookmarklist_window')&eFnc.wndHide('.togglewnd')" , children:[
						{ type:"image"		,'class':"menuobjects", src:"bookmark.svg"	, title:"북마크"				},
						]},
					{ type:"window"		, id:"menu_slide_btn"	,'class' : "menuitem"	,  'data-text'  : 'autoslide' 	, click:"eFnc.runSlideShow()&eFnc.wndHide('.togglewnd')" ,  children:[
						{ type:"image"		,  'class':"menuobjects", src:"slide.svg"			, title:"자동넘김"		},
						]},
					{ type:"window"		, id:"menu_print_btn"	, 'class' : "menuitem"		,  'data-text'  : 'print' 	, click:"eFnc.runPrint()",  children:[
						{ type:"image"		, 'class':"menuobjects", src:"print.svg"			, title:"프린트"		},
						]},
					{ type:"window"		,  id:"menu_thumb_btn"	,'class' : "menuitem"	,  'data-text'  : 'thumnail' 	, click:"eFnc.wndToggle('#thumbnail_window')&eFnc.wndHide('.togglewnd')"	,  children:[
						{ type:"image"		, 'class':"menuobjects", src:"thumb.svg"	, title:"썸네일"				},
						]},
					{ type:"window"		, id:"menu_pdf_down"	, 'class' : "menuitem"		,  'data-text'  : 'pdf_down' 	, click:"eFnc.pdfDown()",  children:[
						{ type:"image"		, 'class':"menuobjects", src:"pdf.svg"				, title:"PDF 다운"},
						]},
					{ type:"window"		, id:"menu_memo_btn"	, 'class' : "menuitem"		,  'data-text'  : 'memo' 	, click:"eFnc.runMemo()&eFnc.wndHide('#menu_window')",  children:[
						{ type:"image"		, 'class':"menuobjects", src:"memo.svg"			, title:"메모"	},
						]},
					{ type:"window"		, id:"menu_draw_btn"	, 'class' : "menuitem"		,  'data-text'  : 'drawing' 	, click:"eFnc.runDrawing()&eFnc.wndHide('#menu_window')",  children:[
						{ type:"image"		, 'class':"menuobjects", src:"draw.svg"		, title:"판서"			},
						]},
	
					{ type:"window"		, id:"menu_sns"	, 'class' : "menuitem langicon",   'data-text' : 'sns',  click:"eFnc.wndToggle('#snswrap')", children:[ 
						{ type:"image"		, id:"menu_snsbtn"	,'class' : "menuobjects "	,   src:"sns.svg" , alt:"SNS"  	  },  
					]},
					
						{ type:"window" , id: "snswrap", children:[
							{ type:"window" , id: "snsbox", children:[
								{ type:"window"	, 'class': "tit_sns menuitem ", 'data-text' : 'sns', title:"SNS 공유하기"},
								{ type:"image"		, 'class' : "btn_cls_sns menuitem "	,   src:"close.svg" ,   click:"eFnc.wndToggle('#snswrap')" }, 
								{ type:"window" , 'class' : "linkbox", children:[
									{type : "input", id : "linkInput"  },
									{type: "window" , id:"copyLink", 'class' : "langicon menuitem"	, 'data-text' : 'copyLink', title:"복사"},
								]},
	
								{ type:"window" , 'class' :'left', children:[
									{ type:"window" , 'class' :'sns_icons', children:[
										{ type:"window"		, id:"menu_twitter"	 , 'class' : "langicon"		,  'data-text'  : 'twitter',  click:"eFnc.sendSNS('twitter')" ,  children:[
											{ type:"image" 		, 'class':"menuobjects", src:"twitter.svg"			, title:"트위터"			},
										]},
										{ type:"window"		, id:"menu_facebook"	 ,'class' : "langicon"	, 'data-text'  : 'facebook',   click:"eFnc.sendSNS('facebook')",  children:[
											{ type:"image" 		, 'class':"menuobjects", src:"facebook.svg"		, title:"페이스북"		},
										]},
										{ type:"window"		, id:"menu_kakaotalk"		, 'class' : "langicon"		, 'data-text'  : 'kakao',   click:"eFnc.sendSNS('kakaotalk')",  children:[
											{ type:"image" 		, 'class':"menuobjects", src:"kakaotalk.svg"		, title:"카카오톡"		},
										]},
									]},

								]},
								{ type:"window" , 'class' :'right', children:[
									{ type:"window"	, id:"qrcode", 'class': "hint--bottom qrcode langicon", title:"QR CODE",  'data-text'  : 'qrcode' },		
								]},
							]},
					]},  /*sns end */

					{ type:"window"		,id:"pagenation" , 	  children:[					
							{ type:"image"		, id:"quick_first_btn"	, 'class':"quickobjects langicon"		, 'data-text'  : 'first_page', src:"btn_first.svg"		, title:"첫 페이지"		, click:"eFnc.gotoPage(1)"	},	
							{ type:"image"		, id:"quick_prev_btn"		, 'class':"quickobjects langicon"		,  'data-text'  : 'prev_page',src:"btn_prev.svg"		, title:"이전 페이지"	, click:"eFnc.gotoPrev()"	},
							{ type:"window"		,id:"page_num" , 	  children:[					
								{ type:"pagenum"	, id:"quick_pagenum_area"		},
								{ type:"text"		, id:"quick_pagenum_slash"	, 'class':"quicktexts"	, text:"/"	, tabindex:-1	},
								{ type:"pagetotal", id:"quick_pagetotal_area"	},
							]},
							{ type:"input"		, id:"quick_page",				'class':""	, onKeypress : "javascript:if(event.keyCode==13) eFnc.gotoPageClick('#quick_page')"  },
							{ type:"image"		, id:"quick_next_btn"		, 'class':"quickobjects langicon"		, 'data-text'  : 'next_page', src:"btn_next.svg"		, title:"다음 페이지"	, click:"eFnc.gotoNext()"	},
							{ type:"image"		, id:"quick_last_btn"		, 'class':"quickobjects langicon"		, 'data-text'  : 'last_page', src:"btn_last.svg"		, title:"끝 페이지"		, click:"eFnc.gotoPage(-1)"	},
						]},
					
					
					]},
				]},
			]},
		]},
	//  Mobile 
	{ type:"window"		, id:"top_window_m"			, 'class':"mainobjects", visible:"mobile", children:[
		{ type:"logo"		, id:"top_logo"				, src:"logo.png"					, title:"LOGO"	},
		{ type:"window"		, id:"top_center"				, 'class':"mainobjects", children:[
			{ type:"home" 		, id:"home_btn"				, 'class':"topobjects_m langicon "	,  'data-text'  : 'home', src:"home.svg"	, title:"홈"	},
			{ type:"image"		, id:"search_text_btn_m"		, 'class':"topobjects_m langicon", 'data-text' : 'search', src:"search.svg"		, title:"검색"			, click:"eFnc.wndToggle('#searchlist_window')"		},
			{ type:"image"		, id:"top_menu_btn_m"		, 'class':"topobjects_m langicon", 'data-text' : 'menu', src:"menu.svg"		, title:"메뉴"			, click:"eFnc.wndToggle('#menu_window_m')"		},
			{ type:"window"		, id:"menu_window_m"			, 'class':"mainobjects"		, children:[
				{ type:"window"		, id:"arrow_up_m"			, 'class':"mainobjects" },

				{ type:"window"		, id:"menu_contents_m"			, 'class':"mainobjects"		, children:[

					{ type:"window"		, id:"menu_tablelist_btn", 'class' : "menuitem"	, 'data-text' : 'contents' 	, click:"eFnc.wndToggle('#tablelist_window')&eFnc.wndHide('.togglewnd')"	,  children:[
						{ type:"image"		, 'class':"menuobjects", src:"list.svg"	, title:"목차"			},
						]},
					{ type:"window"		,  id:"menu_bookmark_btn"	, 'class' : "menuitem"		,  'data-text'  : 'bookmark' 	, click:"eFnc.wndToggle('#bookmarklist_window')&eFnc.wndHide('.togglewnd')" , children:[
						{ type:"image"		,'class':"menuobjects", src:"bookmark.svg"	, title:"북마크"				},
						]},
					{ type:"window"		, id:"menu_slide_btn"	,'class' : "menuitem"	,  'data-text'  : 'autoslide' 	, click:"eFnc.runSlideShow()&eFnc.wndHide('.togglewnd')" ,  children:[
						{ type:"image"		,  'class':"menuobjects", src:"slide.svg"			, title:"자동넘김"		},
						]},
					{ type:"window"		,  id:"menu_thumb_btn"	,'class' : "menuitem"	,  'data-text'  : 'thumnail' 	, click:"eFnc.wndToggle('#thumbnail_window')&eFnc.wndHide('.togglewnd')"	,  children:[
						{ type:"image"		, 'class':"menuobjects", src:"thumb.svg"	, title:"썸네일"				},
						]},
					{ type:"window"		, id:"menu_pdf_down"	, 'class' : "menuitem"		,  'data-text'  : 'pdf_down' 	, click:"eFnc.pdfDown()",  children:[
						{ type:"image"		, 'class':"menuobjects", src:"pdf.svg"				, title:"PDF 다운"},
						]},

							{ type:"window"		,  id:"menu_snsbtn", 'class':"menuitem ",  'data-text' : 'sns',  click:"eFnc.wndToggle('#snswrap')", children:[ 
						{ type:"image"	, 'class' : "menuobjects ",  src:"sns.svg" , title:"공유하기"   },  
					]},
					{ type:"window" , id: "snswrap", children:[
						{ type:"window" , id: "snsbox", children:[
							{ type:"window"	, 'class': "tit_sns menuitem ", 'data-text' : 'sns', title:"SNS 공유하기"},
							{ type:"image"		, 'class' : "btn_cls_sns menuitem "	,   src:"close.svg" ,   click:"eFnc.wndToggle('#snswrap')" }, 
							{ type:"window" , 'class' : "linkbox", children:[
								{type : "input", id : "linkInput"  },
								{type: "window" , id:"copyLink", 'class' : "langicon menuitem"	, 'data-text' : 'copyLink', title:"복사"},
							]},
							{ type:"window" , 'class' :'left', children:[
								{ type:"window" , 'class' :'sns_icons', children:[
									{ type:"window"		, id:"menu_twitter"	 , 'class' : "langicon"		,  'data-text'  : 'twitter',  click:"eFnc.sendSNS('twitter')" ,  children:[
										{ type:"image" 		, 'class':"menuobjects", src:"twitter.svg"			, title:"트위터"			},
									]},
									{ type:"window"		, id:"menu_facebook"	 ,'class' : "langicon"	, 'data-text'  : 'facebook',   click:"eFnc.sendSNS('facebook')",  children:[
										{ type:"image" 		, 'class':"menuobjects", src:"facebook.svg"		, title:"페이스북"		},
									]},
									{ type:"window"		, id:"menu_kakaotalk"		, 'class' : "langicon"		, 'data-text'  : 'kakao',   click:"eFnc.sendSNS('kakaotalk')",  children:[
										{ type:"image" 		, 'class':"menuobjects", src:"kakaotalk.svg"		, title:"카카오톡"		},
									]},
								]},
							]},
							{ type:"window" , 'class' :'right', children:[
								{ type:"window"	, id:"qrcode", 'class': "hint--bottom qrcode langicon", title:"QR CODE",  'data-text'  : 'qrcode' },		
							]},
						]},
					]},  /* snswrap pop end */

					]},
				]},
			]},
		]},
	
	{ type:"image"		, id:"page_prev_btn"		, 'class':"mainobjects langicon"		,  'data-text'  : 'prev_page', src:"page_prev.svg", visible:"pc", title:"이전 페이지"	, click:"eFnc.gotoPrev()"		},
	{ type:"image"		, id:"page_next_btn"		, 'class':"mainobjects langicon"		,  'data-text'  : 'next_page', src:"page_next.svg", visible:"pc", title:"다음 페이지"	, click:"eFnc.gotoNext()"		},
	
	{ type:"pageview"	, id:"pageview"	, visible:"pc"			},
	{ type:"pageview"	, id:"pageview_m"	, visible:"mobile"	},

	// mobile bottom
	{ type:"window"		, id:"bottom_window"	, visible:"mobile"	, children:[
					{ type:"window"		,id:"pagenation" , 	  children:[					
					{ type:"image"		, id:"quick_first_btn"	, 'class':"quickobjects langicon"	, 'data-text'  : 'first_page', 	src:"btn_first.svg"		, title:"첫 페이지"		, click:"eFnc.gotoPage(1)"	},					/* 페이지 번호 end  */
					{ type:"image"		, id:"quick_prev_btn"		, 'class':"quickobjects langicon"	, 'data-text'  : 'prev_page', 	 src:"btn_prev.svg"		, title:"이전 페이지"	, click:"eFnc.gotoPrev()"	},
					{ type:"window"		,id:"page_num" , 	  children:[					
						{ type:"pagenum"	, id:"quick_pagenum_area"		},
						{ type:"text"		, id:"quick_pagenum_slash"	, 'class':"quicktexts"	, text:"/"	, tabindex:-1	},
						{ type:"pagetotal", id:"quick_pagetotal_area"	},
					]},
					{ type:"input"		, id:"quick_page",				'class':""	, onKeypress : "javascript:if(event.keyCode==13) eFnc.gotoPageClick('#quick_page')"  },
					{ type:"image"		, id:"quick_next_btn"		, 'class':"quickobjects langicon"	, 'data-text'  : 'next_page', 	 src:"btn_next.svg"		, title:"다음 페이지"	, click:"eFnc.gotoNext()"	},
					{ type:"image"		, id:"quick_last_btn"		, 'class':"quickobjects langicon"	, 'data-text'  : 'last_page', 	 src:"btn_last.svg"		, title:"끝 페이지"		, click:"eFnc.gotoPage(-1)"	},
			]},
	]},
	
	{ type:"tablelist"		, id:"tablelist_window"			,	draggable:true, resizable:true	},
	{ type:"bookmarklist"	, id:"bookmarklist_window"	,	draggable:true, resizable:true	},
	{ type:"searchlist"		, id:"searchlist_window"		,	draggable:true, resizable:true	},
	{ type:"thumblist"		, id:"thumbnail_window"	},
	{ type:"indexTab"		, id:"bookclip_pc"	,	draggable:false, resizable:false	},
	];
eFnc.showLoading();