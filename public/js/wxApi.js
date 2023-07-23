!function(e,n){"function"==typeof define&&(define.amd||define.cmd)?define(function(){return n(e)}):n(e,!0)}(this,function(o,e){if(!o.jWeixin){var n,c={config:"preVerifyJSAPI",onMenuShareTimeline:"menu:share:timeline",onMenuShareAppMessage:"menu:share:appmessage",onMenuShareQQ:"menu:share:qq",onMenuShareWeibo:"menu:share:weiboApp",onMenuShareQZone:"menu:share:QZone",previewImage:"imagePreview",getLocation:"geoLocation",openProductSpecificView:"openProductViewWithPid",addCard:"batchAddCard",openCard:"batchViewCard",chooseWXPay:"getBrandWCPayRequest",openEnterpriseRedPacket:"getRecevieBizHongBaoRequest",startSearchBeacons:"startMonitoringBeacons",stopSearchBeacons:"stopMonitoringBeacons",onSearchBeacons:"onBeaconsInRange",consumeAndShareCard:"consumedShareCard",openAddress:"editAddress"},a=function(){var e={};for(var n in c)e[c[n]]=n;return e}(),i=o.document,t=i.title,r=navigator.userAgent.toLowerCase(),s=navigator.platform.toLowerCase(),d=!(!s.match("mac")&&!s.match("win")),u=-1!=r.indexOf("wxdebugger"),l=-1!=r.indexOf("micromessenger"),p=-1!=r.indexOf("android"),f=-1!=r.indexOf("iphone")||-1!=r.indexOf("ipad"),m=(n=r.match(/micromessenger\/(\d+\.\d+\.\d+)/)||r.match(/micromessenger\/(\d+\.\d+)/))?n[1]:"",g={initStartTime:L(),initEndTime:0,preVerifyStartTime:0,preVerifyEndTime:0},h={version:1,appId:"",initTime:0,preVerifyTime:0,networkType:"",isPreVerifyOk:1,systemType:f?1:p?2:-1,clientVersion:m,url:encodeURIComponent(location.href)},v={},S={_completes:[]},y={state:0,data:{}};O(function(){g.initEndTime=L()});var I=!1,_=[],w={config:function(e){B("config",v=e);var t=!1!==v.check;O(function(){if(t)M(c.config,{verifyJsApiList:C(v.jsApiList),verifyOpenTagList:C(v.openTagList)},function(){S._complete=function(e){g.preVerifyEndTime=L(),y.state=1,y.data=e},S.success=function(e){h.isPreVerifyOk=0},S.fail=function(e){S._fail?S._fail(e):y.state=-1};var t=S._completes;return t.push(function(){!function(){if(!(d||u||v.debug||m<"6.0.2"||h.systemType<0)){var i=new Image;h.appId=v.appId,h.initTime=g.initEndTime-g.initStartTime,h.preVerifyTime=g.preVerifyEndTime-g.preVerifyStartTime,w.getNetworkType({isInnerInvoke:!0,success:function(e){h.networkType=e.networkType;var n="https://open.weixin.qq.com/sdk/report?v="+h.version+"&o="+h.isPreVerifyOk+"&s="+h.systemType+"&c="+h.clientVersion+"&a="+h.appId+"&n="+h.networkType+"&i="+h.initTime+"&p="+h.preVerifyTime+"&u="+h.url;i.src=n}})}}()}),S.complete=function(e){for(var n=0,i=t.length;n<i;++n)t[n]();S._completes=[]},S}()),g.preVerifyStartTime=L();else{y.state=1;for(var e=S._completes,n=0,i=e.length;n<i;++n)e[n]();S._completes=[]}}),w.invoke||(w.invoke=function(e,n,i){o.WeixinJSBridge&&WeixinJSBridge.invoke(e,x(n),i)},w.on=function(e,n){o.WeixinJSBridge&&WeixinJSBridge.on(e,n)})},ready:function(e){0!=y.state?e():(S._completes.push(e),!l&&v.debug&&e())},error:function(e){m<"6.0.2"||(-1==y.state?e(y.data):S._fail=e)},checkJsApi:function(e){M("checkJsApi",{jsApiList:C(e.jsApiList)},(e._complete=function(e){if(p){var n=e.checkResult;n&&(e.checkResult=JSON.parse(n))}e=function(e){var n=e.checkResult;for(var i in n){var t=a[i];t&&(n[t]=n[i],delete n[i])}return e}(e)},e))},onMenuShareTimeline:function(e){P(c.onMenuShareTimeline,{complete:function(){M("shareTimeline",{title:e.title||t,desc:e.title||t,img_url:e.imgUrl||"",link:e.link||location.href,type:e.type||"link",data_url:e.dataUrl||""},e)}},e)},onMenuShareAppMessage:function(n){P(c.onMenuShareAppMessage,{complete:function(e){"favorite"===e.scene?M("sendAppMessage",{title:n.title||t,desc:n.desc||"",link:n.link||location.href,img_url:n.imgUrl||"",type:n.type||"link",data_url:n.dataUrl||""}):M("sendAppMessage",{title:n.title||t,desc:n.desc||"",link:n.link||location.href,img_url:n.imgUrl||"",type:n.type||"link",data_url:n.dataUrl||""},n)}},n)},onMenuShareQQ:function(e){P(c.onMenuShareQQ,{complete:function(){M("shareQQ",{title:e.title||t,desc:e.desc||"",img_url:e.imgUrl||"",link:e.link||location.href},e)}},e)},onMenuShareWeibo:function(e){P(c.onMenuShareWeibo,{complete:function(){M("shareWeiboApp",{title:e.title||t,desc:e.desc||"",img_url:e.imgUrl||"",link:e.link||location.href},e)}},e)},onMenuShareQZone:function(e){P(c.onMenuShareQZone,{complete:function(){M("shareQZone",{title:e.title||t,desc:e.desc||"",img_url:e.imgUrl||"",link:e.link||location.href},e)}},e)},updateTimelineShareData:function(e){M("updateTimelineShareData",{title:e.title,link:e.link,imgUrl:e.imgUrl},e)},updateAppMessageShareData:function(e){M("updateAppMessageShareData",{title:e.title,desc:e.desc,link:e.link,imgUrl:e.imgUrl},e)},startRecord:function(e){M("startRecord",{},e)},stopRecord:function(e){M("stopRecord",{},e)},onVoiceRecordEnd:function(e){P("onVoiceRecordEnd",e)},playVoice:function(e){M("playVoice",{localId:e.localId},e)},pauseVoice:function(e){M("pauseVoice",{localId:e.localId},e)},stopVoice:function(e){M("stopVoice",{localId:e.localId},e)},onVoicePlayEnd:function(e){P("onVoicePlayEnd",e)},uploadVoice:function(e){M("uploadVoice",{localId:e.localId,isShowProgressTips:0==e.isShowProgressTips?0:1},e)},downloadVoice:function(e){M("downloadVoice",{serverId:e.serverId,isShowProgressTips:0==e.isShowProgressTips?0:1},e)},translateVoice:function(e){M("translateVoice",{localId:e.localId,isShowProgressTips:0==e.isShowProgressTips?0:1},e)},chooseImage:function(e){M("chooseImage",{scene:"1|2",count:e.count||9,sizeType:e.sizeType||["original","compressed"],sourceType:e.sourceType||["album","camera"]},(e._complete=function(e){if(p){var n=e.localIds;try{n&&(e.localIds=JSON.parse(n))}catch(e){}}},e))},getLocation:function(e){},previewImage:function(e){M(c.previewImage,{current:e.current,urls:e.urls},e)},uploadImage:function(e){M("uploadImage",{localId:e.localId,isShowProgressTips:0==e.isShowProgressTips?0:1},e)},downloadImage:function(e){M("downloadImage",{serverId:e.serverId,isShowProgressTips:0==e.isShowProgressTips?0:1},e)},getLocalImgData:function(e){!1===I?(I=!0,M("getLocalImgData",{localId:e.localId},(e._complete=function(e){if(I=!1,0<_.length){var n=_.shift();wx.getLocalImgData(n)}},e))):_.push(e)},getNetworkType:function(e){M("getNetworkType",{},(e._complete=function(e){e=function(e){var n=e.errMsg;e.errMsg="getNetworkType:ok";var i=e.subtype;if(delete e.subtype,i)e.networkType=i;else{var t=n.indexOf(":"),o=n.substring(t+1);switch(o){case"wifi":case"edge":case"wwan":e.networkType=o;break;default:e.errMsg="getNetworkType:fail"}}return e}(e)},e))},openLocation:function(e){M("openLocation",{latitude:e.latitude,longitude:e.longitude,name:e.name||"",address:e.address||"",scale:e.scale||28,infoUrl:e.infoUrl||""},e)},getLocation:function(e){M(c.getLocation,{type:(e=e||{}).type||"wgs84"},(e._complete=function(e){delete e.type},e))},hideOptionMenu:function(e){M("hideOptionMenu",{},e)},showOptionMenu:function(e){M("showOptionMenu",{},e)},closeWindow:function(e){M("closeWindow",{},e=e||{})},hideMenuItems:function(e){M("hideMenuItems",{menuList:e.menuList},e)},showMenuItems:function(e){M("showMenuItems",{menuList:e.menuList},e)},hideAllNonBaseMenuItem:function(e){M("hideAllNonBaseMenuItem",{},e)},showAllNonBaseMenuItem:function(e){M("showAllNonBaseMenuItem",{},e)},scanQRCode:function(e){M("scanQRCode",{needResult:(e=e||{}).needResult||0,scanType:e.scanType||["qrCode","barCode"]},(e._complete=function(e){if(f){var n=e.resultStr;if(n){var i=JSON.parse(n);e.resultStr=i&&i.scan_code&&i.scan_code.scan_result}}},e))},openAddress:function(e){M(c.openAddress,{},(e._complete=function(e){e=function(e){return e.postalCode=e.addressPostalCode,delete e.addressPostalCode,e.provinceName=e.proviceFirstStageName,delete e.proviceFirstStageName,e.cityName=e.addressCitySecondStageName,delete e.addressCitySecondStageName,e.countryName=e.addressCountiesThirdStageName,delete e.addressCountiesThirdStageName,e.detailInfo=e.addressDetailInfo,delete e.addressDetailInfo,e}(e)},e))},openProductSpecificView:function(e){M(c.openProductSpecificView,{pid:e.productId,view_type:e.viewType||0,ext_info:e.extInfo},e)},addCard:function(e){for(var n=e.cardList,i=[],t=0,o=n.length;t<o;++t){var r=n[t],a={card_id:r.cardId,card_ext:r.cardExt};i.push(a)}M(c.addCard,{card_list:i},(e._complete=function(e){var n=e.card_list;if(n){for(var i=0,t=(n=JSON.parse(n)).length;i<t;++i){var o=n[i];o.cardId=o.card_id,o.cardExt=o.card_ext,o.isSuccess=!!o.is_succ,delete o.card_id,delete o.card_ext,delete o.is_succ}e.cardList=n,delete e.card_list}},e))},chooseCard:function(e){M("chooseCard",{app_id:v.appId,location_id:e.shopId||"",sign_type:e.signType||"SHA1",card_id:e.cardId||"",card_type:e.cardType||"",card_sign:e.cardSign,time_stamp:e.timestamp+"",nonce_str:e.nonceStr},(e._complete=function(e){e.cardList=e.choose_card_info,delete e.choose_card_info},e))},openCard:function(e){for(var n=e.cardList,i=[],t=0,o=n.length;t<o;++t){var r=n[t],a={card_id:r.cardId,code:r.code};i.push(a)}M(c.openCard,{card_list:i},e)},consumeAndShareCard:function(e){M(c.consumeAndShareCard,{consumedCardId:e.cardId,consumedCode:e.code},e)},chooseWXPay:function(e){M(c.chooseWXPay,V(e),e)},openEnterpriseRedPacket:function(e){M(c.openEnterpriseRedPacket,V(e),e)},startSearchBeacons:function(e){M(c.startSearchBeacons,{ticket:e.ticket},e)},stopSearchBeacons:function(e){M(c.stopSearchBeacons,{},e)},onSearchBeacons:function(e){P(c.onSearchBeacons,e)},openEnterpriseChat:function(e){M("openEnterpriseChat",{useridlist:e.userIds,chatname:e.groupName},e)},launchMiniProgram:function(e){M("launchMiniProgram",{targetAppId:e.targetAppId,path:function(e){if("string"==typeof e&&0<e.length){var n=e.split("?")[0],i=e.split("?")[1];return n+=".html",void 0!==i?n+"?"+i:n}}(e.path),envVersion:e.envVersion},e)},openBusinessView:function(e){M("openBusinessView",{businessType:e.businessType,queryString:e.queryString||"",envVersion:e.envVersion},(e._complete=function(n){if(p){var e=n.extraData;if(e)try{n.extraData=JSON.parse(e)}catch(e){n.extraData={}}}},e))},miniProgram:{navigateBack:function(e){e=e||{},O(function(){M("invokeMiniProgramAPI",{name:"navigateBack",arg:{delta:e.delta||1}},e)})},navigateTo:function(e){O(function(){M("invokeMiniProgramAPI",{name:"navigateTo",arg:{url:e.url}},e)})},redirectTo:function(e){O(function(){M("invokeMiniProgramAPI",{name:"redirectTo",arg:{url:e.url}},e)})},switchTab:function(e){O(function(){M("invokeMiniProgramAPI",{name:"switchTab",arg:{url:e.url}},e)})},reLaunch:function(e){O(function(){M("invokeMiniProgramAPI",{name:"reLaunch",arg:{url:e.url}},e)})},postMessage:function(e){O(function(){M("invokeMiniProgramAPI",{name:"postMessage",arg:e.data||{}},e)})},getEnv:function(e){O(function(){e({miniprogram:"miniprogram"===o.__wxjs_environment})})}}},T=1,k={};return i.addEventListener("error",function(e){if(!p){var n=e.target,i=n.tagName,t=n.src;if("IMG"==i||"VIDEO"==i||"AUDIO"==i||"SOURCE"==i)if(-1!=t.indexOf("wxlocalresource://")){e.preventDefault(),e.stopPropagation();var o=n["wx-id"];if(o||(o=T++,n["wx-id"]=o),k[o])return;k[o]=!0,wx.ready(function(){wx.getLocalImgData({localId:t,success:function(e){n.src=e.localData}})})}}},!0),i.addEventListener("load",function(e){if(!p){var n=e.target,i=n.tagName;n.src;if("IMG"==i||"VIDEO"==i||"AUDIO"==i||"SOURCE"==i){var t=n["wx-id"];t&&(k[t]=!1)}}},!0),e&&(o.wx=o.jWeixin=w),w}function M(n,e,i){o.WeixinJSBridge?WeixinJSBridge.invoke(n,x(e),function(e){A(n,e,i)}):B(n,i)}function P(n,i,t){o.WeixinJSBridge?WeixinJSBridge.on(n,function(e){t&&t.trigger&&t.trigger(e),A(n,e,i)}):B(n,t||i)}function x(e){return(e=e||{}).appId=v.appId,e.verifyAppId=v.appId,e.verifySignType="sha1",e.verifyTimestamp=v.timestamp+"",e.verifyNonceStr=v.nonceStr,e.verifySignature=v.signature,e}function V(e){return{timeStamp:e.timestamp+"",nonceStr:e.nonceStr,package:e.package,paySign:e.paySign,signType:e.signType||"SHA1"}}function A(e,n,i){"openEnterpriseChat"!=e&&"openBusinessView"!==e||(n.errCode=n.err_code),delete n.err_code,delete n.err_desc,delete n.err_detail;var t=n.errMsg;t||(t=n.err_msg,delete n.err_msg,t=function(e,n){var i=e,t=a[i];t&&(i=t);var o="ok";if(n){var r=n.indexOf(":");"confirm"==(o=n.substring(r+1))&&(o="ok"),"failed"==o&&(o="fail"),-1!=o.indexOf("failed_")&&(o=o.substring(7)),-1!=o.indexOf("fail_")&&(o=o.substring(5)),"access denied"!=(o=(o=o.replace(/_/g," ")).toLowerCase())&&"no permission to execute"!=o||(o="permission denied"),"config"==i&&"function not exist"==o&&(o="ok"),""==o&&(o="fail")}return n=i+":"+o}(e,t),n.errMsg=t),(i=i||{})._complete&&(i._complete(n),delete i._complete),t=n.errMsg||"",v.debug&&!i.isInnerInvoke&&alert(JSON.stringify(n));var o=t.indexOf(":");switch(t.substring(o+1)){case"ok":i.success&&i.success(n);break;case"cancel":i.cancel&&i.cancel(n);break;default:i.fail&&i.fail(n)}i.complete&&i.complete(n)}function C(e){if(e){for(var n=0,i=e.length;n<i;++n){var t=e[n],o=c[t];o&&(e[n]=o)}return e}}function B(e,n){if(!(!v.debug||n&&n.isInnerInvoke)){var i=a[e];i&&(e=i),n&&n._complete&&delete n._complete,console.log('"'+e+'",',n||"")}}function L(){return(new Date).getTime()}function O(e){l&&(o.WeixinJSBridge?e():i.addEventListener&&i.addEventListener("WeixinJSBridgeReady",e,!1))}});

