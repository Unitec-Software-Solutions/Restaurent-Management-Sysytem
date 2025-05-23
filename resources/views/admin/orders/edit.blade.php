@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Edit Order #{{ $order->id }}</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label>Order Status</label>
                    <select name="status" class="form-control">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" 
                                {{ $order->status == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>
</div>
@endsection
