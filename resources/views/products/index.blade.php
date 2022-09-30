@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="/product" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control" value="@if(isset($_GET['title'])){{$_GET['title']}}@endif">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        <option value="">--Select A Variant--</option>
                        @foreach ($variant_list as $key => $variant)
                            <optgroup label="{{$variant['title']}}">
                                @foreach ($variant['product_variants'] as $key => $p_variant)
                                    <option value="{{$p_variant['id']}}" @if(isset($_GET['date'])) selected @endif>{{$p_variant['variant']}}</option>
                                @endforeach
                            </optgroup>

                          @endforeach

                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="From" placeholder="From" class="form-control" value="@if(isset($_GET['price_from'])){{$_GET['price_from']}}@endif">
                        <input type="text" name="price_to" aria-label="To" placeholder="To" class="form-control" value="@if(isset($_GET['price_to'])){{$_GET['price_to']}}@endif">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control" value="@if(isset($_GET['date'])){{$_GET['date']}}@endif">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                        @php $sl= 1; @endphp
                        @foreach ($products as $key => $value)



                            <tr>
                                <td>{{$sl++}}</td>
                                <td>{{ $value['title'] }}
                                    <br> Created at : {{date('d-M-Y', strtotime($value['created_at']))}}</td>
                                <td>{{ $value['description'] }}</td>
                                <td>
                                    <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant">
                                        @foreach ($value['product_variant_prices'] as $vkey => $val)
                                            <dt class="col-sm-3 pb-0">
                                                {{$val['variant_two']['variant']}}/ {{$val['variant_one']['variant']}}@if($val['variant_three'])/{{$val['variant_three']['variant']}}@endif
                                            </dt>
                                            <dd class="col-sm-9">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4 pb-0">Price : {{ number_format( $val['price'],2) }}</dt>
                                                    <dd class="col-sm-8 pb-0">InStock : {{ number_format($val['stock'],2) }}</dd>
                                                </dl>
                                            </dd>
                                        @endforeach
                                    </dl>
                                    <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('product.edit', $value['id']) }}" class="btn btn-success">Edit</a>
                                    </div>
                                </td>
                            </tr>
                    @endforeach

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{$products->firstItem()}} to {{$products->lastItem()}} out of {{$products->total()}}</p>
                </div>
                <div class="col-md-2">
                    {{ $products->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

@endsection
