@extends('layouts.app')

@section('content')

<div class="container">

    <h1>第{{Config::get('buildcompetition.build_competition_count')}}回 建築コンペ投票ページ</h1>

    @if (Session::has('message'))
        <div class="alert alert-danger">{!! nl2br(e(Session::get('message'))) !!}</div>
    @endif

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <p>1つの応募テーマにつき、1票入れることができます。</p>

    <form method="post" action="/buildCompetition/submit" id="form">
        <div class="form-group">
            {{ csrf_field() }}

            @foreach($apply_data as $app_key => $data)
                <h2><span class="glyphicon {{$data['glyphicon']}}"></span> {{ $data['theme_division_name'] }}</h2>

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>候補者</th>
                        <th>アピールポイント</th>
                        <th>区画No</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data['apply_data'] as $val)
                        <tr>
                            <td>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="{{$val->theme_division_id}}" @if($val->build_competition_vote_id) disabled @endif @if($val->build_competition_apply_id === $val->build_competition_vote_apply_id) checked @endif value="{{$val->build_competition_apply_id}}">{{$val->mcid}}
                                    </label>
                                </div>
                            </td>

                            <td style="vertical-align: middle;">
                                {{$val->apply_comment}}
                            </td>
                            <td style="vertical-align: middle;">
                                {{$val->partition_no}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>

                </table>

            @endforeach


        </div>
        @if($vote_flg)
            <button type="submit" class="btn btn-primary" style="margin-bottom: 10px;">送信</button>
        @else
            <button type="button" class="btn btn-success" disabled>投票ありがとうございました！</button>
        @endif
    </form>
</div>

{{-- 広告欄 --}}
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-1577125384876056"
     data-ad-slot="4448153429"
     data-ad-format="auto"></ins>
<script>
    (adsbygoogle = window.adsbygoogle || []).push({});
</script>

@include('footer')

@endsection
