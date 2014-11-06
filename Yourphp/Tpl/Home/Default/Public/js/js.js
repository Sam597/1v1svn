// JavaScript Document
$(document).ready(function(){
    $(".span01").mouseover(function(){//top bar 旗下网站
        $(this).addClass("a01H");
        $(".bar1").show();
    }).mouseout(function(){
        $(this).removeClass("a01H");
        $(".bar1").hide();
    });
    $(".bar1").mouseover(function(){
        $(this).show();
    }).mouseout(function(){
        $(this).hide();
    });
    //切换城市
    $(".change_city").mouseover(function(){
        $(this).show();
    }).mouseout(function(){
        $(this).hide();

    });
    $("#cc").mouseover(function(){
        $(".change_city").show();
    }).mouseout(function(){
        $(".change_city").hide();
    });
    $(".top1").eq(0).show();
    $(".wangpaibox a").mouseover(function(){//top50
        $(this).addClass("acur32").siblings().removeClass("acur32");
        var $aNum=$(this).index() -1;
        $(".top1").eq($aNum).show().siblings(".top1").hide();
        $(".maps").eq($aNum).show().siblings(".maps").hide();
        
    });
    $(".xuexibox a").mouseover(function(){//top50
        $(this).addClass("acur32").siblings().removeClass("acur32");
        var $aNum=$(this).index() -1;
        $(".maps").eq($aNum).show().siblings(".maps").hide();
        
    });
    $(".specialbox a").mouseover(function(){
        var $aNum=$(this).index();	 
        $(".specials").eq($aNum).show().siblings(".specials").hide();
    });
	 
    $(".jsbox").eq(0).show();
    $(".titleb3 a").mouseover(function(){//强大师资
        $(this).addClass("aCur33").siblings("a").removeClass("aCur33");
        var $aNum=$(this).index() -1;
        $(".jsbox").eq($aNum).show().siblings(".jsbox").hide();
    });
	 
    $(".jsbox .dl02").mouseover(function(){//强大师资教师信息显示隐藏
        $(this).find(".jsjieshao").show();
        $(this).siblings(".dl02").find(".jsjieshao").hide(0);
    });
	 
    $(".ul09 .li42" ).click(function(){
        $(this).addClass("Liout").siblings(".li42").removeClass("Liout");
        $(this).find(".li02").show();
        $(this).siblings(".li42").find(".li02").hide();
        
        //处理等高
        var $height = $(".anli_right").height(); 
        if($(".anli_left3").height()>$(".anli_right").height()-150)
            $(".anli_left3").css("height",$(".anli_right").height()+150);
    });
    $(".li02").click(function(){
        $(this).find("a").addClass("aCur08");
        $(this).siblings("em").find("a").removeClass("aCur08");
    });
    //处理等高
    var $height = $(".anli_right").height(); 
    if($(".anli_left").height()<$(".anli_right").height())
        $(".anli_left").css("height",$height);
    else
        $(".anli_right").css("height",$(".anli_right").height()+150);
    //处理等高
    var $height = $(".anli_right").height(); 
    if($(".anli_left3").height()<$(".anli_right").height())
        $(".anli_left3").css("height",$height);
    else
        $(".anli_right").css("height",$(".anli_right").height()+150);
});








