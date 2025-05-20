@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
            <div class="text-white p-3">
                <h4 class="mb-4">RM SYSTEMS</h4>
                <h5 class="mb-3">Dashboard</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Inventory Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Reservation Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Order Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Customer Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="{{ route('admin.menu.index') }}">Digital Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Settings</a>
                    </li>
                </ul>
                <div class="mt-4">
                    <a href="#" class="text-white">Sign Out</a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Digital Menu</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false">
                            All
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#">Beverages</a></li>
                            <li><a class="dropdown-item" href="#">Dairy Products</a></li>
                            <li><a class="dropdown-item" href="#">Frozen Foods</a></li>
                            <li><a class="dropdown-item" href="#">Packaging</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.menu.create') }}"><i class="fas fa-plus"></i> Add Menu Item</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Beverages Section -->
            <div class="mb-5">
                <h4 class="mb-3">BEVERAGES</h4>
                <p class="text-muted mb-4">Refreshing drinks for all occasions</p>
                
                <div class="row">
                    @foreach($beverages as $item)
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $item->name }}</h5>
                                <p class="card-text">Rs. {{ number_format($item->price, 2) }}</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.menu.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('admin.menu.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Dairy Products Section -->
            <div class="mb-5">
                <h4 class="mb-3">DAIRY PRODUCTS</h4>
                <p class="text-muted mb-4">Fresh dairy products</p>
                
                <div class="row">
                    @foreach($dairyProducts as $item)
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $item->name }}</h5>
                                <p class="card-text">Rs. {{ number_format($item->price, 2) }}</p>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.menu.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('admin.menu.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection