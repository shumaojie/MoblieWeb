//{{{ 通用复制函数
function CopyToClipboard(meintext, cb) {
	if (window.clipboardData) {
		// the IE-manier
		window.clipboardData.setData("Text", meintext);
	} else if (window.netscape) {
		// dit is belangrijk maar staat nergens duidelijk vermeld:
		// you have to sign the code to enable this, or see notes below
		netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
		
		// maak een interface naar het clipboard
		var clip = Components.classes['@mozilla.org/widget/clipboard;1']
			.createInstance(Components.interfaces.nsIClipboard);
		if (!clip) return;
		//alert(clip);
		// maak een transferable
		var trans = Components.classes['@mozilla.org/widget/transferable;1']
			.createInstance(Components.interfaces.nsITransferable);
		if (!trans) return;
		
		// specificeer wat voor soort data we op willen halen; text in dit geval
		trans.addDataFlavor('text/unicode');
		
		// om de data uit de transferable te halen hebben we 2 nieuwe objecten
		// nodig om het in op te slaan
		var str = new Object();
		var len = new Object();
		var str = Components.classes["@mozilla.org/supports-string;1"]
			.createInstance(Components.interfaces.nsISupportsString);
		var copytext = meintext;
		str.data = copytext;
		trans.setTransferData("text/unicode", str, copytext.length * 2);
		var clipid = Components.interfaces.nsIClipboard;
		if (!clip) return false;
		clip.setData(trans, null, clipid.kGlobalClipboard);
	} else {
		return false;
	}
	
	//alert("您已经复制: " + meintext);
	if(typeof cb=='function'){
		return cb(meintext);
	}else{
		return true;
	}
}
//}}}

function showBetInfo(id){
	$.get('/index.php/record/betInfo/'+id, function(data){
		$(data).dialog({
			title:'投注信息',
			width:300,
			buttons:{
				"关闭":function(){
					$(this).dialog("destroy");
				}
			}
		});
	});
}

function wait(){
	$('<img src="/skin/main/images/wait.gif" />').modal({
		modal:true,
		escClose:false,
		overlayCss:{
			background:'#000'
		},
		dataCss:{
			padding:'0px',
			margin:'0px'
		}
	});
}

function destroyWait(){
	$.modal.close();
}

function defaultModalCloase(event, ui){
	$(this).dialog('destroy');
}

function dataAddCode(){
	$('form', this).trigger('submit');
}

//{{{ 开奖相关函数
var T,S,KT,KS;
function gameKanJiangDataC(diffTime, actionNo){
	var $dom=$('#pre-kanjiang');
	var thisNo=$('.kj-title span').html();
	var tips='本期['+thisNo+']已截至投注';
	if(diffTime<=0){
		
		if($('#kaijiang .feipan')) $('#kaijiang .feipan em').html("X");
		$dom.text('00:00:00');
		$('#kaijiang .wjtips').html('正在封单中');
		
		$('#btnPostBet').unbind('click');
		$('#btnPostBet').bind('click', function(){
			winjinAlert(tips,"alert");
			
		});
		var tipString='<span class="ui-wjicon-confirm"></span>当前期结束，是否要清空已投注内容？<br /><br />要清空已投注内容请点击"确定"，不刷新页面请点击"取消"。';
		var wjDialog=$('#wanjinDialog').html(tipString).dialog({
		title:'温馨提示',
		resizable: false,
		width:300,
		minHeight:180,
		modal: true,
		buttons: {
		"确定": function() {
			$( this ).dialog( "close" );
			window.location.reload();
			//gameActionRemoveCode();
			
		},
		"取消": function() {
			$( this ).dialog( "close" );
			}
		
		   }
		});//dialog end
		
		S=false;
		KS=true;
		if(KT) clearTimeout(KT);
		$('.lastGame-tit span.last_issues').text($('.kj-title span').text());
		setKJWaiting(kjTime);
		getQiHao();
		
	}else{
	
	if(actionNo) $dom.prev().find('span').html(actionNo);
	var m=Math.floor(diffTime % 60),
	s=(diffTime---m)/60,
	h=0;
	
	if(s<10){
		s="0"+s;
	}
	
	if(m<10){
		m="0"+m;
	}

	if(s>60){
		h=Math.floor(s/60);
		s=s-h*60;
		$dom.text((h<10?"0"+h:h)+":"+(s<10?"0"+s:s)+":"+m);
	}else{
		h=0;
		$dom.text("00:"+s+":"+m);
	}

	//console.log('m:%s, s:%s', m, s);
	if(S && h==0 && m==6 && s==0){
		playVoice('/skin/sound/stop-time.wav', 'stop-time-voice');
	}
	
	if(h==0 && m==0 && s==0){
		//$('#kaijiang').load($dom.attr('action'));
		loadKjData();
	}else{
		if($.browser.msie){
			T=setTimeout(function(){
				gameKanJiangDataC(diffTime);
			}, 1000);
		}else{
			T=setTimeout(gameKanJiangDataC, 1000, diffTime);
		}
	}
  }
}

function setKJWaiting(kjDiffTime){
	var $dom=$('#kaijiang .kjtips');
	$('#kaijiang #kjsay').show();
	$('.wjkjData ul').hide();
	$('.wjkjData p').show();
	var mm=Math.floor(kjDiffTime % 60),
	ss=(kjDiffTime---mm)/60;
	
	if(ss<10){
		ss="0"+ss;
	}
	
	if(mm<10){
		mm="0"+mm;
	}

	if(ss>60){
		hh=Math.floor(ss/60);
		ss=ss-hh*60;
		$dom.text((hh<10?"0"+hh:hh)+":"+ss+":"+mm);
	}else{
		$dom.text(ss+":"+mm);
	}
	
	if(Math.floor(mm)==0 && Math.floor(ss)==0){
		getQiHao();
		setKjing();
		loadKjData();
		KS=false;
		console.log(kjDiffTime);
	}else{

		if($.browser.msie){
			KT=setTimeout(function(){
				setKJWaiting(kjDiffTime);
			}, 1000);
		}else{
			KT=setTimeout(setKJWaiting, 1000, kjDiffTime);
		}
		
	 }
	
		
}

function getQiHao(){
	$.getJSON('/index.php/index/getQiHao/'+game.type, function(data){
		if(data && data.lastNo && data.thisNo){
			$('#kaijiang .wjtips').html('正在销售中');
			$('#btnPostBet').unbind('click');
			$('#btnPostBet').bind('click',gamePostCode);
			$('.kj-title span').html(data.thisNo.actionNo);
			$('#current_endtime').html(data.validTime);
			$('.lastGame-tit span.last_issues').html(data.lastNo.actionNo);
			//alert(data.lastNo.actionNo+'|'+data.thisNo.actionNo+'|'+data.diffTime);
			S=true;
			if(T) clearTimeout(T);
			kjTime=parseInt(data.kjdTime);
			gameKanJiangDataC(data.diffTime-kjTime, data.thisNo.actionNo);
			loadKjData();
		}
	});
}

