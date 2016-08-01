<script>
$(document).ready(function () {
    $('ul.nav li').has('div').append("<img class='navarrow' src='/images/common/arrow.png' />");
    $("ul.nav li").click(
     function(){
		  $(this).find("div").slideToggle(100);
          $(this).toggleClass("navtionhover");
		  $(this).siblings("li").removeClass("navtionhover").find(".subnav").hide();
		 } 
    );
});
</script>

<div id="header">
  <div id="page-header">
    <div class="logo"><img src="/images/common/logo.png"></div>
    <div class="userInfo" style="font-size:14px;">
      <?php $this->display('index/inc_user.php') ?>
    </div>
    <div class="navtionbox">
      <ul class="nav">
        <li class="navtionhover"><a href="/" >平台首页</a></li>
        <li ><a href="#" onclick="return false;">选择彩种</a>
          <div class="subnav reset">
            <table>
              <tr>
                <th class="ssc">时时彩：</th>
              </tr>
              <tr>
                <td><a href="/index.php/index/game/1/2" >重庆时时彩</a><a href="/index.php/index/game/3/2" >江西时时彩</a><a href="/index.php/index/game/12/2" >新疆时时彩</a></td>
              </tr>
              <tr>
                <th class="x5">11选5：</th>
              </tr>
              <tr>
                <td><a href="/index.php/index/game/6/10" >广东11选5</a><a href="/index.php/index/game/7/10" >山东11选5</a><a href="/index.php/index/game/16/10" >江西多乐彩</a></td>
              </tr>
              <tr>
                <th class="fc">福彩体彩：</th>
              </tr>
              <tr>
                <td><a href="/index.php/index/game/9/12" >福彩3D</a><a href="/index.php/index/game/10/12" >排列三</a></td>
              </tr>
              <tr>
                <th class="ssc">其它：</th>
              </tr>
              <tr>
                <td><a href="/index.php/index/game/14/59" >五分彩</a><a href="/index.php/index/game/5/59" >分分彩</a><a href="/index.php/index/game/26/59" >两分彩</a><a href="/index.php/index/game/20/26" >北京PK拾</a><a href="/index.php/index/game/18/20" style="display:none;" >重庆幸运农场</a></td>
              </tr>
            </table>
          </div>
        </li>
        <li ><a href="#" onclick="return false;">会员中心</a>
          <div class="subnav"><a href="/index.php/safe/info" ><img alt="" src="/images/icon/icon (29).png" ></img>个人资料</a><a href="/index.php/safe/passwd" ><img alt="" src="/images/icon/icon (7).png" ></img>密码管理</a><a href="/index.php/record/search" ><img alt="" src="/images/icon/icon (10).png" ></img>游戏记录</a><a href="/index.php/report/count" ><img alt="" src="/images/icon/icon (19).png" ></img>盈亏报表</a><a href="/index.php/report/coin" ><img alt="" src="/images/icon/icon (14).png" ></img>帐变记录</a><a href="/index.php/cash/rechargeLog" ><img alt="" src="/images/icon/icon (18).png" ></img>充值记录</a><a href="/index.php/cash/toCashLog" ><img alt="" src="/images/icon/icon (8).png" ></img>提现记录</a></div>
        </li>
        <?php if($this->user['type']){ ?>
        <li><a href="#" onclick="return false;">代理中心</a>
          <div class="subnav"><a href="/index.php/team/memberList" ><img alt="" src="/images/icon/icon (17).png" ></img>会员管理</a><a href="/index.php/team/onlineMember"><img alt="" src="/images/icon/icon (17).png" ></img>在线会员</a><a href="/index.php/team/gameRecord" ><img alt="" src="/images/icon/icon (32).png" ></img>团队记录</a><a href="/index.php/team/report" ><img alt="" src="/images/icon/icon (19).png" ></img>团队盈亏</a><a href="/index.php/team/coinall" ><img alt="" src="/images/icon/icon (3).png" ></img>团队统计</a><a href="/index.php/team/coin" ><img alt="" src="/images/icon/icon (14).png" ></img>团队帐变</a><a href="/index.php/team/cashRecord" ><img alt="" src="/images/icon/icon (1).png" ></img>团队提现</a><a href="/index.php/team/linkList" ><img alt="" src="/images/icon/icon (33).png" ></img>推广链接</a>
            <?php 
				 if($this->user['fanDian'] == '13.0' || $this->user['fanDian'] == '12.9'){
		?>
            <a href="/index.php/team/shareBonus" ><img alt="" src="/images/icon/icon (1).png" ></img>代理分红</a>
            <?php } ?>
          </div>
        </li>
        <?php } ?>
        <li ><a href="/index.php/notice/info" >系统公告</a></li>
        <li ><a href="/index.php/index/kjdata/" >开奖历史</a></li>
      </ul>
      <div class="clear"></div>
    </div>
  </div>
</div>
<script language="javascript" src="http://chat16.live800.com/live800/chatClient/monitor.js?jid=1902666090&companyID=583030&configID=96820&codeType=custom"></script>
