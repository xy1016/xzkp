<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>跳转提示</title>
    <link href="__ROOT__/Static/css/404.css" rel="stylesheet" type="text/css" />
    <script src="__ROOT__/Static/js/jquery.min.js?v=2.1.4"></script>
    <script type="text/javascript">
        $(function() {
            var h = $(window).height();
            $('body').height(h);
            $('.mianBox').height(h);
            centerWindow(".tipInfo");
        });
        //2.将盒子方法放入这个方，方便法统一调用
        function centerWindow(a) {
            center(a);
            //自适应窗口
            $(window).bind('scroll resize',function() {
                center(a);
            });
        }
        //1.居中方法，传入需要剧中的标签
        function center(a) {
            var wWidth = $(window).width();
            var wHeight = $(window).height();
            var boxWidth = $(a).width();
            var boxHeight = $(a).height();
            var scrollTop = $(window).scrollTop();
            var scrollLeft = $(window).scrollLeft();
            var top = scrollTop + (wHeight - boxHeight) / 2;
            var left = scrollLeft + (wWidth - boxWidth) / 2;
            $(a).css({
                "top": top,
                "left": left
            });
        }
    </script>
</head>
<body>
    <div class="mianBox">
        <img src="__ROOT__/Static/img/404/yun0.png" alt="" class="yun yun0" />
        <img src="__ROOT__/Static/img/404/yun1.png" alt="" class="yun yun1" />
        <img src="__ROOT__/Static/img/404/yun2.png" alt="" class="yun yun2" />
        <img src="__ROOT__/Static/img/404/bird.png" alt="" class="bird" />
        <img src="__ROOT__/Static/img/404/san.png" alt="" class="san" />
        <div class="tipInfo">
            <div class="in">
                <div class="textThis system-message">
                    <present name="message">
                        <h2 class="success"><?php echo($message); ?></h2>
                    <else/>
                        <h2 class="error"><?php echo($error); ?></h2>
                    </present>
                        <p><span>页面自动<a id="href" href="<?php echo($jumpUrl); ?>">跳转</a></span><span>等待<b id="wait"><?php echo($waitSecond); ?></b>秒</span></p>
                </div>
<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
var time = --wait.innerHTML;
if(time <= 0) {
    location.href = href;
    clearInterval(interval);
};
}, 1000);
})();
</script>
                </div>
            </div>
        </div>
    </div>
</body>
</html>