var  moveno;
function setKjing(){
	if(!KS){
		$('#kaijiang #kjsay').html('<em class="kjtips">正在开奖中</em>');
		$('#kaijiang #kjsay').show();
		$('.wjkjData p').hide();
		$('.wjkjData ul').show();
	}
	var ctype=$('.kj-hao').attr('ctype');
	var cnum=$('.kj-hao').attr('cnum'),num;
		cnum=parseInt(cnum);

	$(".kj-hao").find("li").attr("flag", "move");
	
		if(ctype=='g1'){
			moveno = window.setInterval(function () {
				$.each($(".kj-hao").find("li"), function (i, n) {
					if ($(this).attr("flag") == "move") {
						num=Math.floor((cnum-1) * Math.random() + 1);
						if(num<10) num='0'+num;
						$(this).html(num)
					}
				})
			}, 40);
		}else if(ctype=='g2'){  //快3
			moveno = window.setInterval(function () {
				$.each($(".kj-hao").find("li"), function (i, n) {
					if ($(this).attr("flag") == "move") {
						$(this).attr("class", "gr_ks gr_ksm" + Math.floor(6 * Math.random() + 1))
					}
				})
			}, 70);
		}else if(ctype=='g3'){ //11选5
			moveno = window.setInterval(function () {
				$.each($(".kj-hao").find("li"), function (i, n) {
					if ($(this).attr("flag") == "move") {
						$(this).attr("class", "gr_s gr_s0" + Math.floor(8 * Math.random() + 1))
					}
				})
			}, 40);
		}else{
			 moveno = window.setInterval(function () {
				$.each($(".kj-hao").find("li"), function (i, n) {
					if ($(this).attr("flag") == "move") {
						$(this).attr("class", "gr_s gr_s" + Math.floor(10 * Math.random()))
					}
				})
			}, 40);
		}
	// }
}

function setKjing1(i,str)
{
	$(".kj-hao li:eq("+i+")").attr("class",str);
}

function loadKjData(){
	var type=$('#kaijiang').attr('type');
	$.ajax('/index.php/index/getLastKjData/'+type,{
		dataType:'json',
		cache:false,
		error:function(){
			setTimeout(loadKjData, 5000);

		},
		success:function(data, textStatus, xhr){
			//console.log(data);
			//alert(data);
			if(!data){
				setKjing();
				setTimeout(loadKjData, 5000);
				
			}else{
				try{
					
					var $dom=$('#kaijiang'),$kjHaoS,$feipan,hao;
					
					if(parseInt(type)==24){ //快8
						$kjHaoS=data.data.split('|');
						hao=$kjHaoS[0].split(',');
						$feipan=$kjHaoS[1];
					}else{
						hao=data.data.split(',');
					}
		
					$('.lastGame-tit span.last_issues').html(data.actionNo);
					var ctype=$('.kj-hao').attr('ctype');
					var times=3000;
						if(ctype=='g1'){
							$('.kj-hao li').each(function(i){
								$(this).html(hao[i]);
							});
							if($dom.find('.feipan')) $dom.find('.feipan').html("快乐飞盘：<em>"+$feipan+"</em>");
						}else if(ctype=='g2'){ //快3
							$('.kj-hao li').each(function(i){
								times-=500;
								setTimeout("setKjing1("+i+",'gr_ks gr_ks"+hao[i]+"')",times);
							});
						}else{
							$('.kj-hao li').each(function(i){
								times-=500;
								setTimeout("setKjing1("+i+",'gr_s gr_s"+hao[i]+"')",times);
							});
						}
					
					//$('.kja-title div:first').trigger('click');
					//$('.jiang.current select').trigger('change');
					reloadMemberInfo();
					gameFreshOrdered();
					getYKTip(game.type,data.actionNo)
					$('#btnPostBet').unbind('click');
					$('#btnPostBet').bind('click',gamePostCode);
					if((typeof $('#wanjinDialog').dialog("isOpen")=='object') || $('#wanjinDialog').dialog('isOpen')){
    					//$('#wanjinDialog').dialog('destroy');
						$('#wanjinDialog').dialog('close');
					}
					
					S=true;
					if(T) clearTimeout(T);
					KS=true;
					if(KT) clearTimeout(KT);
					kjTime=parseInt(data.kjdTime);
					gameKanJiangDataC(data.diffTime-parseInt(kjTime), data.thisNo);
					
					$('#kaijiang #kjsay').hide();
					$('#kaijiang #kjsay').html('开奖倒计时：<em class="kjtips">00:00</em>');
					$('.wjkjData p').hide();
					$('.wjkjData ul').show();
					$(".kj-hao").find("li").attr("flag", "normal");
					if(moveno) clearInterval(moveno);
					playVoice('/skin/sound/kai-jiang.wav', 'kai-jian-voice');
					
				}catch(err){
					//fconsole.log(err);
					setTimeout(loadKjData, 5000);
				}
			}
		}
	});
}


//获取本期盈亏
function getYKTip(type,actionNo){
	if(type && actionNo){
		$.getJSON('/index.php/Tip/getYKTip/'+type+'/'+actionNo, function(tip){
			if(tip){
				
				//if(!tip.flag) return;
				// 只处理正确返回的数据
				$("<div>").append(tip.message).dialog({
						position:['right','bottom'],
						minHeight:40,
						title:'系统提示',
						buttons:''
					});
		  }
		})
	}
}
//{{{ 安全中心相关函数

/**
 * 修改密码前调用
 */
function safeBeforSetPwd(){
	//console.log(this);
	
	if(!this.oldpassword.value){winjinAlert("请输入原密码","alert");return false;}
	if(this.oldpassword.value.length<6){winjinAlert("原密码至少6位","alert");return false;}
	if(!this.newpassword.value){winjinAlert("请输入新密码","alert");return false;}
	if(this.newpassword.value.length<6){winjinAlert("密码至少6位","alert");return false;}
	
	var confirmpwd=$(':password.confirm', this).val();
	if(confirmpwd!=this.newpassword.value){winjinAlert("两次输入密码不一样","alert");return false;}
	
	return true;
}

/**
 * 修改资金密码前调用
 */
function safeBeforSetCoinPwd(){
	
	if(!this.newpassword.value){winjinAlert("请输入新密码","alert");return false;}
	if(this.newpassword.value.length<6){winjinAlert("密码至少6位","alert");return false;}
	
	var confirmpwd=$(':password.confirm', this).val();
	if(confirmpwd!=this.newpassword.value){winjinAlert("两次输入密码不一样","alert");return false;}
	
	return true;
}

function safeBeforSetCoinPwd2(){
	
	if(!this.oldpassword.value){winjinAlert("请输入原密码","alert");return false;}
	if(this.oldpassword.value.length<6){winjinAlert("原密码至少6位","alert");return false;}
	
	if(!this.newpassword.value){winjinAlert("请输入新密码","alert");return false;}
	if(this.newpassword.value.length<6){winjinAlert("密码至少6位","alert");return false;}
	
	var confirmpwd=$(':password.confirm', this).val();
	if(confirmpwd!=this.newpassword.value){winjinAlert("两次输入密码不一样","alert");return false;}
	
	return true;
}

/**
 * 修改密码和资金密码调用
 */
function safeSetPwd(err, data){
	if(err){
		winjinAlert(err,"err");
		
	}else{
		this.reset();
		winjinAlert(data,"ok");
		
	}
}

/**
 * 修改银行信息前调用
 */
function safeBeforSetCBA(){
	//if(!this.cb_type.value) throw('银行类型没有填写');
	if(!this.account.value){winjinAlert("银行帐号没有填写","alert");return false;}
	if(!this.username.value){winjinAlert("银行开户名没有填写","alert");return false;}
		
	//if(!this.safeEmail.value) throw('密保邮箱没有填写');
	//if(!/^\w+\@(\w+\.)?\w{2,4}$/.test(this.safeEmail.value)) throw('密保邮箱格式不正确');
	if(!this.coinPassword.value){winjinAlert("请输入资金密码","alert");return false;}
		
	if(this.coinPassword.value<6){winjinAlert("资金密码至少6位","alert");return false;}
	return true;
}

/**
 * 修改银行信息调用
 */
function safeSetCBA(err, data){
	if(err){
		winjinAlert(err,"err");
		
	}else{
		winjinAlert(data,"ok");
		location.reload();
	}
}

//}}}

//{{{ 团队管理相关函数

function teamCopyTip(text){
	if(text){
		winjinAlert("复制成功","ok");
		
		}
}

/**
 * 新增会员前调用
 */
function teamBeforeAddMember(){
	var type=$('[name=type]:checked',this).val();
	if(!this.username.value){winjinAlert("没有输入用户名","alert");return false;}
	if(!/^\w{4,16}$/.test(this.username.value)){winjinAlert("用户名由4到16位的字母或数字组成","alert");return false;}
	if(!this.password.value){winjinAlert("请输入密码","alert");return false;}
	if(this.password.value.length<6){winjinAlert("密码至少6位","alert");return false;}
	if(document.getElementById('cpasswd').value!=this.password.value){winjinAlert("两次输入密码不一样","alert");return false;}
	if(!this.fanDian.value){winjinAlert("请输入返点","alert");return false;}
	if(parseFloat(this.fanDian.value)<0){winjinAlert("返点不能小于0%","alert"); return false;}
	if(parseFloat(this.fanDian.value)>parseFloat($(this.fanDian).attr('max'))){winjinAlert('返点不能大于'+$(this.fanDian).attr('max'),"alert"); return false;}
	var fanDianDiff= $(this.fanDian).attr('fanDianDiff');
	if((this.fanDian.value*1000) % (fanDianDiff*1000)){winjinAlert('返点只能是'+fanDianDiff+'%的倍数',"alert");return false;}
}

/**
 * 新增会员调用
 */
function teamAddMember(err, data){
	if(err){
		winjinAlert(err,"err");
	}else{
		$('#username').val(this.username.value);
		$('#password').val(this.password.value);
		winjinAlert(data,"ok");
		//this.reset();
		location.reload();
	}
}
function dataAddCode(){
	$('form', this).trigger('submit');
}
function defaultCloseModal(){
	$(this).dialog('destroy');
}

//}}}
//修改会员
function userDataBeforeSubmitCode(){
	
	if(!this.fanDian.value.match(/^[\d\.\%]{1,4}$/)) throw('请正确设置返点');
	if(parseFloat(this.fanDian.value)>=parseFloat($(this.fanDian).attr('max'))) throw('返点不能大于或等于'+$(this.fanDian).attr('max'));
	if(parseFloat(this.fanDian.value)<parseFloat($(this.fanDian).attr('min'))) throw('返点不能小于'+$(this.fanDian).attr('min'));
	if(parseFloat(this.fanDian.value)<parseFloat($(this.fanDian).attr('val'))) throw('返点不能小于'+$(this.fanDian).attr('val'));
	var fanDianDiff= $(this.fanDian).attr('fanDianDiff');
	if((this.fanDian.value*1000) % (fanDianDiff*1000)) throw('返点只能是'+fanDianDiff+'%的倍数');
}

function userDataSubmitCode(err, data){
	if(err){
		
		winjinAlert(err,"err");
	}else{
		winjinAlert("修改成功","ok");
		$(this).parent().dialog('destroy');
		reload();
	}
}

/**
 * 新增注册链接前调用
 */
function teamBeforeAddLink(){
	var type=$('[name=type]:checked',this).val();
	if(!this.fanDian.value) throw('请输入返点');
	if(parseFloat(this.fanDian.value)<0) throw('返点不能小于0%');
	if(parseFloat(this.fanDian.value)>parseFloat($(this.fanDian).attr('max'))) throw('返点不能大于'+$(this.fanDian).attr('max'));
	var fanDianDiff= $(this.fanDian).attr('fanDianDiff');
	if((this.fanDian.value*1000) % (fanDianDiff*1000)) throw('返点只能是'+fanDianDiff+'%的倍数');
}

/**
 * 新增注册链接调用
 */
function teamAddLink(err, data){
	if(err){
		winjinAlert(err,"err");
	}else{
		winjinAlert(data,"ok");
		this.reset();
		window.location='/index.php/team/linkList';
	}
}

//}}}
//修改注册链接
function linkDataBeforeSubmitCode(){
	if(!this.fanDian.value.match(/^[\d\.\%]{1,4}$/)) throw('请正确设置返点');
	if(parseFloat(this.fanDian.value)>parseFloat($(this.fanDian).attr('max'))) throw('返点不能大于或等于'+$(this.fanDian).attr('max'));
	if(parseFloat(this.fanDian.value)<parseFloat($(this.fanDian).attr('min'))) throw('返点不能小于'+$(this.fanDian).attr('min'));
	var fanDianDiff= $(this.fanDian).attr('fanDianDiff');
	if((this.fanDian.value*1000) % (fanDianDiff*1000)) throw('返点只能是'+fanDianDiff+'%的倍数');
}

function linkDataSubmitCode(err, data){
	if(err){
		winjinAlert(err,"err");
	}else{
		winjinAlert("修改成功","ok");
		$(this).parent().dialog('destroy');
		reload();
	}
}
//删除注册链接
function linkDataBeforeSubmitDelete(){
	
}

function linkDataSubmitDelete(err, data){
	if(err){
		winjinAlert(err,"err");
	}else{
		winjinAlert("删除成功","ok");
		$(this).parent().dialog('destroy');
		reload();
	}
}



	
//{{{ 游戏相关函数
/**
 * 快速选择唯一选择
 */
function uniqueSelect(parent){
	var $this=$(this),$unique=parent.closest('.unique'),
	fun=function(i,c){
		return $('input.code.checked[value='+this.value+']').length?'':'checked';
	};
	
	
	if($this.is('.all')){
		// 全－全部选中
		$('input.code',parent).addClass(fun);
	}else if($this.is('.large')){
		// 大－选中5到9
		$('input.code.max',parent).addClass(fun);
		$('input.code.min',parent).removeClass('checked');
	}else if($this.is('.small')){
		// 小－选中0到4
		$('input.code.min',parent).addClass(fun);
		$('input.code.max',parent).removeClass('checked');
	}else if($this.is('.odd')){
		// 单－选中单数
		$('input.code.d',parent).addClass(fun);
		$('input.code.s',parent).removeClass('checked');
	}else if($this.is('.even')){
		// 双－选中双数
		$('input.code.s',parent).addClass(fun);
		$('input.code.d',parent).removeClass('checked');
	}else if($this.is('.none')){
		// 清－全不选
		$('input.code',parent).removeClass('checked');
	}
}

function reload(){
	location.reload();
}

function reloadMemberInfo(){
	$('.userInfo').load('/index.php/index/userInfo');
}

function randomSelectCode(len, codes){
	var i,selectCode=[], codesLen=codes.length;
	for(i=0; i<len; i++){
		selectCode[i]=Math.floor(Math.random()*codesLen);
	}
	
	return selectCode;
}
/**
 * 追号
 * fpcount 是否飞盘 费用翻倍
 */
