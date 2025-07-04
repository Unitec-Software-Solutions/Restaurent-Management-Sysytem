<!-- Create Reservation Modal -->
<div class="modal fade" id="createReservationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar text-info"></i> Create Reservation
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="createReservationForm" onsubmit="submitReservation(event)">
                <div class="modal-body">
                    <!-- Customer Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2">Customer Information</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="res_customer_name">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="res_customer_name" name="customer_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="res_customer_phone">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="res_customer_phone" name="customer_phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="res_customer_email">Email</label>
                                <input type="email" class="form-control" id="res_customer_email" name="customer_email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="res_guests">Number of Guests <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="res_guests" name="guests" min="1" max="20" required value="2">
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2">Reservation Details</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="res_branch">Branch <span class="text-danger">*</span></label>
                                <select class="form-control" id="res_branch" name="branch_id" required onchange="loadAvailableTables()">
                                    <option value="">Select Branch</option>
                                    @if(isset($organization))
                                        @foreach($organization->branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="res_date">Reservation Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="res_date" name="reservation_date" required min="{{ date('Y-m-d') }}" onchange="loadAvailableTables()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="res_time">Reservation Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="res_time" name="reservation_time" required onchange="loadAvailableTables()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="res_duration">Duration (hours)</label>
                                <select class="form-control" id="res_duration" name="duration">
                                    <option value="1">1 hour</option>
                                    <option value="1.5">1.5 hours</option>
                                    <option value="2" selected>2 hours</option>
                                    <option value="2.5">2.5 hours</option>
                                    <option value="3">3 hours</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Table Selection -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2">Table Selection</h6>
                            <div id="availableTablesContainer">
                                <p class="text-muted text-center">Please select branch, date, and time to view available tables</p>
                            </div>
                        </div>
                    </div>

                    <!-- Special Requests -->
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="res_special_requests">Special Requests</label>
                                <textarea class="form-control" id="res_special_requests" name="special_requests" rows="3" placeholder="Any special requirements (birthday, anniversary, dietary restrictions, etc.)"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" id="submitReservationBtn" disabled>
                        <i class="fas fa-calendar-check"></i> Create Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let selectedTableId = null;

// Set minimum date to today
document.getElementById('res_date').value = new Date().toISOString().split('T')[0];

// Set default time to next hour
const now = new Date();
now.setHours(now.getHours() + 1);
document.getElementById('res_time').value = now.toTimeString().slice(0, 5);

function loadAvailableTables() {
    const branchId = document.getElementById('res_branch').value;
    const date = document.getElementById('res_date').value;
    const time = document.getElementById('res_time').value;
    const guests = document.getElementById('res_guests').value;
    
    const container = document.getElementById('availableTablesContainer');
    
    if (!branchId || !date || !time) {
        container.innerHTML = '<p class="text-muted text-center">Please select branch, date, and time to view available tables</p>';
        document.getElementById('submitReservationBtn').disabled = true;
        return;
    }
    
    container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i><p class="mt-2">Loading available tables...</p></div>';
    
    fetch(`/admin/branches/${branchId}/available-tables?date=${date}&time=${time}&guests=${guests}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAvailableTables(data.tables);
            } else {
                container.innerHTML = '<p class="text-warning text-center">No tables available for selected time</p>';
                document.getElementById('submitReservationBtn').disabled = true;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p class="text-danger text-center">Failed to load available tables</p>';
            document.getElementById('submitReservationBtn').disabled = true;
        });
}

function displayAvailableTables(tables) {
    const container = document.getElementById('availableTablesContainer');
    
    if (tables.length === 0) {
        container.innerHTML = '<p class="text-warning text-center">No tables available for selected time</p>';
        document.getElementById('submitReservationBtn').disabled = true;
        return;
    }
    
    let html = '<div class="row">';
    tables.forEach(table => {
        html += `
            <div class="col-md-4 mb-2">
                <div class="card table-card" data-table-id="${table.id}" onclick="selectTable(${table.id}, '${table.number}', ${table.capacity})">
                    <div class="card-body text-center p-2">
                        <h6 class="mb-1">Table ${table.number}</h6>
                        <small class="text-muted">Capacity: ${table.capacity} guests</small>
                        <div class="mt-2">
                            <span class="badge badge-success">Available</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
    document.getElementById('submitReservationBtn').disabled = true;
}

function selectTable(tableId, tableNumber, capacity) {
    // Remove previous selection
    document.querySelectorAll('.table-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-light');
    });
    
    // Add selection to clicked table
    const tableCard = document.querySelector(`[data-table-id="${tableId}"]`);
    tableCard.classList.add('border-primary', 'bg-light');
    
    selectedTableId = tableId;
    document.getElementById('submitReservationBtn').disabled = false;
}

function submitReservation(event) {
    event.preventDefault();
    
    if (!selectedTableId) {
        alert('Please select a table for the reservation');
        return;
    }
    
    const formData = new FormData(event.target);
    const reservationData = {
        customer_name: formData.get('customer_name'),
        customer_phone: formData.get('customer_phone'),
        customer_email: formData.get('customer_email'),
        guests: formData.get('guests'),
        branch_id: formData.get('branch_id'),
        table_id: selectedTableId,
        reservation_date: formData.get('reservation_date'),
        reservation_time: formData.get('reservation_time'),
        duration: formData.get('duration'),
        special_requests: formData.get('special_requests')
    };
    
    // Disable submit button
    document.getElementById('submitReservationBtn').disabled = true;
    document.getElementById('submitReservationBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Reservation...';
    
    fetch('/admin/organizations/{{ $organization->id ?? "0" }}/reservations', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(reservationData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            $('#createReservationModal').modal('hide');
            
            // Reset form
            document.getElementById('createReservationForm').reset();
            selectedTableId = null;
            
            alert('Reservation created successfully!');
            
            // Optionally open confirmation page
            if (result.confirmation_url) {
                window.open(result.confirmation_url, '_blank');
            }
            
            // Optionally reload page
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to create reservation'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create reservation');
    })
    .finally(() => {
        // Re-enable submit button
        document.getElementById('submitReservationBtn').disabled = false;
        document.getElementById('submitReservationBtn').innerHTML = '<i class="fas fa-calendar-check"></i> Create Reservation';
    });
}
</script>

<style>
.table-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.table-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.table-card.border-primary {
    border-width: 2px !important;
}
</style>
