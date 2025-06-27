@props([
    'branches' => [],
    'selectedBranch' => null,
    'selectedDate' => null,
    'selectedTime' => null,
    'partySize' => 2,
    'customerName' => '',
    'customerPhone' => '',
    'customerEmail' => '',
    'specialRequests' => ''
])

<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
            <i class="fas fa-calendar-plus text-indigo-600 mr-2"></i>
            Make a Reservation
        </h3>
        <p class="text-gray-600">Book your table for an unforgettable dining experience</p>
    </div>
    
    <form action="{{ route('guest.reservations.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Branch Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    Restaurant Branch
                </label>
                <select name="branch_id" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Select a branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} - {{ $branch->address }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Party Size -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-users mr-1"></i>
                    Party Size
                </label>
                <select name="party_size" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $partySize == $i ? 'selected' : '' }}>
                            {{ $i }} {{ $i == 1 ? 'Guest' : 'Guests' }}
                        </option>
                    @endfor
                    <option value="13+">13+ Guests (Call for arrangement)</option>
                </select>
                @error('party_size')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <!-- Date and Time -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-1"></i>
                    Date
                </label>
                <input type="date" name="reservation_date" required 
                       value="{{ $selectedDate ?: date('Y-m-d', strtotime('+1 day')) }}"
                       min="{{ date('Y-m-d') }}"
                       max="{{ date('Y-m-d', strtotime('+60 days')) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('reservation_date')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-clock mr-1"></i>
                    Time
                </label>
                <select name="reservation_time" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Select time</option>
                    @foreach(['11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30', '22:00'] as $time)
                        <option value="{{ $time }}" {{ $selectedTime == $time ? 'selected' : '' }}>
                            {{ date('g:i A', strtotime($time)) }}
                        </option>
                    @endforeach
                </select>
                @error('reservation_time')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <!-- Customer Information -->
        <div class="border-t pt-6">
            <h4 class="text-md font-medium text-gray-900 mb-4">Contact Information</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i>
                        Full Name
                    </label>
                    <input type="text" name="customer_name" required 
                           value="{{ old('customer_name', $customerName) }}"
                           placeholder="Your full name"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('customer_name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-phone mr-1"></i>
                        Phone Number
                    </label>
                    <input type="tel" name="customer_phone" required 
                           value="{{ old('customer_phone', $customerPhone) }}"
                           placeholder="+1 (555) 123-4567"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('customer_phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-1"></i>
                    Email Address (Optional)
                </label>
                <input type="email" name="customer_email" 
                       value="{{ old('customer_email', $customerEmail) }}"
                       placeholder="your.email@example.com"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('customer_email')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <!-- Special Requests -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-comment mr-1"></i>
                Special Requests (Optional)
            </label>
            <textarea name="special_requests" rows="3" 
                      placeholder="Any special dietary requirements, celebration details, or other requests..."
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('special_requests', $specialRequests) }}</textarea>
            @error('special_requests')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">
                <i class="fas fa-info-circle mr-1"></i>
                Let us know about birthdays, anniversaries, dietary restrictions, accessibility needs, etc.
            </p>
        </div>
        
        <!-- Terms and Conditions -->
        <div class="flex items-start">
            <input type="checkbox" name="terms_accepted" required 
                   class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <label class="ml-2 text-sm text-gray-600">
                I agree to the 
                <a href="#" class="text-indigo-600 hover:text-indigo-800">reservation terms and conditions</a>
                and 
                <a href="#" class="text-indigo-600 hover:text-indigo-800">cancellation policy</a>
            </label>
        </div>
        
        <!-- Submit Button -->
        <div class="flex items-center justify-between pt-4">
            <div class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Your information is secure and will only be used for reservation purposes
            </div>
            <button type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center">
                <i class="fas fa-calendar-check mr-2"></i>
                Book Reservation
            </button>
        </div>
    </form>
    
    <!-- Availability Notice -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">Reservation Policy:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Reservations can be made up to 60 days in advance</li>
                    <li>Tables are held for 15 minutes past reservation time</li>
                    <li>Cancellations must be made at least 2 hours in advance</li>
                    <li>For parties of 8 or more, please call us directly</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchSelect = document.querySelector('select[name="branch_id"]');
    const dateInput = document.querySelector('input[name="reservation_date"]');
    const timeSelect = document.querySelector('select[name="reservation_time"]');
    
    // Update available times based on date and branch selection
    function updateAvailableTimes() {
        const selectedBranch = branchSelect.value;
        const selectedDate = dateInput.value;
        
        if (selectedBranch && selectedDate) {
            // Here you would typically make an AJAX call to check availability
            // For now, we'll just show all times
            console.log('Checking availability for branch:', selectedBranch, 'on date:', selectedDate);
        }
    }
    
    branchSelect.addEventListener('change', updateAvailableTimes);
    dateInput.addEventListener('change', updateAvailableTimes);
    
    // Phone number formatting
    const phoneInput = document.querySelector('input[name="customer_phone"]');
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 10) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        }
        e.target.value = value;
    });
});
</script>