function setGameZhuiHao(data){
	//console.log(data);
	var fpcount=1,$feipan=$(':checkbox[name=fpEnable]'); 
	if($feipan.prop('checked')) fpcount=2;
	$.get('/index.php/index/zhuiHaoModal', function(html){
		$(html).dialog({
			title:'<select name="qh"><option value="10">最近10期</option><option value="20">最近20期</option><option value="30">最近30期</option><option value="40">最近40期</option><option value="50">最近50期</option><option value="0">今天全部</option></select>　<label><input type="checkbox" checked name="zhuiHaoMode" value="1"/>中奖后停止追号</label>　追号期数：<span class="qs">0</span>　总金额：<span class="amount">0.00</span>元',
			width:300,
			minHeight:350,
			modal:true,
			stack:false,
			dialogClass:'zhui-hao-modal',
			
			buttons:{
				"全选":function(){
					$('thead :checkbox', this).prop('checked', true).trigger('change');
				},
				"反选":function(){
					$('tbody :checkbox', this).each(function(){
						this.checked=!this.checked;
						$(this).trigger('change');
					});
					$('thead :checkbox', this).prop('checked', false);
				},
				
				"确定追号":function(){
					var data=[];
					$('tbody :checkbox:checked', this).each(function(){
						var $this=$(this),
						$tr=$this.closest('tr');
						data.push([$('td:eq(1)', $tr).text(), $('.beishu', $tr).val(), $('td:eq(4)', $tr).text()].join('|'));
					});
					
					if(!data.length){
						alert('追号至少选一期');
						return false;
					}
					
					$('.touzhu-bottom .tz-buytype :checkbox[name=zhuiHao]').data({
						zhuiHao:data.join(';'),
						zhuiHaoMode:$(this).closest('.zhui-hao-modal').find(':checkbox[name=zhuiHaoMode]:first')[0].checked?1:0
					})[0].checked=true;
					$( this ).dialog( "destroy" );
					gameCalcAmount();
				},
				"取消追号":function(){
					$('.touzhu-bottom .tz-buytype :checkbox[name=zhuiHao]').removeData()[0].checked=false;
					$( this ).dialog( "destroy" );
					gameCalcAmount();
				}
			},
			
			open:function(event, ui){
				//console.log(this);
				var $this=$(this),
				price=Math.round(data.mode * data.actionNum  * fpcount * 100)/100;
				$this.attr('rel', price);
				$this.attr('src', '/index.php/index/zhuiHaoQs/'+data.type+'/'+price+'/');
				//console.log($this.attr('src')+10);
				$('.tr-cont', this).load($this.attr('src')+10);
				
				$this.closest('.zhui-hao-modal').find('select:first').change(function(){
					$('tbody', $this).load($this.attr('src')+this.value);
				});
			}
		});
	});
	

}

function doZhuiHaoCount(){
	var count=0, amount=0;
	$('tbody tr :checkbox', this).each(function(i, v){
		
	});
}

/**
 * 查看投注号码
 */
function displayCodeList(opts){
	$('<div>').append(
		$('<textarea class="code-tip-box"></textarea>')
		.append(opts.actionData)
	).dialog({title:'投注号码'});
}

function gameMsgAutoTip(){
	var obj,$game=$('#num-select .pp'),
	calcFun=$game.attr('action');
	
	if(calcFun && (calcFun=window[calcFun]) && (typeof calcFun=='function')){
		try{
			obj=calcFun.call($game);
			if($.isArray(obj)){
				o={actionNum:0};
				obj.forEach(function(v,i){
					o.actionNum+=v.actionNum;
				});
				obj=o;
			}
			//console.log(obj);
			//var str='共'+obj.actionNum+'注，金额'+(gameGetMode()*gameGetBeiShu()*obj.actionNum).round(2)+'元';
			$('#game-tip-dom').text('共'+obj.actionNum+'注，金额'+(gameGetMode()*gameGetBeiShu()*obj.actionNum).round(2)+'元');
		}catch(err){
			//console.log(err);
			$('#game-tip-dom').text(err);
		}
	}
}

/**
 * 设置cookie
 */

$(function(){
	
	$(':radio[name=danwei]').live("click",function(){
		var value=$(this).val();
		if($(this).attr("checked")){
			$.cookie('mode', value, { expires: 7, path: '/'});
		}
	})	
	$('#slider').live("mouseover",function(){
		$.cookie('fanDian', gameGetFanDian(), { expires: 7, path: '/'});
		//alert(gameGetFanDian());
	})	
	
})



/**
 * 清除号码
 *
 * @params bool isSelected	是否只清除选中的项，默认false
 */
function gameActionRemoveCode(isSelected){
	$('.touzhu-cont tr').remove();
	$('.touzhu-bottom :checkbox[name=zhuiHao]').removeData()[0].checked=false;
	gameCalcAmount();
}

/**
 * 添加预投注
 * code {actionNo:'12,3,4,567,8', actionNum:6}
 */
function gameAddCode(code){
	wait();
	var actionNo=$.parseJSON($.ajax('/index.php/game/checkBuy',{async:false}).responseText);
	destroyWait();
	
	if(actionNo){
		alert('本期投注已截止，请下一期再投注');
		return false;
	}
	if($.isArray(code)){
		for(var i=0; i<code.length; i++) gameAddCode(code[i]);
		return;
	}
	
	if(code.actionNum==0) throw('号码不正确');
	try{
		code=$.extend({
			
			// 反点
			fanDian: gameGetFanDian(),
			bonusProp:gameGetPl(code),
			// 模式
			mode: gameGetMode(),
			
			// 倍数
			beiShu: gameGetBeiShu(),

			// 预定单ID
			orderId: (new Date())-2147483647*623
		}, code);
		
		var weiShu=0, wei='',
		modeName={'2':'元', '0.2':'角', '0.02':'分'},
		amount=code.mode * code.beiShu * code.actionNum,
		$wei=$('#wei-shu'),
		playedName=code.playedName||$('.game-cont .current').text(),
		weiCount=parseInt($wei.attr('length'));
		
		if(code.playedName) delete code.playedName;
		delete code.isZ6;
		
		//console.log(code.mode);
		
		if($wei.length){
			if($(':checked', $wei).length!=weiCount) throw('请选择'+weiCount+'位数！');
			$(':checked', $wei).each(function(){
				weiShu|=parseInt(this.value);
			});
		}
		code.weiShu=weiShu;
		
		if(weiShu){
			var w={16:'万', 8:'千', 4:'百', 2:'十',1:'个'}
			for(var p in w){
				if(weiShu & p) wei+=w[p];
			}
			wei+=':';
		}
		//console.log(weiShu);
		
		$('#num-select input:hidden').each(function(){
			code[$(this).attr('name')]=this.value;
		});

		delete code.undefined;
		$('<tr>').data('code', code)
		.append(
			// 玩法
			$('<td>').append(playedName)
		)
		.append(
			// 号码列表
			$('<td class="code-list">').append(wei+(code.actionData.length>18?(code.actionData.substr(0,5)+'...'):code.actionData))
		)
		.append(
			// 注数
			$('<td>').data('value', code.actionNum).append('['+code.actionNum+'注]')
		)
		.append(
			// 总金额
			$('<td>').data('value', amount).append(amount.round(2)+"元")
		)
		.append(
			// 倍数
			$('<td>').append(code.beiShu+'倍')
		)
		.append(
			// 单价
			$('<td>').append(modeName[code.mode])
		)
		.append(
			// 奖－返
			$('<td>').append('奖－返：'+parseFloat(code.bonusProp).round(2)+'-'+parseFloat(code.fanDian).round(1)+'%')
		)
		.append(
			// 操作
			$('<td><div class="del"></div></td>')
		)
		.appendTo('.touzhu-cont table');
		
		$('#textarea-code').val("");
		gameCalcAmount();
		
		$('.tz-buytype :checkbox[name=zhuiHao]').removeData()[0].checked=false;
		
		$('.num-table :button.checked').removeClass('checked');
	}catch(err){
		alert(err);
	}
}

