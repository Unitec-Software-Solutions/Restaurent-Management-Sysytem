{{-- filepath: resources/views/orders/create.blade.php --}}
@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0">Place New Order</h5>
                </div>

                <div class="card-body p-3">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('orders.store') }}">
                        @csrf
                        <input type="hidden" name="reservation_id" value="{{ $reservationId }}">

                        <div class="mb-3">
                            <h6 class="mb-2 text-secondary">Select Menu Items</h6>
                            <div class="row g-2">
                                @foreach($menuItems as $item)
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card h-100 border-primary compact-card">
                                        <div class="card-body p-2 d-flex flex-column">
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" 
                                                    name="items[{{ $item->id }}][item_id]" 
                                                    value="{{ $item->id }}" 
                                                    id="item{{ $item->id }}">
                                                <label class="form-check-label fw-bold fs-6" for="item{{ $item->id }}">
                                                    {{ Str::limit($item->name, 15) }}
                                                </label>
                                            </div>
                                            <div class="mt-auto">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted fs-7">
                                                        Rs.{{ number_format($item->selling_price, 0) }}
                                                    </span>
                                                    <input type="number" 
                                                        name="items[{{ $item->id }}][quantity]" 
                                                        class="form-control form-control-sm" 
                                                        min="1" 
                                                        value="1"
                                                        style="width: 50px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check2-circle me-1"></i>
                                Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .compact-card {
        min-width: 120px;
        min-height: 120px;
        max-width: 140px;
        margin: 0 auto;
    }
    .fs-7 {
        font-size: 0.85rem;
    }
    .form-control-sm {
        padding: 0.15rem 0.3rem;
        font-size: 0.8rem;
    }
    .form-check-label {
        line-height: 1.2;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // For each menu item, if its checkbox is not checked, remove its quantity input
        document.querySelectorAll('.form-check-input').forEach(function(checkbox) {
            if (!checkbox.checked) {
                // Remove the corresponding quantity input
                const quantityInput = checkbox.closest('.card-body').querySelector('input[type="number"]');
                if (quantityInput) {
                    quantityInput.disabled = true;
                }
                // Also remove the item_id input so it's not submitted
                checkbox.disabled = true;
            }
        });
    });
});
</script>
@endsection