(function (global) {
    global.mapleWx = mapleWx(global.wx);
    var margin = function(o,n){
        for (var p in n){
            if(n.hasOwnProperty(p))
                o[p]=n[p];
        }
        return o;
    };
    function mapleWx(wx) {
        'use strict';
        var mapleApi = new _mapleApi();
        var jsApiList = ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone', 'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice', 'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation', 'getLocation', 'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard'];
        function _mapleApi() {
            var that = this;
            //微信接口初始化
            this.init = function (config, readFn, errorFn) {
                mapleApi.option.config = config;
                mapleApi.option.wx = wx;
                wx.config({
                    debug: config.debug || false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                    appId: config.appId, // 必填，公众号的唯一标识
                    timestamp: config.timestamp, // 必填，生成签名的时间戳
                    nonceStr: config.nonceStr, // 必填，生成签名的随机串
                    signature: config.signature,// 必填，签名，见附录1
                    jsApiList: config.jsApiList || jsApiList // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                });
                wx.ready(function () {
                    readFn && readFn.call(mapleApi);
                });
                wx.error(function (error) {
                    errorFn && errorFn.call(mapleApi, error);
                });
                return mapleApi;
            };

            //隐藏不安全接口
            that.hideNonSafetyMenuItem = function () {
                var list = ['menuItem:copyUrl', 'menuItem:delete', '', 'menuItem:originPage', 'menuItem:openWithQQBrowser', 'menuItem:openWithSafari', 'menuItem:share:email', 'menuItem:share:brand', 'menuItem:delete', 'menuItem:editTag'];
                that.hideMenuItems(list);
            };
            //一键配置所有分享
            that.onMenuShareAll = function(options,successFn,closeFn){
                that.onMenuShareAppMessage(options,function(){
                    successFn && successFn('AppMessage');
                },function(){
                    closeFn && closeFn('AppMessage');
                });
                that.onMenuShareQQ(options,function(){
                    successFn && successFn('QQ');
                },function(){
                    closeFn && closeFn('QQ');
                });
                that.onMenuShareQZone(options,function(){
                    successFn && successFn('QZone');
                },function(){
                    closeFn && closeFn('QZone');
                });
                that.onMenuShareTimeline(options,function(){
                    successFn && successFn('Timeline');
                },function(){
                    closeFn && closeFn('Timeline');
                });
                that.onMenuShareWeibo(options,function(){
                    successFn && successFn('Weibo');
                },function(){
                    closeFn && closeFn('Weibo');
                });
            };
        };
        //拍照或从手机相册中选图接口
        _mapleApi.prototype.chooseImage = function (options, successFn) {
            options || (options = {});
            if (typeof(options) == 'function') {
                successFn = options;
                options = {};
            }
            wx.chooseImage({
                count: options.count || 1, // 默认9
                sizeType: options.sizeType || ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                sourceType: options.sourceType || ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                success: function (res) {
                    var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                    successFn && successFn.call(mapleApi, localIds, res);
                },
                fail:function(err){
                }
            });
        };
        //预览图片接口
        _mapleApi.prototype.previewImage = function (current, urls) {
            wx.previewImage({
                current: current, // 当前显示图片的http链接
                urls: urls || [] // 需要预览的图片http链接列表
            });
        };
        //获取本地图片接口
        _mapleApi.prototype.getLocalImgData = function (localId, successFn) {
            wx.getLocalImgData({
                localId: localId, // 图片的localID
                success: function (res) {
                    var localData = res.localData; // localData是图片的base64数据，可以用img标签显示
                    successFn && successFn.call(mapleApi, localIds, res);
                }
            });
        };
        //上传图片接口
        _mapleApi.prototype.uploadImageOne = function (localId, successFn, isShowProgressTips) {
            wx.uploadImage({
                localId: localId, // 需要上传的图片的本地ID，由chooseImage接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    var serverId = res.serverId; // 返回图片的服务器端ID
                    successFn && successFn.call(mapleApi, serverId, res);
                }
            });
        };
        //上传多张图片接口
        _mapleApi.prototype.uploadImage = function (localIds, successFn, errorFn) {
            // var _this = this,allFn=[];
            // localIds.forEach(function(localId,k){
            //     allFn.push(new Promise(function(resolve){
            //         _this.uploadImageOne(localId,function(serverId){
            //             return resolve(serverId);
            //         })
            //     }));
            // });
            // Promise.all(allFn).then(function(){
            //     var i = arguments.length,serverIdList = new Array(i);
            //     while(i--){serverIdList[i] = arguments[i];}
            //     successFn && successFn.call(mapleApi,serverIdList[0]);
            // }).catch(function(err){
            //     errorFn && errorFn.call(mapleApi,err,localIds);
            // });
            var serverIdList = [], length = localIds.length, _this = this;
            var _upload = function () {
                var localId = localIds[--length];
                if (!localId) return errorFn && errorFn.call(mapleApi, localIds, serverIdList);
                _this.uploadImageOne(localId, function (serverId) {
                    serverIdList.push(serverId);
                    length==0 ? successFn.call(mapleApi, serverIdList) : _upload();
                })
            };
            _upload();


        };
        //下载图片接口
        _mapleApi.prototype.downloadImage = function (serverId, successFn, isShowProgressTips) {
            wx.downloadImage({
                serverId: serverId, // 需要下载的图片的服务器端ID，由uploadImage接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    var localId = res.localId; // 返回图片下载后的本地ID
                    successFn && successFn.call(mapleApi, localId);
                }
            });
        };


        //开始录音接口
        _mapleApi.prototype.startRecord = function () {
            wx.startRecord.call(mapleApi);
        };
        //停止录音接口
        _mapleApi.prototype.stopRecord = function (successFn) {
            wx.stopRecord({
                success: function (res) {
                    var localId = res.localId;
                    successFn && successFn.call(mapleApi, localId, res);
                }
            });
        };
        //监听录音自动停止接口
        _mapleApi.prototype.onVoiceRecordEnd = function (completeFn) {
            wx.onVoiceRecordEnd({
                // 录音时间超过一分钟没有停止的时候会执行 complete 回调
                complete: function (res) {
                    var localId = res.localId;
                    completeFn && completeFn.call(mapleApi, localId, res);
                }
            });
        };
        //播放语音接口
        _mapleApi.prototype.playVoice = function (localId) {
            wx.playVoice({
                localId: localId // 需要播放的音频的本地ID，由stopRecord接口获得
            });
        };
        //暂停播放接口
        _mapleApi.prototype.pauseVoice = function (localId) {
            wx.pauseVoice({
                localId: localId // 需要暂停的音频的本地ID，由stopRecord接口获得
            });
        };
        //停止播放接口
        _mapleApi.prototype.stopVoice = function (localId) {
            wx.stopVoice({
                localId: localId // 需要停止的音频的本地ID，由stopRecord接口获得
            });
        };
        //监听语音播放完毕接口
        _mapleApi.prototype.onVoicePlayEnd = function (successFn) {
            wx.onVoicePlayEnd({
                success: function (res) {
                    var localId = res.localId; // 返回音频的本地ID
                    successFn && successFn.call(mapleApi, localId, res);
                }
            });
        };
        //上传语音接口
        _mapleApi.prototype.uploadVoice = function (localId, successFn, isShowProgressTips) {
            wx.uploadVoice({
                localId: localId, // 需要上传的音频的本地ID，由stopRecord接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    var serverId = res.serverId; // 返回音频的服务器端ID
                    successFn && successFn.call(mapleApi, serverId, res);
                }
            });
        };
        //下载语音接口
        _mapleApi.prototype.downloadVoice = function (serverId, successFn, isShowProgressTips) {
            wx.downloadVoice({
                serverId: serverId, // 需要下载的音频的服务器端ID，由uploadVoice接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    var localId = res.localId; // 返回音频的本地ID
                    successFn && successFn.call(mapleApi, localId, res);
                }
            });
        };
        //识别音频并返回识别结果接口
        _mapleApi.prototype.translateVoice = function (localId, successFn, isShowProgressTips) {
            wx.translateVoice({
                localId: localId, // 需要识别的音频的本地Id，由录音相关接口获得
                isShowProgressTips: isShowProgressTips || 1, // 默认为1，显示进度提示
                success: function (res) {
                    successFn && successFn.call(mapleApi, res.translateResult, res);
                }
            });
        };

        //获取网络状态接口
        _mapleApi.prototype.getNetworkType = function (successFn) {
            wx.getNetworkType({
                success: function (res) {
                    successFn && successFn.call(mapleApi, res.networkType, res);
                }
            });
        };
        //使用微信内置地图查看位置接口
        _mapleApi.prototype.openLocation = function (options) {
            wx.openLocation({
                latitude: options.latitude || 0, // 纬度，浮点数，范围为90 ~ -90
                longitude: options.longitude || 0, // 经度，浮点数，范围为180 ~ -180。
                name: options.name || '', // 位置名
                address: options.address || '', // 地址详情说明
                scale: options.scale || 14, // 地图缩放级别,整形值,范围从1~28。默认为最大
                infoUrl: options.infoUrl || '' // 在查看位置界面底部显示的超链接,可点击跳转
            });
        };
        //获取地理位置接口
        _mapleApi.prototype.getLocation = function (successFn, type) {
            wx.getLocation({
                type: type || 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
                success: function (res) {
                    var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                    var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
//                        var speed = res.speed; // 速度，以米/每秒计
//                        var accuracy = res.accuracy; // 位置精度
                    successFn && successFn.call(mapleApi, latitude, longitude, res);
                }
            });
        };
        //开启查找周边ibeacon设备接口
        _mapleApi.prototype.startSearchBeacons = function (completeFn, ticket) {
            wx.startSearchBeacons({
                ticket: ticket || "",  //摇周边的业务ticket, 系统自动添加在摇出来的页面链接后面
                complete: function (argv) {
                    //开启查找完成后的回调函数
                    completeFn && completeFn.call(mapleApi, argv);
                }
            });
        };
        //关闭查找周边ibeacon设备接口
        _mapleApi.prototype.stopSearchBeacons = function (completeFn) {
            wx.stopSearchBeacons({
                complete: function (res) {
                    //关闭查找完成后的回调函数
                    completeFn && completeFn.call(mapleApi, res);
                }
            });
        };
        //监听周边ibeacon设备接口
        _mapleApi.prototype.onSearchBeacons = function (completeFn) {
            wx.onSearchBeacons({
                complete: function (argv) {
                    //回调函数，可以数组形式取得该商家注册的在周边的相关设备列表
                    completeFn && completeFn.call(mapleApi, argv);
                }
            });
        };
        //关闭当前网页窗口接口
        _mapleApi.prototype.closeWindow = function () {
            wx.closeWindow();
        };
        //批量隐藏功能按钮接口
        _mapleApi.prototype.hideMenuItems = function (menuList) {
            wx.hideMenuItems({
                menuList: menuList || [] // 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮，所有menu项见附录3
            });
        };
        //批量显示功能按钮接口
        _mapleApi.prototype.showMenuItems = function (menuList) {
            wx.showMenuItems({
                menuList: menuList || [] // 要显示的菜单项，所有menu项见附录3
            });
        };
        //隐藏所有非基础按钮接口
        _mapleApi.prototype.hideAllNonBaseMenuItem = function () {
            wx.hideAllNonBaseMenuItem();
        };
        //显示所有功能按钮接口
        _mapleApi.prototype.showAllNonBaseMenuItem = function () {
            wx.showAllNonBaseMenuItem();
        };
        //调起微信扫一扫接口
        _mapleApi.prototype.scanQRCode = function (options, successFn) {
            options || (options = {});
            if (typeof(options) == 'function') {
                successFn = options;
                options = {};
            }
            wx.scanQRCode({
                needResult: options.needResult || 0, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
                scanType: options.scanType || ["qrCode", "barCode"], // 可以指定扫二维码还是一维码，默认二者都有
                success: function (res) {
                    var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
                    successFn && successFn.call(mapleApi, result, res);
                }
            });
        };
        //跳转微信商品页接口
        _mapleApi.prototype.openProductSpecificView = function (productId, viewType) {
            wx.openProductSpecificView({
                productId: productId, // 商品id
                viewType: viewType || 0 // 0.默认值，普通商品详情页1.扫一扫商品详情页2.小店商品详情页
            });
        };
        //拉取适用卡券列表并获取用户选择信息
        _mapleApi.prototype.chooseCard = function (options, successFn) {
            wx.chooseCard({
                shopId: options.shopId, // 门店Id
                cardType: options.cardType, // 卡券类型
                cardId: options.cardId, // 卡券Id
                timestamp: options.timestamp, // 卡券签名时间戳
                nonceStr: options.nonceStr, // 卡券签名随机串
                signType: options.signType || 'SHA1', // 签名方式，默认'SHA1'
                cardSign: options.cardSign, // 卡券签名
                success: function (res) {
                    var cardList = res.cardList; // 用户选中的卡券列表信息
                    successFn && successFn.call(mapleApi, cardList, res);
                }
            });
        };
        //批量添加卡券接口
        _mapleApi.prototype.addCard = function (cardList, successFn) {
            wx.addCard({
                cardList: cardList, // 需要添加的卡券列表
                success: function (res) {
                    var cardList = res.cardList; // 添加的卡券列表信息
                    successFn && successFn.call(mapleApi, cardList, res);
                }
            });
        };
        //查看微信卡包中的卡券接口
        _mapleApi.prototype.openCard = function (cardList) {
            wx.openCard({
                cardList: cardList// 需要打开的卡券列表
            });
        };
        //发起一个微信支付请求
        _mapleApi.prototype.chooseWXPay = function (config, successFn,groupFn) {
            groupFn || (groupFn = {});

            margin(groupFn,{
                timestamp: parseInt(config.timestamp), // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                nonceStr: config.nonceStr, // 支付签名随机串，不长于 32 位
                package: config.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
                signType: config.signType || 'SHA1', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
                paySign: config.paySign, // 支付签名
                success: function (res) {
                    // 支付成功后的回调函数
                    successFn && successFn.call(mapleApi, res);
                }
            });
            wx.chooseWXPay(groupFn);
        };
        //获取“分享到朋友圈”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareTimeline = function (options, successFn, cancelFn) {
            options || (options = {});
            wx.onMenuShareTimeline({
                title: options.title || '', // 分享标题
                link: options.link || location.href, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: options.imgUrl || '', // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        //获取“分享给朋友”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareAppMessage = function (options, successFn, cancelFn) {
            options || (options = {});
            wx.onMenuShareAppMessage({
                title: options.title || '', // 分享标题
                desc: options.desc || '', // 分享描述
                imgUrl: options.imgUrl || '', // 分享图标
                link: options.link || location.href, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                type: options.type || 'link', // 分享类型,music、video或link，不填默认为link
                dataUrl: options.dataUrl || '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        //获取“分享到QQ”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareQQ = function (options, successFn, cancelFn) {
            options || (options = {});
            wx.onMenuShareQQ({
                title: options.title || '', // 分享标题
                desc: options.desc || '', // 分享描述
                link: options.link || location.href, // 分享链接
                imgUrl: options.imgUrl || '', // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        //获取“分享到腾讯微博”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareWeibo = function (options, successFn, cancelFn) {
            options || (options = {});

            wx.onMenuShareWeibo({
                title: options.title || '', // 分享标题
                desc: options.imgUrl || '', // 分享描述
                link: options.imgUrl || location.href, // 分享链接
                imgUrl: options.imgUrl || '', // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        //获取“分享到QQ空间”按钮点击状态及自定义分享内容接口
        _mapleApi.prototype.onMenuShareQZone = function (options, successFn, cancelFn) {
            options || (options = {});
            wx.onMenuShareQZone({
                title: options.title || '', // 分享标题
                desc: options.desc || '', // 分享描述
                link: options.link || location.href, // 分享链接
                imgUrl: options.imgUrl || '', // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    successFn && successFn.call(mapleApi);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    cancelFn && cancelFn.call(mapleApi);
                }
            });
        };
        _mapleApi.prototype.option = {};

        return mapleApi.init;
    }
}(this));