/**
 * 添加投注
 */
function gamePostCode(){
	var code=[],	// 存放投注号特有信息
	zhuiHao,		// 存放追号信息
	data={};		// 存放共同信息

	$('.touzhu-cont tr').each(function(){
		code.push($(this).data('code'));
	});
	
	if(code==""){
		alert('您还未添加预投注');
		return false;
	}

	$('.touzhu-bottom :checkbox:checked').each(function(){
		data[$(this).attr('name')]=this.value;
	});
	
	if($(':checkbox[name=zhuiHao]')[0].checked){
		var $dom=$(':checkbox[name=zhuiHao]');
		zhuiHao=$dom.data('zhuiHao');
		data.zhuiHao=1;
		data.zhuiHaoMode=$dom.data('zhuiHaoMode');
	}

	wait();
	var actionNo=$.parseJSON($.ajax('/index.php/game/getNo/'+game.type,{async:false}).responseText);
	destroyWait();
	if(!actionNo){
		alert('获取投注期号出错');
		return false;
	}
	
	var tipString='<span class="ui-wjicon-confirm"></span>确定要购买第<b>'+actionNo['actionNo']+'</b>期彩票？';
	tipString+='<br /><table width="100%"><tr><th>玩法</th><th>号码</th><th>注数</th><th>倍数</th><th>模式</th></tr>';
	$('.touzhu-cont tr').each(function(){
		var $this=$(this);
		tipString+="<tr><td>"+$('td:eq(0)', $this).text()+"</td><td class='code-list'>"+$('td:eq(1)', $this).text()+"</td><td>"+$('td:eq(2)', $this).data('value')+"</td><td>"+$('td:eq(4)', $this).text()+"</td><td>"+$('td:eq(5)', $this).text()+"</td></tr>";
	});
	tipString+='</table>';
	tipString+='<br />'+$('.tz-tongji').html();
	
	$('#wanjinDialog').html(tipString).dialog({
		title:'投注提示',
		resizable: false,
		width:300,
		minHeight:220,
		modal: true,
		buttons: {
		"确定购买": function() {
			$( this ).dialog( "close" );
			data['type']=game.type;
			data['actionNo']=actionNo.actionNo;
			data['kjTime']=actionNo.actionTime;
		
			wait();
			$.ajax('/index.php/game/postCode', {
				data:{
					code:code,
					para:data,
					zhuiHao:zhuiHao
				},
				type:'post',
				dataType:'json',
				error:function(xhr, textStatus, errorThrown){
					gamePostedCode(errorThrown||textStatus);
				},
				success:function(data, textStatus, xhr){
					gamePostedCode(null, data);
					if(data) alert(data);
				},
				complete:function(xhr, textStatus){
					// 服务器运行异常
					// 尝试获取服务器抛出
					destroyWait();
					var errorMessage=xhr.getResponseHeader('X-Error-Message');
					if(errorMessage) gamePostedCode(decodeURIComponent(errorMessage));
				}
			});
	},
	"取消购买": function() {
		$( this ).dialog( "close" );
		return false;
	}
	}
	});//dialog end

	
}

/**
 * 投注后置函数
 */
function gamePostedCode(err, data){
	if(err){
		if('您的可用资金不足，是否充值？'==err){
			if(window.confirm(err)) location='/index.php/cash/recharge';
		}else{
			alert(err);
		}
	}else{
		gameActionRemoveCode();
		gameFreshOrdered();
		reloadMemberInfo();
		gameCalcAmount();
		$('#game-tip-dom').text('');
		//reload();
	}
}


/**
 * 计算注数与金额，并显示
 * fpcount 是否飞盘 费用翻倍
 */
function gameCalcAmount(){
	var count=0;fpcount=1;amount=0.0, $zhuiHao=$(':checkbox[name=zhuiHao]'), $feipan=$(':checkbox[name=fpEnable]');
	if($feipan.prop('checked')) fpcount=2;
	if($zhuiHao.prop('checked')){
		var data=$('.touzhu-cont tr').data('code');
		$zhuiHao.data('zhuiHao').split(';').forEach(function(v){
			count+=parseInt(v.split('|')[1]);
		});
		amount=data.mode*data.actionNum*count*fpcount;
	}else{
		$('.touzhu-cont tr').each(function(){
			var $this=$(this);
			count+=$('td:eq(2)', $this).data('value');
			amount+=$('td:eq(3)', $this).data('value')*fpcount;
		});
	}

	$('#all-count').text(count);
	$('#all-amount').text(amount.round(2));

}

/**
 * 添加投注
 */

function gameActionAddCode(){
	//奖金返点限制[如奖金模式在1920以下才能购买分模式(返点大于最大返点-11)]
	var modeName={'2.00':'元','0.20':'角','0.02':'分'},
	$mode=$(':radio:checked[name=danwei]'),
	$slider=$('#slider'),
	fanDian=gameGetFanDian(),
	modeFanDian=$mode.data().maxFanDian,
	userFanDian=$slider.attr('fan-dian'),
	mode=$mode.val();
	
	if(userFanDian-fanDian> modeFanDian){
		var pl=$('#fandian-value').data(),
		_amount=(pl.maxpl-pl.minpl)/$slider.attr('game-fan-dian')*modeFanDian+(pl.minpl-0);
		alert(modeName[mode]+'模式最大奖金只能为'+_amount.toFixed(2));
		return false;
	}
	
	// 单笔中奖限额
	var maxZjAmount=$slider.data().betZjAmount;
		//console.log('限额：%s, 中奖：%s', maxZjAmount, gameGetPl() * mode * ($('#beishu').val()||1));
	if(maxZjAmount){
		if($('#fandian-value').data('pl') * mode/2 * ($('#beishu').val()||1) > maxZjAmount){
			alert('单笔中奖奖金不能超过'+maxZjAmount+'元');
			return false;
		}
	}

	var obj,$game=$('#num-select .pp'),
	calcFun=$game.attr('action');
	if(calcFun && (calcFun=window[calcFun]) && (typeof calcFun=='function')){
		try{
			obj=calcFun.call($game);
			//console.log(obj);
			// 单笔投注注数限额
			var maxBetCount=$slider.data().betCount;
			if(maxBetCount && obj.actionNum>maxBetCount){
				alert('单笔投注注数最大不能超过'+maxBetCount+'注');
				return false;
			}
			
			if(typeof obj!='object'){
				throw('未知出错');
			}else{
				gameAddCode(obj);
				$game.find('input.action').removeClass('on');
			}
		}catch(err){
			alert(err);
		}
	}
}

/**
 * 更新定单列表
 */
function gameFreshOrdered(err, msg){
	if(err){
		alert(err);
	}else{
		$('#order-history').load('/index.php/game/getOrdered/'+game.type, reloadMemberInfo);
	}
}

/**
 * 设置反点
 *
 * @params value		反点值，可以是个浮点数，表示在当前值时的增量
 */

