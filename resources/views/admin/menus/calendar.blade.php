@extends('layouts.admin')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.menus.index') }}" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Menu Calendar</h1>
                    <p class="text-gray-600">Schedule and manage menu availability</p>
                </div>
            </div>
            
            <div class="flex gap-3">
                <select id="branch-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                
                <a href="{{ route('admin.menus.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Create Menu
                </a>
            </div>
        </div>
    </div>

    <!-- Calendar Navigation -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
                <button id="prev-month" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-chevron-left text-lg"></i>
                </button>
                <h2 id="current-month" class="text-xl font-semibold text-gray-900"></h2>
                <button id="next-month" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-chevron-right text-lg"></i>
                </button>
            </div>
            
            <div class="flex gap-3">
                <button id="today-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm">
                    Today
                </button>
                <button id="month-view" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">
                    Month
                </button>
                <button id="week-view" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm">
                    Week
                </button>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-6 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <span class="text-gray-600">Active Menu</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-blue-500 rounded"></div>
                <span class="text-gray-600">Upcoming Menu</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-gray-400 rounded"></div>
                <span class="text-gray-600">Inactive Menu</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-500 rounded"></div>
                <span class="text-gray-600">Expired Menu</span>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div id="calendar"></div>
    </div>

    <!-- Menu Details Modal -->
    <div id="menu-modal" class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modal-title" class="text-lg font-semibold text-gray-900"></h3>
                <button id="close-modal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="modal-content" class="space-y-4">
                <!-- Content will be dynamically populated -->
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button id="modal-close" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                    Close
                </button>
                <button id="modal-edit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                    Edit Menu
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('menu-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');
    const closeModal = document.getElementById('close-modal');
    const modalClose = document.getElementById('modal-close');
    const modalEdit = document.getElementById('modal-edit');
    const branchFilter = document.getElementById('branch-filter');
    
    let currentEditingMenu = null;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: false, // We're using custom navigation
        height: 'auto',
        events: function(fetchInfo, successCallback, failureCallback) {
            const branchId = branchFilter.value;
            const params = new URLSearchParams({
                start: fetchInfo.startStr,
                end: fetchInfo.endStr
            });
            
            if (branchId) {
                params.append('branch_id', branchId);
            }

            fetch(`{{ route('admin.menus.calendar.data') }}?${params}`)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Error fetching calendar data:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            showMenuDetails(info.event);
        },
        dateClick: function(info) {
            // Navigate to create menu with pre-selected date
            window.location.href = `{{ route('admin.menus.create') }}?date=${info.dateStr}`;
        },
        eventDisplay: 'block',
        dayMaxEvents: 3,
        moreLinkClick: 'popover'
    });

    calendar.render();

    // Update month title
    function updateMonthTitle() {
        const currentDate = calendar.getDate();
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"];
        document.getElementById('current-month').textContent = 
            `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
    }

    // Navigation buttons
    document.getElementById('prev-month').addEventListener('click', function() {
        calendar.prev();
        updateMonthTitle();
    });

    document.getElementById('next-month').addEventListener('click', function() {
        calendar.next();
        updateMonthTitle();
    });

    document.getElementById('today-btn').addEventListener('click', function() {
        calendar.today();
        updateMonthTitle();
    });

    document.getElementById('month-view').addEventListener('click', function() {
        calendar.changeView('dayGridMonth');
        updateMonthTitle();
    });

    document.getElementById('week-view').addEventListener('click', function() {
        calendar.changeView('timeGridWeek');
        updateMonthTitle();
    });

    // Branch filter
    branchFilter.addEventListener('change', function() {
        calendar.refetchEvents();
    });

    // Modal functionality
    function showMenuDetails(event) {
        currentEditingMenu = event.id;
        modalTitle.textContent = event.title;
        
        // Create modal content
        const props = event.extendedProps;
        modalContent.innerHTML = `
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Type</label>
                    <p class="text-gray-900">${props.type ? props.type.charAt(0).toUpperCase() + props.type.slice(1).replace('_', ' ') : 'Unknown'}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Branch</label>
                    <p class="text-gray-900">${props.branch || 'All Branches'}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <p class="text-gray-900">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(props.status)}">
                            ${props.status ? props.status.charAt(0).toUpperCase() + props.status.slice(1) : 'Unknown'}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Duration</label>
                    <p class="text-gray-900">${formatEventDate(event)}</p>
                </div>
            </div>
        `;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function getStatusClass(status) {
        switch(status) {
            case 'active': return 'bg-green-100 text-green-800';
            case 'inactive': return 'bg-gray-100 text-gray-800';
            case 'upcoming': return 'bg-blue-100 text-blue-800';
            case 'expired': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    function formatEventDate(event) {
        const start = new Date(event.start);
        const end = event.end ? new Date(event.end) : start;
        
        if (start.toDateString() === end.toDateString()) {
            return start.toLocaleDateString();
        } else {
            return `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;
        }
    }

    // Modal close handlers
    [closeModal, modalClose].forEach(btn => {
        btn.addEventListener('click', function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentEditingMenu = null;
        });
    });

    modalEdit.addEventListener('click', function() {
        if (currentEditingMenu) {
            window.location.href = `/admin/menus/${currentEditingMenu}/edit`;
        }
    });

    // Close modal on backdrop click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentEditingMenu = null;
        }
    });

    // Initialize month title
    updateMonthTitle();
});
</script>
@endpush
@endsection
