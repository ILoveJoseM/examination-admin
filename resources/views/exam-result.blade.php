<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">{{$sort}}{{$title}}</h3>
    </div>

    <!-- /.box-header -->
    <div class="box-body dependencies">
        <div class="table-responsive">
            <table class="table table-striped">
                @foreach($questionList as $number => $question)
                    @php
                        $answerName = "";
                        if(isset($answers[$question->id])){
                            $answerName = implode("", $answers[$question->id]);
                        }
                    @endphp
                    <tr
                        @if(strtoupper($answerName) != strtoupper($question->answer))
                            style="border: red 2px solid;"
                        @endif
                    >
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
                                            <input type="checkbox" name="{{$question->id}}" value="{{$option}}"
                                                   @if(isset($answers[$question->id]) and in_array($option, $answers[$question->id]))
                                                       checked="checked"
                                                   @endif
                                            />&nbsp;{{$option}}、{{$optionName}}
                                        </label>
                                        <br />
                                    @else
                                        <label class="radio-inline">
                                            <input type="radio" name="{{$question->id}}" value="{{$option}}" class="minimal"
                                                   @if(isset($answers[$question->id]) and in_array($option, $answers[$question->id]))
                                                        checked="checked"
                                                   @endif
                                            />&nbsp;{{$option}}、{{$optionName}}
                                        </label>
                                        <br />
                                    @endif
                                @endforeach
                            </p>
                            <p style="font-size: 15px">
                                正确答案：{{$question->answer}}
                            </p>
                            <p style="font-size: 15px">
                                解析：{{$question->description}}
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