function gameSetFanDian(value){
	var $dom=$("#fandian-value"),
	gameFanDian=parseFloat($('#slider').attr('game-fan-dian')),
	myFanDian=parseFloat($('#slider').attr('fan-dian')),
	pl=parseFloat($dom.data('maxpl')),
	minPl=parseFloat($dom.data('minpl')),
	str=(pl-minPl)/gameFanDian*myFanDian+minPl-(pl-minPl)*value/gameFanDian;

	str=str.round(2);
	
	if(pl==minPl){
		$('.fandian-box').hide();
		//value=0;
	}else{
		$('.fandian-box').show();
	}
	
	$('#slider').slider('option', 'value', value);
	
	$dom.data('pl', str);
	str+='/'+value.round(1)+'%';

	$dom.text(str);
}

/**
 * 设置赔率
 */
var FANDIAN=0;
function gameSetPl(value, flag){
	var $dom=$('#slider');
	$('#fandian-value').data('maxpl', value.bonusProp);
	$('#fandian-value').data('minpl', value.bonusPropBase);
	var $slider=$('#slider').closest('.fandian-box');
	if(flag){
		$('.fandian-k').css('visibility','hidden');
	}else{
		$('.fandian-k').css('visibility','visible');
	}
		FANDIAN=FANDIAN||gameGetFanDian();
		gameSetFanDian(FANDIAN);
}

/**
 * 读取反点值
 */
function gameGetFanDian(){
	return $('#slider').slider('option', 'value');
}

/**
 * 读取陪率
 */
function gameGetPl(code){
	var $dom=$('#num-select .pp');
	if($dom.is('[action=tzSscHhzxInput]')){
		//gameSetPl
		if(code.isZ6){
			var set={
				bonusProp:parseFloat($dom.attr('z6max')),
				bonusPropBase:parseFloat($dom.attr('z6min'))
			};
		}else{
			var set={
				bonusProp:parseFloat($dom.attr('z3max')),
				bonusPropBase:parseFloat($dom.attr('z3min'))
			};
		}
		//console.log(set);
		gameSetPl(set, true);
	}
	
	return $('#fandian-value').data('pl');
}

// 读取模式
function gameGetMode(){
	return parseFloat($('#game-dom :radio[name=danwei]:checked').val()||1);
}

// 读取倍数
function gameGetBeiShu(){
	var txt=$('#beishu').val();
	if(!txt) return 1;
	var re=/^[1-9][0-9]*$/;
	if(!re.test(txt)){
		throw('倍数只能为大于1正整数');
		$('#beishu').val(1);
	}
	
	if(isNaN(txt=parseInt(txt))) throw('倍数设置不正确');
	return txt;
}

//}}}

//{{{ 相关算法集
/**
 * 笛卡尔乘积算法
 *
 * @params 一个可变参数，原则上每个都是数组，但如果数组只有一个值是直接用这个值
 *
 * useage:
 * console.log(DescartesAlgorithm(2, [4,5], [6,0],[7,8,9]));
 */
function DescartesAlgorithm(){
	var i,j,a=[],b=[],c=[];
	if(arguments.length==1){
		if(!$.isArray(arguments[0])){
			return [arguments[0]];
		}else{
			return arguments[0];
		}
	}
	
	if(arguments.length>2){
		for(i=0;i<arguments.length-1;i++) a[i]=arguments[i];
		b=arguments[i];
		
		return arguments.callee(arguments.callee.apply(null, a), b);
	}

	if($.isArray(arguments[0])){
		a=arguments[0];
	}else{
		a=[arguments[0]];
	}
	if($.isArray(arguments[1])){
		b=arguments[1];
	}else{
		b=[arguments[1]];
	}

	for(i=0; i<a.length; i++){
		for(j=0; j<b.length; j++){
			if($.isArray(a[i])){
				c.push(a[i].concat(b[j]));
			}else{
				c.push([a[i],b[j]]);
			}
		}
	}
	
	return c;
}

/**
 * 组合算法
 *
 * @params Array arr		备选数组
 * @params Int num
 *
 * @return Array			组合
 *
 * useage:  combine([1,2,3,4,5,6,7,8,9], 3);
 */
function combine(arr, num) {
	var r = [];
	(function f(t, a, n) {
		if (n == 0) return r.push(t);
		for (var i = 0, l = a.length; i <= l - n; i++) {
			f(t.concat(a[i]), a.slice(i + 1), n - 1);
		}
	})([], arr, num);
	return r;
}

/**
 * 排列算法
 */
function permutation(arr, num){
	var r=[];
	(function f(t,a,n){
		if (n==0) return r.push(t);
		for (var i=0,l=a.length; i<l; i++){
			f(t.concat(a[i]), a.slice(0,i).concat(a.slice(i+1)), n-1);
		}
	})([],arr,num);
	return r;
}

//}}}

//{{{ 抢庄玩法相关函数
function gameLoadZnzPage(type){
	$('.game-left.img-bj').load('/index.php/index/znz/'+type);
}

//}}}

//{{{ 计算注数算法集
	
/**
 * 全选号码
 *
 * @params			没有参数，函数的this指向$('#num-select')
 * @return			要求返回一个对象{actionData:"1,23,4,5,6",actionNum:2}
 * @throw			遇到不正常时请抛出，系统会自动处理
 */
function tzAllSelect(){
	var code=[], len=1,codeLen=parseInt(this.attr('length')), delimiter=this.attr('delimiter')||"";
	
	
	//console.log(this.has('.checked'));
	if(this.has('.checked').length!=codeLen) throw('请选'+codeLen+'位数字');
	
	this.each(function(i){
		//console.log(i);
		var $code=$('input.code.checked', this);
		if($code.length==0){
			//throw('选择号码不正确');
			code[i]='-';
		}else{
			len*=$code.length;
			code[i]=[];
			$code.each(function(){
				code[i].push(this.value);
			});
			code[i]=code[i].join(delimiter);
			
		}
	});
	
	return {actionData:code.join(','), actionNum:len};
}

/**
 * 排列组选2  除去对子和豹子
 */
function tzDesAlgorSelect(){
	var code=[], len=1,codeLen=parseInt(this.attr('length')), delimiter=this.attr('delimiter')||"";
	
	
	//console.log(this.has('.checked'));
	if(this.has('.checked').length!=codeLen) throw('请选'+codeLen+'位数字');
	
	this.each(function(i){
		//console.log(i);
		var $code=$('input.code.checked', this);
		if($code.length==0){
			//throw('选择号码不正确');
			code[i]='-';
		}else{
			//len*=$code.length;
			code[i]=[];
			$code.each(function(){
				code[i].push(this.value);
			});
			code[i]=code[i].join(delimiter);
	
		}
	});
	code=code.join(',');
	
	// 笛卡尔乘取得所投的号码
	len=DescartesAlgorithm.apply(null, code.split(",").map(function(v){return v.split(delimiter)}))
	
	// 把号码由数组变成字符串，以便比较
	.map(function(v){ return v.join(','); })
	
	// 过滤掉对子和豹子的号码
	.filter(function(v){ return (!isRepeat(v.split(","))) })  //v.match(/^(\\d)\\1{"+(codeLen-1)+"}/)
	
	// 返回中奖号码的总数
	.length;
	
	return {actionData:code, actionNum:len};
	
}

//是否有重复值
  function isRepeat(arr){  
      
         var hash = {};  
      
         for(var i in arr) {  
      
             if(hash[arr[i]])  
      
                  return true;  
      
             hash[arr[i]] = true;  
      
         }  
      
         return false;  
      
    }  

/**
 * 大小单双选号
 *
 * @params			没有参数，函数的this指向$('#num-select')
 * @return			要求返回一个对象{actionData:"1,23,4,5,6",actionNum:2}
 * @throw			遇到不正常时请抛出，系统会自动处理
 */
