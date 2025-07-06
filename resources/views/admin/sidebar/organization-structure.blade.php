{{-- Super Admin Sidebar - Organization Structure --}}
@if(auth('admin')->user()?->isSuperAdmin())
<div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Organization Management</div>

<!-- Organizations List -->
<div class="space-y-2">
    @foreach(\App\Models\Organization::with(['subscriptionPlan', 'branches.kitchenStations', 'admins'])->get() as $org)
    <div class="border-l-2 {{ $org->is_active ? 'border-green-500' : 'border-red-500' }} pl-3">
        <!-- Organization Header -->
        <div class="flex items-center justify-between group">
            <a href="{{ route('admin.organizations.show', $org) }}" class="flex items-center space-x-2 text-sm hover:text-blue-600 transition-colors">
                <i class="fas fa-building {{ $org->is_active ? 'text-green-500' : 'text-red-500' }}"></i>
                <span class="font-medium">{{ Str::limit($org->name, 20) }}</span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                    {{ $org->subscriptionPlan?->name ?? 'No Plan' }}
                </span>
            </a>
            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                <span class="text-xs {{ $org->is_active ? 'text-green-600' : 'text-red-600' }}">
                    {{ $org->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        <!-- Organization Stats -->
        <div class="ml-6 text-xs text-gray-500 mt-1 grid grid-cols-3 gap-2">
            <div class="flex items-center">
                <i class="fas fa-store mr-1"></i>
                <span>{{ $org->branches->count() }} Branches</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-utensils mr-1"></i>
                <span>{{ $org->branches->sum(fn($b) => $b->kitchenStations->count()) }} Kitchens</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-users mr-1"></i>
                <span>{{ $org->admins->count() }} Admins</span>
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
        <div class="ml-6 mt-2 flex space-x-2">
            @if(!$org->is_active)
            <form action="{{ route('admin.organizations.activate', $org) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 transition-colors">
                    Activate
                </button>
            </form>
            @endif
            <a href="{{ route('admin.organizations.show', $org) }}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition-colors">
                View Details
            </a>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4 px-4">
    <a href="{{ route('admin.organizations.create') }}" class="flex items-center justify-center w-full text-sm bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
        <i class="fas fa-plus mr-2"></i>
        Add Organization
    </a>
</div>
@endif
