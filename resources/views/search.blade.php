@extends('layout');

@section('title')
    Search
@endsection

@section('main_content')
    <h1>Search engine</h1>
    @if($errors->any())
        <div class="alert-danger alert">
            <ul>
                @foreach($errors->all() AS $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="/search">
        @csrf
        <select id="country_id" name="country_id">
            <option value="0">Choose Country</option>
            @foreach($countries AS $country)
                <option value="{{ $country->get_code() }}"
                @if($selected_country == $country->get_code())
                    selected
                @endif >
                    {{ $country->get_name() }}
                </option>
            @endforeach
        </select>
        <select id="year" name="year">
            <option value="0">Choose Year</option>
        @for($i = 2010; $i < 2040; $i++)
                <option value="{{ $i }}"
                @if($selected_year == $i)
                    selected
                @endif>
                {{ $i }}
                </option>
        @endfor
        </select>
        <hr>
        <button type="submit" class="btn btn-success">Search</button>
    </form>

    @if(!empty($holidays))
        <div>Today: {{ date('Y-m-d') }}  @if(!empty($current_date_status)) {{ $current_date_status }} @endif</div>
        <div>Total holidays: {{ count($holidays) }}</div>
        <div class="table-responsive">
        <table class="table text-center table-bordered">
            <thead class="thead-dark">
                <tr scope="row">
                    <th>Date</th>
                    <th>Holiday Name</th>
                    <th>Holiday Name(origin)</th>
                </tr>
            </thead>
            <tbody>
            @foreach($holidays AS $month=>$data)
                <tr scope="row"><td colspan="3">Holidays In {{ $month }}</td></tr>
                @foreach($data AS $holiday)
                <tr scope="row">
                    <td>{{ $holiday->date }}</td>
                    <td>{{ $holiday->en_name }}</td>
                    <td>{{ $holiday->original_name }}</td>
                </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
        </div>
    @endif
@endsection