function tzDXDS(){
	var code=[], len=1,codeLen=2;
	
	
	//console.log(this.has('.checked'));
	if(this.has('.checked').length!=codeLen) throw('请选'+codeLen+'位数字');
	
	this.each(function(i){
		//console.log(i);
		var $code=$('input.code.checked', this);
		if($code.length==0){
			//throw('选择号码不正确');
			code[i]='-';
		}else{
			len*=$code.length;
			code[i]=[];
			$code.each(function(){
				code[i].push(this.value);
			});
			code[i]=code[i].join("");
			
		}
	});
	
	return {actionData:code.join(','), actionNum:len};
}

/**
 * 五星定位胆选号
 *
 * @params			没有参数，函数的this指向$('#num-select')
 * @return			要求返回一个对象{actionData:"1,23,4,5,6",actionNum:2}
 * @throw			遇到不正常时请抛出，系统会自动处理
 */
function tz5xDwei(){
	var code=[], len=0, delimiter=this.attr('delimiter')||"";
	
	
	//console.log(this.has('.checked'));
	//if(this.has('.checked').length!=codeLen) throw('请选'+codeLen+'位数字');
	
	this.each(function(i){
		//console.log(i);
		var $code=$('input.code.checked', this);
		if($code.length==0){
			//throw('选择号码不正确');
			code[i]='-';
		}else{
			len+=$code.length;
			code[i]=[];
			$code.each(function(){
				code[i].push(this.value);
			});
			code[i]=code[i].join(delimiter);
			
		}
	});
	
	if(!len) throw('至少选一个号码');
	
	return {actionData:code.join(','), actionNum:len};
}

/**
 * 不定胆选号
 *
 * @params			没有参数，函数的this指向$('#num-select')
 * @return			要求返回一个对象{actionData:"1,23,4,5,6",actionNum:2}
 * @throw			遇到不正常时请抛出，系统会自动处理
 */
function tz5xBDwei(){
	var code="", len=0, $code=$('input.code.checked', this);
	len=$code.length;
	if(!len) throw('至少选一个号码');
	
	$code.each(function(){
		code+=this.value;
	});
	//console.log(code);
	return {actionData:code, actionNum:len};
}

/**
 * 时时彩录入式投注
 * 这种方式投注时可共享DOM和length属性
 */
function tzSscInput(){
	var codeLen=parseInt(this.attr('length')),
	codes=[],
	str=$('#textarea-code',this).val().replace(/[^\d]/g,'');
	
	if(str.length && str.length % codeLen == 0){
		if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符。');
		codes=codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
	}else{
		throw('输入号码不正确');
	}
	
	codes=codes.map(function(code){
		return code.split("").join(',')
	});
	
	return {actionData:codes.join('|'), actionNum:codes.length}
}

/**
 * 11选5录入式投注
 * 这种方式投注时可共享DOM和length属性
 */
function tz11x5Input(){
	var codeLen=parseInt(this.attr('length'))*2,
	codes=[],
	ncode,
	str=$('#textarea-code',this).val().replace(/[^\d]/g,'');
	
	if(str.length && str.length % codeLen == 0){
		if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符。');
		codes=codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
	}else{
		throw('输入号码不正确');
	}
	
	
	codes=codes.map(function(code){
		code=code.split("");
		ncode="";
		code.forEach(function(v,i){
			if(i % 2==0 && ncode){	
				 ncode+=","+v;
			}else{ 
				 ncode+=v;
			}
		});
		
		return ncode;
	});
	
	return {actionData:codes.join('|'), actionNum:codes.length}
}

/**
 * 时时彩录入式组选投注
 * 这种方式投注时可共享DOM和length属性
 */
function tzSscZuInput(){
	var codeLen=parseInt(this.attr('length')),
	codes=[];
	$('#textarea-code',this).val().split(/[\r\n]/).forEach(function(str){
		if(str.length && str.length % codeLen == 0){
			if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符。');
			codes=codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
		}else{
			throw('输入号码不正确');
		}
	});
	
	codes.forEach(function(code){
		if((new RegExp("^(\\d)\\1{"+(codeLen-1)+"}$")).test(code)) throw('组选不能为豹子');
	});
	
	codes=codes.map(function(code){
		return code.split("").join(',')
	});

	return {actionData:codes.join('|'), actionNum:codes.length}
}

/**
 * 时时彩录入式选位数投注
 * 这种方式投注时可共享DOM和length属性
 */
function tzSscWeiInput(){
	var codeLen=parseInt(this.attr('length')),
	codes=[],weiShu=[],
	str=$('#textarea-code',this).val().replace(/[^\d]/g,'');
	
	if($('#wei-shu :checked',this).length!=codeLen) throw('请选'+codeLen+'位数');
	$('#wei-shu :checkbox',this).each(function(i){
		if(!this.checked) weiShu.push(i);
	});

	if(str.length && str.length % codeLen == 0){
		if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符。');
		codes=codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
	}else{
		throw('输入号码不正确');
	}
	
	
	codes=codes.map(function(code){
		code=code.split("");
		
		weiShu.forEach(function(v,i){
			code.splice(v, 0, '-');
		});
		
		return code.join(',');
	});

	return {actionData:codes.join('|'), actionNum:codes.length}
}

/**
 * 11选5录入式选位数投注
 * 这种方式投注时可共享DOM和length属性
 */
function tz11x5WeiInput(){
	var codeLen=parseInt(this.attr('length')),
	codes=[],weiShu=[],ncode,
	str=$('#textarea-code',this).val().replace(/[^\d]/g,'');
	
	if($('#wei-shu :checked',this).length!=codeLen) throw('请选'+codeLen+'位数');
	$('#wei-shu :checkbox',this).each(function(i){
		if(!this.checked) weiShu.push(i);
	});
	codeLen*=2;
	if(str.length && str.length % codeLen == 0){
		if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符。');
		codes=codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
	}else{
		throw('输入号码不正确');
	}
	
		codes=codes.map(function(code){
		code=code.split("");
		ncode="";
		code.forEach(function(v,i){
			if(i % 2==0 && ncode){	
				 ncode+=","+v;
			}else{ 
				 ncode+=v;
			}
		});
		
		ncode=ncode.split(",");
		weiShu.forEach(function(v,i){
			ncode.splice(v, 0, '-');
		});
		
		return ncode;
	});
		

	return {actionData:codes.join('|'), actionNum:codes.length}
}

/**
 * 时时彩录入式组选位数投注
 * 这种方式投注时可共享DOM和length属性
 */
function tzSscZuWeiInput(){
	var codeLen=parseInt(this.attr('length')),
	codes=[],weiShu=[],
	str=$('#textarea-code',this).val().replace(/[^\d]/g,'');
	
	if($('#wei-shu :checked',this).length!=codeLen) throw('请选'+codeLen+'位数');
	$('#wei-shu :checkbox',this).each(function(i){
		if(!this.checked) weiShu.push(i);
	});
	

	if(str.length && str.length % codeLen == 0){
		if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符。');
		codes=codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
	}else{
		throw('输入号码不正确');
	}
	
	
	codes.forEach(function(code){
		if((new RegExp("^(\\d)\\1{"+(codeLen-1)+"}$")).test(code)) throw('组选不能为豹子');
	});
	
	codes=codes.map(function(code){
		code=code.split("");
		
		weiShu.forEach(function(v,i){
			code.splice(v, 0, '-');
		});
		
		return code.join(',');
	});

	return {actionData:codes.join('|'), actionNum:codes.length};
}


