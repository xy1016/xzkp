$(function(){
var flag = true; //标记保存状态,防止在返回结果前重复保存
$(".modal").on('submit', 'form', function(e){
    e.preventDefault();
    if(!flag) return false;
    flag = false;
    //清掉之前的提示信息
    $(this).find(".result").html('').end().find("input:not('[type*=hidden], [readonly]')").next().children().remove();
    $(this).find("#submitButton").text("保存中...");
    var str = $(this).serialize();
    postData(this, $(this).attr("action"), str);
});

function showResult(obj, status)
{
  var result = $(obj).find(".result");
  if(status){
    result.css("color","green").html("保存成功!");
  } 
  else result.css("color","red").html("保存失败!");
  $(obj).find("#submitButton").text("保存");
  flag = true;
}

function postData(obj, url, str) {
    $.post(url, str, function (data) {
        if (data.status == 1) {
            showResult(obj, 1);
            $(obj).find(".fa-info-circle").remove();
            // parent.location.reload();
        } else if(data.error != undefined) {
            var res = data.error;
            for (var i = 0; i < res.length; i++) {
                var ele = $(obj).find("input[name=\"" + res[i].ele + "\"]:not('[type*=hidden], [readonly]')");
                if (res[i].status == 1) {
                   ele.next().children().remove();
                } else {
                    ele.next().append('<i class="fa fa-info-circle"> ' + res[i].error + '</i>')
                    // ele.next().children("i").addClass("fa fa-info-circle").text(res[i].error);
                }
            }
            if(res.length > 1)
            showResult(obj, 0);
        }
        else showResult(obj, 0);
    }, 'JSON');  
  }
});