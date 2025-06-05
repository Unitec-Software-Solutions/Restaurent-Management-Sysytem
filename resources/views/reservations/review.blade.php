<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Reservation | TableEase</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#4f46e5',
                        light: '#f0f9ff',
                        dark: '#1e293b'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: 0, transform: 'translateY(10px)' },
                            '100%': { opacity: 1, transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
        }
        
        .reservation-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .reservation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 15px 10px -6px rgba(0, 0, 0, 0.05);
        }
        
        .info-card {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }
        
        .info-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        
        .gradient-header {
            background: linear-gradient(90deg, #dbeafe 0%, #e0e7ff 100%);
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #2563eb 0%, #4f46e5 100%);
            transition: all 0.3s ease;
            border-radius: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(90deg, #1d4ed8 0%, #4338ca 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }
        
        .btn-outline {
            border: 1px solid #cbd5e1;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            border-color: #94a3b8;
            background-color: #f8fafc;
        }
        
        .guest-badge {
            background-color: #e0e7ff;
            color: #4f46e5;
            transition: all 0.2s ease;
        }
        
        .guest-badge:hover {
            transform: scale(1.05);
        }
        
        .date-badge {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body class="py-8 px-4">
    <div class="max-w-4xl mx-auto animate-fade-in">
        <!-- Header Card -->
        <div class="bg-white reservation-card mb-8">
            <div class="gradient-header px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
                <div class="flex items-center mb-3 sm:mb-0">
                    <div class="bg-blue-100 p-2 rounded-lg mr-4">
                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Review Your Reservation</h1>
                        <p class="text-sm text-gray-600">Please verify your details before confirming</p>
                    </div>
                </div>
                <span class="date-badge px-3 py-1 rounded-full text-sm font-medium text-gray-700">
                    <i class="far fa-calendar-alt mr-1 text-blue-500"></i>
                    <span>{{ \Carbon\Carbon::parse($reservation->date)->format('d-m-Y') }}</span>
                </span>
            </div>

            <!-- Content -->
            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Customer Information -->
                <div class="space-y-6">
                    <div class="info-card bg-white p-5">
                        <h2 class="flex items-center text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-user-circle text-blue-500 mr-2"></i>
                            Customer Information
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Name</p>
                                <p class="font-medium text-gray-800 mt-1">{{ $reservation->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</p>
                                <p class="font-medium text-gray-800 mt-1">{{ $reservation->email ?: 'Not provided' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</p>
                                <p class="font-medium text-gray-800 mt-1">{{ $reservation->phone }}</p>
                            </div>
                        </div>
                    </div>

                    @if($reservation->comments)
                    <div class="info-card bg-white p-5">
                        <h2 class="flex items-center text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-comment-dots text-blue-500 mr-2"></i>
                            Special Requests
                        </h2>
                        <p class="text-gray-700">{{ $reservation->comments }}</p>
                    </div>
                    @endif
                </div>

                <!-- Reservation Details -->
                <div class="space-y-6">
                    <div class="info-card bg-white p-5">
                        <h2 class="flex items-center text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-store text-blue-500 mr-2"></i>
                            Reservation Details
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</p>
                                <p class="font-medium text-gray-800 mt-1">{{ $branch->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date</p>
                                <p class="font-medium text-gray-800 mt-1">{{ \Carbon\Carbon::parse($reservation->date)->format('F j, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Time</p>
                                <p class="font-medium text-gray-800 mt-1">
                                    {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Number of Guests</p>
                                <div class="mt-1">
                                    <span class="guest-badge inline-block px-3 py-1 rounded-full text-sm font-medium">
                                        {{ $reservation->number_of_people }} guests
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-card bg-blue-50 p-5 border border-blue-100">
                        <h2 class="flex items-center text-lg font-semibold text-gray-800 mb-3">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            Important Information
                        </h2>
                        <ul class="text-sm text-gray-700 space-y-2">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-xs"></i>
                                <span>Please arrive 10 minutes before your reservation time</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-xs"></i>
                                <span>Table will be held for 15 minutes past reservation time</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-xs"></i>
                                <span>Call us at (555) 987-6543 if you're running late</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-xs"></i>
                                <span>Cancellations must be made at least 24 hours in advance</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <form method="POST" action="{{ route('reservations.store') }}" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="name" value="{{ $reservation->name }}">
                    <input type="hidden" name="email" value="{{ $reservation->email }}">
                    <input type="hidden" name="phone" value="{{ $reservation->phone }}">
                    <input type="hidden" name="branch_id" value="{{ $reservation->branch_id }}">
                    <input type="hidden" name="date" value="{{ $reservation->date }}">
                    <input type="hidden" name="start_time" value="{{ $reservation->start_time }}">
                    <input type="hidden" name="end_time" value="{{ $reservation->end_time }}">
                    <input type="hidden" name="number_of_people" value="{{ $reservation->number_of_people }}">
                    <input type="hidden" name="comments" value="{{ $reservation->comments }}">
                    
                    <button type="submit" class="btn-primary w-full text-white font-semibold py-3 px-6 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Confirm Reservation
                    </button>
                </form>
                
                <a href="{{ route('reservations.create', [
                    'name' => $reservation->name,
                    'email' => $reservation->email,
                    'branch_id' => $reservation->branch_id,
                    'phone' => $reservation->phone,
                    'date' => $reservation->date,
                    'start_time' => $reservation->start_time,
                    'end_time' => $reservation->end_time,
                    'number_of_people' => $reservation->number_of_people,
                    'comments' => $reservation->comments,
                    'edit_mode' => true
                ]) }}" class="btn-outline text-gray-600 hover:text-gray-800 font-medium py-3 px-6 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pen mr-2"></i>
                    Edit Details
                </a>
            </div>
        </div>
        
        <!-- Summary Page (Hidden until confirmation) -->
        <div id="summaryPage" class="hidden">
            <div class="bg-white reservation-card">
                <div class="gradient-header px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div class="flex items-center mb-3 sm:mb-0">
                        <div class="bg-green-100 p-2 rounded-lg mr-4">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Reservation Confirmed!</h1>
                            <p class="text-sm text-gray-600">Thank you for choosing TableEase</p>
                        </div>
                    </div>
                    <span class="date-badge px-3 py-1 rounded-full text-sm font-medium text-gray-700">
                        <i class="fas fa-ticket-alt mr-1 text-green-500"></i>
                        <span>Reservation #: R-864259</span>
                    </span>
                </div>

                <!-- Summary Content -->
                <div class="px-6 py-8">
                    <div class="max-w-2xl mx-auto">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-50 mb-6">
                                <i class="fas fa-check text-green-500 text-4xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">Your reservation is confirmed!</h2>
                            <p class="text-gray-600">A confirmation email has been sent to <span class="font-medium">{{ $reservation->email ?: $reservation->phone }}</span></p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">Reservation Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Restaurant</p>
                                        <p class="font-medium text-gray-800 mt-1">{{ $branch->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</p>
                                        <p class="font-medium text-gray-800 mt-1">
                                            {{ \Carbon\Carbon::parse($reservation->date)->format('F j, Y') }} at {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation ID</p>
                                        <p class="font-medium text-gray-800 mt-1">R-864259</p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Guests</p>
                                        <p class="font-medium text-gray-800 mt-1">{{ $reservation->number_of_people }} people</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Name</p>
                                        <p class="font-medium text-gray-800 mt-1">{{ $reservation->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</p>
                                        <p class="font-medium text-gray-800 mt-1">{{ $reservation->phone }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 mb-8">
                            <h3 class="flex items-center text-lg font-semibold text-gray-800 mb-3">
                                <i class="fas fa-star text-yellow-500 mr-2"></i>
                                Make Your Evening Special
                            </h3>
                            <ul class="text-sm text-gray-700 space-y-3">
                                <li class="flex items-start">
                                    <i class="fas fa-wine-glass-alt text-blue-500 mt-1 mr-3"></i>
                                    <span>Pre-order a bottle of wine for your table and receive 10% off</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-birthday-cake text-blue-500 mt-1 mr-3"></i>
                                    <span>Celebrating a special occasion? Let us know for a complimentary dessert</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-parking text-blue-500 mt-1 mr-3"></i>
                                    <span>Complimentary valet parking available for all guests</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="#" class="btn-outline text-gray-600 hover:text-gray-800 font-medium py-3 px-6 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Add to Calendar
                            </a>
                            <a href="{{ route('home') }}" class="btn-primary text-white font-semibold py-3 px-6 rounded-lg flex items-center justify-center">
                                <i class="fas fa-home mr-2"></i>
                                Return to Homepage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmBtn = document.getElementById('confirmBtn');
            const summaryPage = document.getElementById('summaryPage');
            const reservationCard = document.querySelector('.reservation-card');
            
            // This script would need to be updated to work with the form submission
            // Currently it's just for the demo animation
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Show loading state on button
                    const submitBtn = confirmBtn.querySelector('button');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                    submitBtn.disabled = true;
                    
                    // Simulate processing delay
                    setTimeout(() => {
                        // Hide the reservation card and show the summary page
                        reservationCard.classList.add('hidden');
                        summaryPage.classList.remove('hidden');
                        summaryPage.classList.add('animate-fade-in');
                        
                        // Scroll to the top of the summary page
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }, 1500);
                });
            }
        });
    </script>
</body>
</html>