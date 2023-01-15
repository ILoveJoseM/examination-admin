<script>
    $.fn.smartFloat = function () {

        var position = function (element) {

            var top = 100, pos = element.css("position");

            $(window).scroll(function () {

                var scrolls = $(this).scrollTop();
                var originalWidth = $("#fixed").width();

                console.log(scrolls);

                if (scrolls > top) {

                    if (window.XMLHttpRequest) {
                        element.css({

                            position: "fixed",
                            top: 0,
                            width: originalWidth + "px",
                            zIndex: 999

                        });

                    } else {
                        element.css({
                            top: scrolls,
                            width: originalWidth + "px",
                            zIndex: 999
                        });

                    }

                } else {
                    console.log("还原");
                    element.removeAttr("style");
                    element.css({
                        width: originalWidth + "px",
                        position: "position"
                    });
                }

            });

        };

        return $(this).each(function () {

            position($(this));

        });

    };
    var exam_time = {{$exam_time}};
</script>
<div class="box box-default" id="fixed" style="z-index: 999">
    <div class="box-header with-border">
        <h3 class="box-title">考试时间：<span id="exam_time"></span></h3>
        <div class="box-tools pull-right">
            <button type="button" id="commit" class="btn btn-primary" data-loading-text="Loading...">
                交卷
            </button>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<script>
    var examination_subject_id = {{$examination_subject_id}}
    $("#fixed").smartFloat();

    $("#commit").click(function () {
        if (confirm("确定要交卷？")) {
            commit();
        }
    });

    function commit() {
        let input = $("input");
        let formData = {};
        for (let i = 0; i < input.length; i++) {
            let obj = input.get(i);
            if (formData[obj.name] === undefined) {
                formData[obj.name] = [];
            }
            if (obj.checked) {
                formData[obj.name].push(obj.value)
            }
        }

        formData['_token'] = '{{csrf_token()}}';
        $.ajax({
            "url": "/admin/user/examination_subject/" + examination_subject_id + "/commit",
            "type": "POST",
            "data": formData,
            "dataType": "json",
            "success": function (response) {
                console.log(response);
                if (response.code == 0) {
                    location.href = "/admin/user/examination_subject/" + response.id + "/success"
                } else {
                    alert("操作失败");
                }
            }
        })
    }

    var intervalFun = function () {
        if (exam_time === 0) {
            // 自动交卷
            clearInterval(interval);
            commit();
            return;
        }
        let minutes = Math.floor(exam_time / 60);
        let seconds = exam_time % 60;
        if (minutes < 10) {
            minutes = "0" + minutes
        }

        if (seconds < 10) {
            seconds = "0" + seconds
        }
        $("#exam_time").text(minutes + ":" + seconds);
        exam_time--
    };
    var interval = setInterval(intervalFun, 1000)
</script>
