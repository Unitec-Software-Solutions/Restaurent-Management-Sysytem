@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Reservation Summary</div>

                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="card-title">Reservation Details</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Date:</th>
                                        <td>{{ \Carbon\Carbon::parse($reservation->reservation_datetime)->format('F j, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Time:</th>
                                        <td>{{ \Carbon\Carbon::parse($reservation->reservation_datetime)->format('g:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Party Size:</th>
                                        <td>{{ $reservation->party_size }} people</td>
                                    </tr>
                                    @if($reservation->special_requests)
                                    <tr>
                                        <th>Special Requests:</th>
                                        <td>{{ $reservation->special_requests }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge bg-warning">Pending Confirmation</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="alert-heading">Important Information</h6>
                        <ul class="mb-0">
                            <li>Please arrive on time for your reservation</li>
                            <li>We can only hold your table for 15 minutes past your reservation time</li>
                            <li>If you need to cancel, please do so at least 24 hours in advance</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('reservation.confirm', $reservation->id) }}">
                        @csrf
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                Confirm Reservation
                            </button>
                            <a href="{{ route('reservation.create') }}" class="btn btn-outline-secondary">
                                Edit Reservation
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 