@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Create Goods Transfer Note</h2>
    <form action="{{ route('admin.gtn.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>GTN Number</label>
            <input type="text" name="gtn_number" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>From Branch</label>
            <select name="from_branch_id" class="form-control" required>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>To Branch</label>
            <select name="to_branch_id" class="form-control" required>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <input type="hidden" name="organization_id" value="{{ $organization->id }}">

        <div class="mb-3">
            <label>Transfer Date</label>
            <input type="date" name="transfer_date" class="form-control" required>
        </div>

        <h4>Items</h4>
        <div id="items-container">
            <div class="item-row border p-3 mb-2">
                <select name="items[0][item_id]" class="form-control mb-2" required>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->item_code }})</option>
                    @endforeach
                </select>
                <input type="number" name="items[0][transfer_quantity]" class="form-control mb-2" placeholder="Quantity" required>
                <input type="number" name="items[0][transfer_price]" class="form-control mb-2" placeholder="Unit Price" step="0.01">
                <input type="text" name="items[0][batch_no]" class="form-control mb-2" placeholder="Batch No">
                <input type="date" name="items[0][expiry_date]" class="form-control mb-2" placeholder="Expiry Date">
                <textarea name="items[0][notes]" class="form-control" placeholder="Notes"></textarea>
            </div>
        </div>

        <button type="button" class="btn btn-secondary mb-3" onclick="addItem()">Add Item</button>

        <div>
            <label>Notes</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Save GTN</button>
    </form>
</div>

<script>
    let itemIndex = 1;
    function addItem() {
        const container = document.getElementById('items-container');
        const html = `
        <div class="item-row border p-3 mb-2">
            <select name="items[${itemIndex}][item_id]" class="form-control mb-2" required>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->item_code }})</option>
                @endforeach
            </select>
            <input type="number" name="items[${itemIndex}][transfer_quantity]" class="form-control mb-2" placeholder="Quantity" required>
            <input type="number" name="items[${itemIndex}][transfer_price]" class="form-control mb-2" placeholder="Unit Price" step="0.01">
            <input type="text" name="items[${itemIndex}][batch_no]" class="form-control mb-2" placeholder="Batch No">
            <input type="date" name="items[${itemIndex}][expiry_date]" class="form-control mb-2" placeholder="Expiry Date">
            <textarea name="items[${itemIndex}][notes]" class="form-control" placeholder="Notes"></textarea>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    }
</script>
@endsection
