// JavaScript Document
$(document).ready(function(){
    //切换城市
    $(".change").mouseover(function(){
        $(".citychange").show();
        $('#city-box').addClass('city-box');
    }).mouseout(function(){
        $(".citychange").hide();
        $('#city-box').removeClass('city-box');

    });
    $(".citychange").mouseover(function(){
        $(".citychange").show();
    }).mouseout(function(){
        $(".citychange").hide();
    });
    //top_bar菜单
    $(".icon01").mouseover(function(){
		
        });
    //实现nav鼠标经过去除左右两侧的竖线
    $(".nav li a").mouseover(function(){ 
        var $aCur = $(this).parent().index()-1;
        $(".nav li").eq($aCur).find("a").addClass("a01");
        $(".nav li").eq($aCur).siblings().find("a").removeClass("a01");
    }).mouseout(function(){
        var $aCur2 = $(this).parent().index()-1;
        $(".nav li").eq($aCur2).find("a").removeClass("a01");
    });
    $('.ims2,ims').hide();
    $('.ims2:eq(0),ims:eq(0)').show();

    //首页名师之队模块 切换
    $(".imingshi span").mouseover(function(){
        $(this).addClass("msCur").siblings().removeClass("msCur");
        var $ms = $(this).index();
        $(".ims_box .ims").eq($ms).show().siblings().hide();
    });
    //首页成功案例模块 切换
    $(".imingshi2 span").mouseover(function(){
        $(this).addClass("msCur").siblings().removeClass("msCur");
        var $ms = $(this).index();
        $(".ims_box2 .ims2").eq($ms).show().siblings().hide();
    });
    //成功案例左侧列表 最后一个不显示borde
    $(".bdlist dl:last").css("border-bottom","none");
    //名师之路左侧列表 最后一个不显示border
    $(".cont_left dl:last").css("border-bottom","none");
    //让左右两侧高度统一
    //    if($('.cont_left img:last').complete) { // 如果图片已经存在于浏览器缓存，直接调用回调函数
    //        var $height = $(".cont_left").height()+30; 
    //        alert(345345);
    //        if($(".cont_left").height()>$(".cont_right").height())
    //            $(".cont_right").css("height",$height);
    //     }else{
    //        $('.cont_left img:last').load(function(){
    //            var $height = $(".cont_left").height()+30; 
    //            if($(".cont_left").height()>$(".cont_right").height())
    //                $(".cont_right").css("height",$height);
    //        });
    //    }
    var $height = $(".cont_left").height()+30; 
    if($(".cont_left").height()>$(".cont_right").height())
        $(".cont_right").css("height",$height);
    else
        $(".cont_right").css("height",$(".cont_right").height()+150);

    //隔行变色
    $(".tab01 p").each(function(i){
        this.style.backgroundColor = ['#FFF','#FAFAFA'][i%2]
    });
    //去除上一层背景
    $('.aCur1').closest('li').prev().addClass('nobg');



});

