/**
 * 组合组选
 */
function tzCombineSelect(){
	var codeLen=parseInt(this.attr('length')),
	codes='', $select=$('.checked'),len;
	
	if($select.length<codeLen) throw('请选'+codeLen+'位数');
	
	$select.each(function(){
		codes+=this.value;
	});
	
	len=combine(codes.split(""), codeLen).length;
	
	return {actionData:codes, actionNum:len};
}

/**
 * 排列组选
 */
function tzPermutationSelect(){
	var codeLen=parseInt(this.attr('length')),
	codes='', $select=$('.checked'),len;
	
	if($select.length<codeLen) throw('请选'+codeLen+'位数');
	
	$select.each(function(){
		codes+=this.value;
	});
	
	len=permutation(codes.split(""), codeLen).length;
	
	return {actionData:codes, actionNum:len};
}


/**
 * 混合组选录入式投注
 */
function tzSscHhzxInput(){
	var codeList=$('#textarea-code').val(),		// 输入号码列表
	played=this.attr('played'),					// 玩法：前、后、任选
	z3=[],			// 分解出来的组三列表
	z6=[];			// 分解出来的组六列表
	
	var o={"前":[16,17],"后":[19,20],"任选":[22,23]};
	
	if(played=='任选' && $('#wei-shu :checked',this).length!=3) throw('请选3位数');
	
	
	codeList=codeList.replace(/[^\d]/gm,'');
	if(codeList.length==0) throw('请输入号码');
	if(codeList.length % 3) throw('输入号码不正确');
	
	codeList.replace(/[^\d]/gm,'').match(/\d{3}/g).forEach(function(code){
		var reg=/(\d)(.*)\1/;
		if(/(\d)\1{2}/.test(code)){
			throw('组选不能为豹子');
		}else if(reg.test(code)){
			// 组三
			//z3.push(code.replace(reg,'$1$2'));
			z3.push(code);
		}else{
			// 组六
			z6.push(code);
		}
	});
	
	if(z3.length && z6.length){
		return [{playedId:o[played][0], playedName:played+'三组三', actionData:z3.join(','), actionNum:z3.length, isZ6:false},
				{playedId:o[played][1], playedName:played+'三组六', actionData:z6.join(','), actionNum:z6.length, isZ6:true}];
	}else if(z3.length){
		return {playedId:o[played][0], playedName:played+'三组三', actionData:z3.join(','), actionNum:z3.length, isZ6:false};
	}else if(z6.length){
		return {playedId:o[played][1], playedName:played+'三组六', actionData:z6.join(','), actionNum:z6.length, isZ6:true};
	}
}

/**
 * 十一选五任选玩法投注
 */
function tz11x5Select(){
	var code=[], len=1,codeLen=parseInt(this.attr('length')),sType=!!$('.dantuo :radio:checked').val();
	//console.log(this);
	
	if(sType){
		// 胆拖方式
		var $d=$(this).filter(':visible:first'),
		$t=$d.next(),
		dLen=$('.code.checked', $d).length;
		
		if(dLen==0){
			throw('至少选一位胆码');
		}else if(dLen>=codeLen){
			throw('最多只能选择'+(codeLen-1)+'个胆码');
		//}else if(dLen==1){
		//	$(':input:visible.code.checked').each(function(i,o){
		//		code[i]=o.value;
		//	});
		//	if(code.length<codeLen) throw('胆码和拖码至少选择'+codeLen+'位数');
		//	
		//	return {actionData:code.join(' '), actionNum:combine(code, codeLen).length};
		}else{
			var dCode=[],tCode=[];
			$('.code.checked', $d).each(function(i,o){
				dCode[i]=o.value;
			});
			
			$('.code.checked', $t).each(function(i,o){
				tCode[i]=o.value;
			});
			
			len=combine(tCode, codeLen-dCode.length).length;
			return {actionData:'('+dCode.join(' ')+')'+tCode.join(' '), actionNum:len};
		}
	}else{
		// 普通方式
		$(':input:visible.code.checked').each(function(i,o){
			//console.log(i);
			code[i]=o.value;
		});
		if(code.length<codeLen) throw('至少选择'+codeLen+'位数');
		
		return {actionData:code.join(' '), actionNum:combine(code, codeLen).length};
	}
}

//}}}

/**
 * 快乐十分任选玩法投注
 */
function tzKLSFSelect(){
	var code=[], len=1,codeLen=parseInt(this.attr('length')),sType=!!$('.dantuo :radio:checked').val();
	//console.log(this);
	
	if(sType){
		// 胆拖方式
		var $d=$(this).filter(':visible:first'),
		$t=$d.next(),
		dLen=$('.code.checked', $d).length;
		
		if(dLen==0){
			throw('至少选一位胆码');
		}else if(dLen>=codeLen){
			throw('最多只能选择'+(codeLen-1)+'个胆码');
		//}else if(dLen==1){
		//	$(':input:visible.code.checked').each(function(i,o){
		//		code[i]=o.value;
		//	});
		//	if(code.length<codeLen) throw('胆码和拖码至少选择'+codeLen+'位数');
		//	
		//	return {actionData:code.join(' '), actionNum:combine(code, codeLen).length};
		}else{
			var dCode=[],tCode=[];
			$('.code.checked', $d).each(function(i,o){
				dCode[i]=o.value;
			});
			
			$('.code.checked', $t).each(function(i,o){
				tCode[i]=o.value;
			});
			
			len=combine(tCode, codeLen-dCode.length).length;
			return {actionData:'('+dCode.join(' ')+')'+tCode.join(' '), actionNum:len};
		}
	}else{
		// 普通方式
		$(':input:visible.code.checked').each(function(i,o){
			//console.log(i);
			code[i]=o.value;
		});
		if(code.length<codeLen) throw('至少选择'+codeLen+'位数');
		
		return {actionData:code.join(' '), actionNum:combine(code, codeLen).length};
	}
}

//}}}

//{{{随机投注函数集

/**
 * 时时彩随机投注函数
 * 
 * @params num		投机投几注，默认为1，可以设置为5，选几位数由HTML属性length得
 * @return			要求返回一个对象{actionData:"1,23,4,5,6",actionNum:2}
 *
 */
/*
function sscRandom(num){
	var i, j, code, codes=[], codeLen=parseInt(this.attr('length'));
	
	for(i=0; i<num; i++){
		
		code=[];
		for(j=0; j<codeLen; j++){
			code.push(Math.floor(Math.random()*10));
		}
		
		codes[i]=code;
	}
	
	return {actionData:codes.join('|'), actionNum:codes.length};
}
*/
//}}}

//随机数 GetRandomNum(1,6)
function GetRandomNum(Min,Max)
{   
	var Range = Max - Min;   
	var Rand = Math.random();   
	return(Min + Math.round(Rand * Range));   
}   

//{{{签到
function indexSign(err, data){
	$('#sign').css('display','none');
	if(err){
		alert(err);
	}else{
		reloadMemberInfo();
		alert(data);
	}
}
//}}}

//万金提示  
function winjinAlert(tips,style,minH){
	
	$( "#wanjinDialog" ).html('<span class="ui-wjicon-'+style+'"></span><b>'+tips+'</b>').dialog({
		title:'温馨提示',
		resizable: false,
		width:300,
		minHeight:(minH?minH:120),
		buttons: {
		"确定": function() {$( this ).dialog( "close" );}
	   }
	});	//dialog end

	
}

