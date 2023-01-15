<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">{{$sort}}{{$title}}</h3>
    </div>

    <!-- /.box-header -->
    <div class="box-body dependencies">
        <div class="table-responsive">
            <table class="table table-striped">
                @foreach($questionList as $number => $question)
                    <tr>
                        <td width="1px"><p style="font-size: 17px;font-weight: bolder">{{ $number + 1 }}、</p></td>
                        <td>
                            <p style="font-size: 17px;font-weight: bolder">{{ $question->content }}（{{$question->score}}分）</p>
                            <p style="font-size: 15px">
                                @foreach($question->options as $option => $optionName)
                                    @if(empty($optionName))
                                        @continue;
                                    @endif
                                    @if($questionType == 2)
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="{{$question->id}}" value="{{$option}}" />&nbsp;{{$option}}、{{$optionName}}
                                        </label>
                                        <br />
                                    @else
                                        <label class="radio-inline">
                                            <input type="radio" name="{{$question->id}}" value="{{$option}}" class="minimal"/>&nbsp;{{$option}}、{{$optionName}}
                                        </label>
                                        <br />
                                    @endif
                                @endforeach
                            </p>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>

<script>
</script>
