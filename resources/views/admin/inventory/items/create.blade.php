@extends('layouts.admin')
@section('header-title', 'Add Inventory Items')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Add New Items</h2>
                    <p class="text-sm text-gray-500">Create multiple inventory items at once</p>
                    <p class="text-sm text-gray-500 mt-1">
                        @if (Auth::guard('admin')->user()->is_super_admin)
                            Organization: All Organizations (Super Admin)
                        @elseif(Auth::guard('admin')->user()->organization)
                            Organization: {{ Auth::guard('admin')->user()->organization->name }}
                        @else
                            Organization: Not Assigned
                        @endif
                    </p>
                </div>

                <a href="{{ route('admin.inventory.items.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Items
                </a>
            </div>

            <!-- Form Container -->
            <form id="items-form" action="{{ route('admin.inventory.items.store') }}" method="POST" class="p-6">
                @csrf

                <!-- Organization Selection for Super Admin -->
                @if (Auth::guard('admin')->user()->is_super_admin)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-building text-blue-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-blue-900">Organization Selection</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Target Organization <span class="text-red-500">*</span>
                                </label>
                                <select name="organization_id" id="organization_id" required
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Organization</option>
                                    @foreach ($organizations as $org)
                                        <option value="{{ $org->id }}"
                                            {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('organization_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <div class="text-sm text-blue-700">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Items will be created for the selected organization
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Display current organization for non-super admins -->
                    <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-building text-gray-600 mr-2"></i>
                            <div>
                                <h3 class="text-sm font-medium text-gray-700">Organization</h3>
                                <p class="text-gray-900 font-semibold">
                                    {{ Auth::guard('admin')->user()->organization->name }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div id="items-container">
                    <!-- Initial item form -->
                    @include('admin.inventory.items.partials.item-form', ['index' => 0])
                </div>

                <div class="mt-6">
                    <button type="button" id="add-item"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Another Item
                    </button>
                </div>

                <div class="flex justify-end space-x-3 mt-8">
                    <button type="reset"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <i class="fas fa-save mr-2"></i> Save All Items
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemCount = 1;
            const itemsContainer = document.getElementById('items-container');
            const addItemBtn = document.getElementById('add-item');
            const organizationSelect = document.getElementById('organization_id');
            const isSuperAdmin = {{ Auth::guard('admin')->user()->is_super_admin ? 'true' : 'false' }};

            // Super admin organization selection handler
            if (isSuperAdmin && organizationSelect) {
                organizationSelect.addEventListener('change', function() {
                    const selectedOrgId = this.value;
                    updateCategoriesForOrganization(selectedOrgId);
                });
            }

            // Update categories based on organization selection
            async function updateCategoriesForOrganization(orgId) {
                if (!orgId) {
                    // Clear all category dropdowns
                    document.querySelectorAll('.item-category').forEach(select => {
                        select.innerHTML = '<option value="">Select Category</option>';
                        const helpText = select.parentNode.querySelector('.category-help-text');
                        if (helpText) {
                            helpText.textContent = 'Categories will load after selecting organization';
                            helpText.className = 'text-xs text-gray-500 mt-1 category-help-text';
                        }
                    });
                    return;
                }

                try {
                    // Show loading state
                    document.querySelectorAll('.item-category').forEach(select => {
                        select.innerHTML = '<option value="">Loading categories...</option>';
                        select.disabled = true;
                        const helpText = select.parentNode.querySelector('.category-help-text');
                        if (helpText) {
                            helpText.textContent = 'Loading categories...';
                            helpText.className = 'text-xs text-blue-600 mt-1 category-help-text';
                        }
                    });

                    // Fetch categories for the selected organization
                    const response = await fetch(`/admin/api/organizations/${orgId}/categories`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || ''
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({}));
                        throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`);
                    }

                    const categories = await response.json();

                    // Update all category dropdowns
                    document.querySelectorAll('.item-category').forEach(select => {
                        const currentValue = select.value;
                        select.innerHTML = '<option value="">Select Category</option>';
                        select.disabled = false;

                        if (Array.isArray(categories) && categories.length > 0) {
                            categories.forEach(category => {
                                const option = new Option(category.name, category.id);
                                if (category.id == currentValue) {
                                    option.selected = true;
                                }
                                select.appendChild(option);
                            });

                            const helpText = select.parentNode.querySelector('.category-help-text');
                            if (helpText) {
                                helpText.textContent = `${categories.length} categories loaded`;
                                helpText.className = 'text-xs text-green-600 mt-1 category-help-text';
                            }
                        } else {
                            const option = new Option('No categories available', '');
                            option.disabled = true;
                            select.appendChild(option);

                            const helpText = select.parentNode.querySelector('.category-help-text');
                            if (helpText) {
                                helpText.textContent = 'No categories found for this organization';
                                helpText.className = 'text-xs text-yellow-600 mt-1 category-help-text';
                            }
                        }
                    });

                } catch (error) {
                    console.error('Error fetching categories:', error);

                    // Show error state
                    document.querySelectorAll('.item-category').forEach(select => {
                        select.innerHTML = '<option value="">Error loading categories</option>';
                        select.disabled = true;
                        const helpText = select.parentNode.querySelector('.category-help-text');
                        if (helpText) {
                            helpText.textContent = `Failed to load categories: ${error.message}`;
                            helpText.className = 'text-xs text-red-600 mt-1 category-help-text';
                        }
                    });

                    // Show user-friendly error message
                    alert(
                        `Failed to load categories for the selected organization.\n\nError: ${error.message}\n\nPlease try selecting the organization again or contact support if the problem persists.`
                        );
                }
            }

            // Add new item form
            addItemBtn.addEventListener('click', async function() {
                // For super admin, ensure organization is selected before adding items
                if (isSuperAdmin && organizationSelect && !organizationSelect.value) {
                    alert('Please select an organization first');
                    organizationSelect.focus();
                    return;
                }

                try {
                    const response = await fetch(
                        `/admin/inventory/items/create-template/${itemCount}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'text/html',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.getAttribute('content') || ''
                            },
                            credentials: 'same-origin'
                        });

                    if (!response.ok) {
                        throw new Error(`Failed to fetch item form template: HTTP ${response.status}`);
                    }

                    const html = await response.text();
                    itemsContainer.insertAdjacentHTML('beforeend', html);

                    // If super admin and organization is selected, update categories for new form
                    if (isSuperAdmin && organizationSelect && organizationSelect.value) {
                        await updateCategoriesForOrganization(organizationSelect.value);
                    }

                    // Enable remove buttons if more than one item exists
                    const removeButtons = document.querySelectorAll('.remove-item');
                    if (removeButtons.length > 1) {
                        removeButtons.forEach(btn => btn.classList.remove('hidden'));
                    }

                    itemCount++;
                } catch (error) {
                    console.error('Error adding item form:', error);
                    alert(`Failed to add new item form: ${error.message}\n\nPlease try again.`);
                }
            });

            // Remove item form
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item')) {
                    e.preventDefault();
                    const itemSection = e.target.closest('.item-section');
                    if (itemSection) {
                        itemSection.remove();

                        // Hide remove button if only one item remains
                        const removeButtons = document.querySelectorAll('.remove-item');
                        if (removeButtons.length <= 1) {
                            removeButtons.forEach(btn => btn.classList.add('hidden'));
                        }
                    }
                }
            });

            // Handle menu item checkbox change
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('menu-item-checkbox')) {
                    const index = e.target.dataset.index;
                    const menuAttributesSection = document.querySelector(
                        `.menu-attributes[data-index="${index}"]`);

                    if (e.target.checked) {
                        menuAttributesSection.classList.remove('hidden');
                        // Make menu attributes required
                        setMenuAttributesRequired(index, true);
                    } else {
                        menuAttributesSection.classList.add('hidden');
                        // Remove required status and clear menu attributes when unchecked
                        setMenuAttributesRequired(index, false);
                        clearMenuAttributes(index);
                    }

                    // Update the attributes field
                    updateMenuAttributesField(index);
                }
            });

            // Handle menu attribute field changes
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('menu-attribute-field')) {
                    const menuAttrSection = e.target.closest('.menu-attributes');
                    if (menuAttrSection) {
                        const index = menuAttrSection.dataset.index;
                        updateMenuAttributesField(index);
                    }
                }
            });

            // Handle menu attribute checkbox changes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('menu-attribute-field') && e.target.type === 'checkbox') {
                    const menuAttrSection = e.target.closest('.menu-attributes');
                    if (menuAttrSection) {
                        const index = menuAttrSection.dataset.index;
                        updateMenuAttributesField(index);
                    }
                }
            });

            // Update menu attributes field
            function updateMenuAttributesField(index) {
                const menuAttrSection = document.querySelector(`.menu-attributes[data-index="${index}"]`);
                const attrField = document.querySelector(`input[name="items[${index}][attributes]"]`);
                const isMenuItemChecked = document.querySelector(`#menuitem-${index}`).checked;

                if (!menuAttrSection || !attrField) return;

                let attributes = {};

                // Get existing category attributes if any
                try {
                    attributes = JSON.parse(attrField.value || '{}');
                } catch (e) {
                    attributes = {};
                }

                if (isMenuItemChecked) {
                    // Add menu-specific attributes
                    const menuFields = menuAttrSection.querySelectorAll('.menu-attribute-field');
                    menuFields.forEach(field => {
                        const key = field.dataset.menuAttr;
                        if (field.type === 'checkbox') {
                            attributes[key] = field.checked;
                        } else {
                            attributes[key] = field.value;
                        }
                    });
                } else {
                    // Remove menu-specific attributes
                    const menuAttrKeys = [
                        'cuisine_type', 'spice_level', 'prep_time_minutes', 'serving_size',
                        'dietary_type', 'availability', 'main_ingredients', 'allergen_info',
                        'is_chefs_special', 'is_popular'
                    ];
                    menuAttrKeys.forEach(key => {
                        delete attributes[key];
                    });
                }

                attrField.value = JSON.stringify(attributes);
            }

            // Clear menu attributes
            function clearMenuAttributes(index) {
                const menuAttrSection = document.querySelector(`.menu-attributes[data-index="${index}"]`);
                if (!menuAttrSection) return;

                // Clear all menu attribute fields
                menuAttrSection.querySelectorAll('.menu-attribute-field').forEach(field => {
                    if (field.type === 'checkbox') {
                        field.checked = false;
                    } else {
                        field.value = '';
                    }
                });
            }

            // Set menu attributes as required or optional
            function setMenuAttributesRequired(index, required) {
                const menuAttrSection = document.querySelector(`.menu-attributes[data-index="${index}"]`);
                if (!menuAttrSection) return;

                // Core menu attributes that should be required
                const requiredMenuFields = [
                    'cuisine_type', 'prep_time_minutes', 'serving_size'
                ];

                menuAttrSection.querySelectorAll('.menu-attribute-field').forEach(field => {
                    const attrName = field.dataset.menuAttr;
                    if (!attrName) return;

                    // Update required status for key menu attributes
                    if (requiredMenuFields.includes(attrName)) {
                        if (required) {
                            field.setAttribute('required', 'required');
                            // Add visual indicator
                            const label = field.closest('div').querySelector('label');
                            if (label && !label.textContent.includes('*')) {
                                label.innerHTML = label.innerHTML + ' <span class="text-red-500">*</span>';
                            }
                        } else {
                            field.removeAttribute('required');
                            // Remove visual indicator
                            const label = field.closest('div').querySelector('label');
                            if (label && label.textContent.includes('*')) {
                                label.innerHTML = label.innerHTML.replace(
                                    ' <span class="text-red-500">*</span>', '');
                            }
                        }
                    }
                });
            }

            // Handle category-specific attributes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-category')) {
                    const index = e.target.dataset.index;
                    const categoryId = e.target.value;
                    const attrContainer = document.querySelector(
                        `.category-attributes[data-index="${index}"]`);
                    const attrField = document.querySelector(`input[name="items[${index}][attributes]"]`);

                    if (!attrContainer) return;

                    // Clear existing attributes
                    attrContainer.innerHTML = '';
                    if (attrField) attrField.value = '';

                    // Add specific attributes based on category
                    if (categoryId == 1) { // Food category
                        attrContainer.innerHTML = `
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-lg font-semibold mb-3 text-gray-700">Food Attributes</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ingredients</label>
                                <input type="text" data-attr="ingredients"
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter ingredients">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Portion Size</label>
                                <input type="text" data-attr="portion_size"
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter portion size">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prep Time (mins)</label>
                                <input type="number" data-attr="prep_time"
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter prep time">
                            </div>
                        </div>
                    </div>`;
                    } else if (categoryId == 4) { // Equipment category
                        attrContainer.innerHTML = `
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-lg font-semibold mb-3 text-gray-700">Equipment Attributes</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <input type="text" data-attr="brand"
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter brand">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                                <input type="text" data-attr="model"
                                       class="attribute-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Enter model">
                            </div>
                        </div>
                    </div>`;
                    }

                    // Set up event listeners for new attribute fields
                    const attributeFields = attrContainer.querySelectorAll('.attribute-field');
                    attributeFields.forEach(field => {
                        field.addEventListener('input', function() {
                            updateAttributesField(index);
                        });
                    });
                }
            });

            // Update hidden attributes field (handles both category and menu attributes)
            function updateAttributesField(index) {
                const attrContainer = document.querySelector(`.category-attributes[data-index="${index}"]`);
                const attrField = document.querySelector(`input[name="items[${index}][attributes]"]`);

                if (!attrField) return;

                let attributes = {};

                // Get category-specific attributes
                if (attrContainer) {
                    const categoryInputs = attrContainer.querySelectorAll('.attribute-field');
                    categoryInputs.forEach(input => {
                        const key = input.dataset.attr;
                        if (key && input.value) {
                            attributes[key] = input.value;
                        }
                    });
                }

                // Get menu-specific attributes if menu item is checked
                const isMenuItemChecked = document.querySelector(`#menuitem-${index}`)?.checked;
                if (isMenuItemChecked) {
                    const menuAttrSection = document.querySelector(`.menu-attributes[data-index="${index}"]`);
                    if (menuAttrSection) {
                        const menuFields = menuAttrSection.querySelectorAll('.menu-attribute-field');
                        menuFields.forEach(field => {
                            const key = field.dataset.menuAttr;
                            if (key) {
                                if (field.type === 'checkbox') {
                                    attributes[key] = field.checked;
                                } else if (field.value) {
                                    attributes[key] = field.value;
                                }
                            }
                        });
                    }
                }

                attrField.value = JSON.stringify(attributes);
            }

            // Form submission handling with organization validation for super admin
            document.getElementById('items-form').addEventListener('submit', function(e) {
                // Super admin organization validation
                if (isSuperAdmin && organizationSelect && !organizationSelect.value) {
                    e.preventDefault();
                    alert('Please select an organization for the items');
                    organizationSelect.focus();
                    return;
                }

                // Validate at least one item exists
                const itemSections = document.querySelectorAll('.item-section');
                if (itemSections.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one item');
                    return;
                }

                // Validate menu items have required attributes
                let hasValidationErrors = false;
                const errorMessages = [];

                itemSections.forEach((section, sectionIndex) => {
                    const isMenuItemChecked = section.querySelector('.menu-item-checkbox')?.checked;

                    if (isMenuItemChecked) {
                        const menuAttrSection = section.querySelector('.menu-attributes');
                        const requiredFields = ['cuisine_type', 'prep_time_minutes',
                            'serving_size'
                        ];

                        requiredFields.forEach(fieldName => {
                            const field = menuAttrSection?.querySelector(
                                `[data-menu-attr="${fieldName}"]`);
                            if (!field || !field.value || field.value.trim() === '') {
                                hasValidationErrors = true;
                                const fieldLabel = {
                                    'cuisine_type': 'Cuisine Type',
                                    'prep_time_minutes': 'Preparation Time',
                                    'serving_size': 'Serving Size'
                                };
                                errorMessages.push(
                                    `Item #${sectionIndex + 1}: ${fieldLabel[fieldName]} is required for menu items`
                                );
                            }
                        });
                    }
                });

                if (hasValidationErrors) {
                    e.preventDefault();
                    alert('Please fix the following validation errors:\n\n' + errorMessages.join('\n'));
                    return;
                }

                // Update all attributes fields before submission
                document.querySelectorAll('.item-category').forEach(select => {
                    const index = select.dataset.index;
                    updateAttributesField(index);
                });

                // Also update menu attributes for all items
                document.querySelectorAll('.menu-item-checkbox').forEach(checkbox => {
                    const index = checkbox.dataset.index;
                    updateMenuAttributesField(index);
                });
            });

            // ...existing code...
        });
    </script>
@endsection
