{{-- Super Admin Sidebar - Organization Structure --}}
@if(auth('admin')->user()?->isSuperAdmin())
<div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-200">
    <i class="fas fa-building mr-2"></i>Organization Management
</div>

<!-- Organizations List -->
<div class="space-y-3 max-h-96 overflow-y-auto">
    @foreach(\App\Models\Organization::with(['subscriptionPlan', 'branches.kitchenStations', 'admins'])->get() as $org)
    <div class="border-l-4 {{ $org->is_active ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }} pl-3 py-2 mx-2 rounded-r-lg">
        <!-- Organization Header -->
        <div class="flex items-center justify-between group">
            <a href="{{ route('admin.organizations.show', $org) }}" class="flex items-center space-x-2 text-sm hover:text-blue-600 transition-colors flex-1">
                <i class="fas fa-building {{ $org->is_active ? 'text-green-500' : 'text-red-500' }}"></i>
                <span class="font-medium">{{ Str::limit($org->name, 20) }}</span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                    {{ $org->subscriptionPlan?->name ?? 'No Plan' }}
                </span>
            </a>
            <div class="flex items-center space-x-2">
                <span class="text-xs px-2 py-1 rounded {{ $org->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $org->is_active ? 'Active' : 'Inactive' }}
                </span>
                @if($org->activation_key)
                <i class="fas fa-key text-xs text-yellow-500" title="Has activation key"></i>
                @endif
            </div>
        </div>

        <!-- Organization Stats -->
        <div class="ml-6 text-xs text-gray-500 mt-1 grid grid-cols-3 gap-2">
            <div class="flex items-center" title="{{ $org->branches->count() }} branches in this organization">
                <i class="fas fa-store mr-1"></i>
                <span>{{ $org->branches->count() }} Branch{{ $org->branches->count() !== 1 ? 'es' : '' }}</span>
            </div>
            <div class="flex items-center" title="{{ $org->branches->sum(fn($b) => $b->kitchenStations->count()) }} kitchen stations across all branches">
                <i class="fas fa-utensils mr-1"></i>
                <span>{{ $org->branches->sum(fn($b) => $b->kitchenStations->count()) }} Kitchen{{ $org->branches->sum(fn($b) => $b->kitchenStations->count()) !== 1 ? 's' : '' }}</span>
            </div>
            <div class="flex items-center" title="{{ $org->admins->count() }} administrators in this organization">
                <i class="fas fa-users mr-1"></i>
                <span>{{ $org->admins->count() }} Admin{{ $org->admins->count() !== 1 ? 's' : '' }}</span>
            </div>
        </div>

        <!-- Head Office Details -->
        @php
            $headOffice = $org->branches->where('is_head_office', true)->first();
        @endphp
        @if($headOffice)
        <div class="ml-6 mt-2 space-y-1">
            <div class="flex items-center justify-between text-xs">
                <a href="#" onclick="showBranchDetails('{{ $headOffice->id }}')" class="flex items-center space-x-1 text-blue-600 hover:text-blue-800">
                    <i class="fas fa-home"></i>
                    <span>Head Office</span>
                </a>
                <span class="text-gray-500">{{ $headOffice->is_active ? 'Active' : 'Inactive' }}</span>
            </div>

            <!-- Kitchen Stations -->
            @if($headOffice->kitchenStations->count() > 0)
            <div class="ml-4 space-y-1">
                <div class="text-xs font-medium text-gray-600">Kitchen Stations:</div>
                @foreach($headOffice->kitchenStations->take(2) as $station)
                <div class="flex items-center justify-between text-xs ml-2">
                    <a href="#" onclick="showKitchenDetails('{{ $station->id }}')" class="flex items-center space-x-1 text-purple-600 hover:text-purple-800">
                        <i class="fas fa-fire text-orange-500"></i>
                        <span>{{ Str::limit($station->name, 15) }}</span>
                    </a>
                    <span class="text-gray-500">{{ $station->type }}</span>
                </div>
                @endforeach
                @if($headOffice->kitchenStations->count() > 2)
                <div class="text-xs text-gray-400 ml-2">
                    +{{ $headOffice->kitchenStations->count() - 2 }} more...
                </div>
                @endif
            </div>
            @endif

            <!-- Organization Admin -->
            @php
                $orgAdmin = $org->admins->where('is_super_admin', false)->first();
            @endphp
            @if($orgAdmin)
            <div class="ml-4 text-xs">
                <div class="flex items-center space-x-1 text-indigo-600">
                    <i class="fas fa-user-tie"></i>
                    <span class="font-medium">Admin:</span>
                    <a href="#" onclick="showAdminDetails('{{ $orgAdmin->id }}')" class="hover:underline">
                        {{ $orgAdmin->name }}
                    </a>
                </div>
                <div class="text-gray-500 ml-4">{{ $orgAdmin->email }}</div>
            </div>
            @endif
        </div>
        @endif

        <!-- Branch List (excluding head office) -->
        @php
            $otherBranches = $org->branches->where('is_head_office', false);
        @endphp
        @if($otherBranches->count() > 0)
        <div class="ml-6 mt-2">
            <div class="text-xs font-medium text-gray-600 mb-1">Other Branches:</div>
            @foreach($otherBranches->take(3) as $branch)
            <div class="ml-2 flex items-center justify-between text-xs">
                <a href="#" onclick="showBranchDetails('{{ $branch->id }}')" class="flex items-center space-x-1 text-green-600 hover:text-green-800">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ Str::limit($branch->name, 18) }}</span>
                </a>
                <span class="text-gray-500">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            @endforeach
            @if($otherBranches->count() > 3)
            <div class="text-xs text-gray-400 ml-2">
                +{{ $otherBranches->count() - 3 }} more branches...
            </div>
            @endif
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="ml-6 mt-2 flex flex-wrap gap-1">
            @if(!$org->is_active)
            <a href="{{ route('admin.organizations.activate.form', $org) }}" 
               class="inline-flex items-center text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105" 
               title="Activate this organization">
                <i class="fas fa-power-off mr-1"></i>
                <span>Activate</span>
            </a>
            @else
            <a href="{{ route('admin.organizations.activate.form', $org) }}" 
               class="inline-flex items-center text-xs bg-amber-500 text-white px-2 py-1 rounded hover:bg-amber-600 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105" 
               title="Manage activation settings">
                <i class="fas fa-cogs mr-1"></i>
                <span>Manage</span>
            </a>
            @endif
            
            <a href="{{ route('admin.organizations.show', $org) }}" 
               class="inline-flex items-center text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105" 
               title="View organization details">
                <i class="fas fa-eye mr-1"></i>
                <span>View</span>
            </a>
            
            @if(auth('admin')->user()->isSuperAdmin())
            <a href="{{ route('admin.organizations.edit', $org) }}" 
               class="inline-flex items-center text-xs bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105" 
               title="Edit organization">
                <i class="fas fa-edit mr-1"></i>
                <span>Edit</span>
            </a>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4 px-4 border-t border-gray-200 pt-4 space-y-2">
    <!-- Quick Stats Summary -->
    @php
        $totalOrgs = \App\Models\Organization::count();
        $activeOrgs = \App\Models\Organization::where('is_active', true)->count();
        $inactiveOrgs = $totalOrgs - $activeOrgs;
    @endphp
    <div class="grid grid-cols-3 gap-2 text-xs mb-3">
        <div class="text-center">
            <div class="font-semibold text-gray-700">{{ $totalOrgs }}</div>
            <div class="text-gray-500">Total</div>
        </div>
        <div class="text-center">
            <div class="font-semibold text-green-600">{{ $activeOrgs }}</div>
            <div class="text-gray-500">Active</div>
        </div>
        <div class="text-center">
            <div class="font-semibold text-red-600">{{ $inactiveOrgs }}</div>
            <div class="text-gray-500">Inactive</div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="space-y-2">
        <a href="{{ route('admin.organizations.create') }}" 
           class="flex items-center justify-center w-full text-sm bg-gradient-to-r from-indigo-600 to-blue-600 text-white py-2 px-4 rounded-lg hover:from-indigo-700 hover:to-blue-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
            <i class="fas fa-plus mr-2"></i>
            Add Organization
        </a>
        
        <a href="{{ route('admin.organizations.activation.index') }}" 
           class="flex items-center justify-center w-full text-sm bg-gradient-to-r from-green-600 to-emerald-600 text-white py-2 px-4 rounded-lg hover:from-green-700 hover:to-emerald-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
            <i class="fas fa-key mr-2"></i>
            Activation Center
        </a>
    </div>
</div>
@endif
