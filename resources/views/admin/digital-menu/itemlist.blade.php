@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Digital Menu</h1>
    
    @foreach($categories as $category)
        @if($category->items->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="h5 mb-0">{{ $category->name }}</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($category->items as $item)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                    <div>
                                        <h3 class="h6 mb-1">{{ $item->name }}</h3>
                                        <p class="mb-0">Price: ${{ number_format($item->selling_price, 2) }}</p>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
@endsection