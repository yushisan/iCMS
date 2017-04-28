$(function() {
    //图片延迟加载
    $("img.lazy").lazyload();
    //图片切换
    $(".carousel-box").slider({
        left_btn: '#carousel-left',
        right_btn: '#carousel-right',
    });
    //标签页
    $(".tabs-wrap").tabs({
        action: 'mouseover'
    });
    $(".rank").tabs({
        item: '.rank-list',
        action: 'mouseover'
    });

    var doc = $(document);
    //搜索
    doc.on('click', ".search-btn", function(event) {
        var q = $('[name="q"]',"#search-form").val();
        if(q==""){
            iCMS.UI.alert("请输入关键词");
            return false;
        }
    });
    //验证码
    doc.on('click', ".seccode-img,.seccode-text", function(event) {
        event.preventDefault();
        iCMS.UI.seccode();
    });
    //user模块
    iCMS.run('user', function($USER) {
        //定义登陆事件
        // $USER.LOGIN = function () {}
        //
        //用户状态
        $USER.STATUS({},
            //登陆后事件
            function($info) {
                iCMS.$('user_nickname').text($info.nickname);
                iCMS.$('user_avatar').attr("src",$info.avatar).show();
                iCMS.$('login').hide();
                iCMS.$('profile').show();
            },
            //未登陆事件
            function(f) {
                // console.log(f)
            }
        );
        //点击退出登陆
        doc.on('click', "[i='logout']", function(event) {
            event.preventDefault();
            $USER.LOGOUT({
                'forward': window.top.location.href
            },
            //退出成功事件
            function(s) {
                window.top.location.reload();
            });
        });
        //点击关注
        doc.on('click', "[i^='follow']", function(event) {
            event.preventDefault();
            $USER.FOLLOW(this,
                //关注成功
                function(ret,$param){
                    if(ret.code){
                        var show = ($param.follow == '1' ? '0' : '1');
                        $("[i='follow:"+$param.uid+":"+$param.follow+"']").hide();
                        $("[i='follow:"+$param.uid+":"+show+"']").show();
                    }
                },
                //关注失败
                function (ret) {
                    iCMS.UI.alert(ret.msg);
                }
            );
        });
        //点击私信
        doc.on('click', '[i^="pm"]', function(event) {
            event.preventDefault();
            $USER.PM(this);
        });
        //点击举报
        doc.on('click', '[i="report"]', function(event) {
            event.preventDefault();
            $USER.REPORT(this);
        });
        //用户信息浮层
        $USER.UCARD();
    });
    //通用事件
    iCMS.run('common', function($COMMON) {
        //点赞
        doc.on('click', '[i^="vote:"]', function(event) {
            event.preventDefault();
            var me = this;
            $COMMON.vote(this,
                //点赞成功后
                function(ret,param) {
                       var numObj = iCMS.$('vote_'+param['type']+'_num',me),
                           count = parseInt(numObj.text());
                        numObj.text(count + 1);
                }
            );
        });
        //收藏
        doc.on('click', '[i^="favorite:"]', function(event) {
            event.preventDefault();
            $COMMON.favorite(this);
        });
    });
    //用户主页评论
    iCMS.run('comment', function($COMMENT) {
        doc.on('click', '[i="comment:article"]', function(event) {
            event.preventDefault();
            var me = this;
            $COMMENT.create(me);
            //加载评论框模板
            // $COMMENT.widget('form',function (tmpl) {
            //     $COMMENT._widget._form = $(tmpl);
            // });
        });
    });
});
//user模块API
var iUSER = iCMS.run('user');
//comment模块API
// var iCOMMENT = iCMS.run('comment');

function imgFix (im, x, y) {
    x = x || 99999
    y = y || 99999
    im.removeAttribute("width");
    im.removeAttribute("height");
    if (im.width / im.height > x / y && im.width > x) {
        im.height = im.height * (x / im.width)
        im.width  = x
        im.parentNode.style.height = im.height * (x / im.width) + 'px';
    } else if (im.width / im.height <= x / y && im.height > y) {
        im.width  = im.width * (y / im.height)
        im.height = y
        im.parentNode.style.height = y + 'px';
    }
}
function redirect_to_mobile(url){
    if(url){
        if (/AppleWebKit.*Mobile/i.test(navigator.userAgent) || (/MIDP|SymbianOS|NOKIA|SAMSUNG|LG|NEC|TCL|Alcatel|BIRD|DBTEL|Dopod|PHILIPS|HAIER|LENOVO|MOT-|Nokia|SonyEricsson|SIE-|Amoi|ZTE/.test(navigator.userAgent))){
            // console.log(window.location.href);
            if(window.location.href.indexOf(url)<0){
                // console.log(url);
                window.location.href = url;
            }
        }
    }
}
