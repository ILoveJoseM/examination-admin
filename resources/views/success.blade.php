<style>
    .title {
        font-size: 50px;
        color: #636b6f;
        font-family: 'Raleway', sans-serif;
        font-weight: 100;
        display: block;
        text-align: center;
        margin: 20px 0 10px 0px;
    }

    .links {
        text-align: center;
        margin-bottom: 20px;
    }

    .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }
</style>

<div class="title">
    <i class="fa fa-check"></i>提交成功，本次成绩{{$score}}分<br />
    <button type="button" id="commit" class="btn btn-primary" data-loading-text="Loading...">
        确定（<span id="interval">5</span>）
    </button>
</div>
<script>
    var seconds = 5;
    var redirect = function () {
        location.href = "/admin/user_examination_history";
    }
    let interval = setInterval(function () {
        if(seconds <= 0){
            redirect();
            clearInterval(interval);
            return;
        }
        seconds--;
        $("#interval").html(seconds)
    },1000)
    $("#commit").click(redirect);


</script>
{{--<div class="links">--}}
    {{--<a href="https://github.com/z-song/laravel-admin" target="_blank">Github</a>--}}
    {{--<a href="http://laravel-admin.org/docs"  target="_blank">Documentation</a>--}}
    {{--<a href="http://laravel-admin.org/demo"  target="_blank">Demo</a>--}}
{{--</div>--}}
