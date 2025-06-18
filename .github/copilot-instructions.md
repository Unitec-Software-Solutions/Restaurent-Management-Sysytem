Here's a universal UI/UX guide for all application components, including GTN views (show, index, edit, create) and global patterns for forms, dashboards, and modals:

### **Global UI Patterns** (Apply to all views)

**1. Layout Principles**
- **Card-based containers**: White cards with subtle shadows (`shadow-sm`), rounded corners (`rounded-lg`), and consistent padding
- **Responsive grid**: Use Tailwind's grid system with breakpoints:
  ```html
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  ```
- **Section hierarchy**: 
  - Header (title + description)
  - Content area (form/data)
  - Action footer (buttons)

**2. Typography System**
```html
<h1 class="text-2xl font-bold text-gray-900">Main Title</h1>
<h2 class="text-xl font-semibold text-gray-800">Section Header</h2>
<h3 class="text-lg font-medium text-gray-700">Sub-header</h3>
<p class="text-gray-600">Body text</p>
<p class="text-sm text-gray-500">Supporting text</p>
```

**3. Color Palette**
| Purpose       | Color Class          | Use Case                      |
|---------------|----------------------|-------------------------------|
| Primary       | `bg-indigo-600`      | Main buttons, active elements |
| Success       | `bg-green-600`       | Positive actions, approvals   |
| Warning       | `bg-yellow-500`      | Caution states                |
| Danger        | `bg-red-600`         | Destructive actions           |
| Info          | `bg-blue-600`        | Informational elements        |
| Disabled      | `bg-gray-300`        | Inactive elements             |

**4. Form Standards**
```html
<div class="mb-4">
  <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
  <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
  <p class="text-xs text-gray-500 mt-1">Helper text</p>
</div>
```

**5. Button System**
```html
<!-- Primary -->
<button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
  <i class="fas fa-plus mr-2"></i> Create
</button>

<!-- Secondary -->
<button class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">

<!-- Danger -->
<button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">

<!-- Disabled -->
<button class="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed">
```

**6. Status Indicators**
```html
<span class="px-2 py-1 text-xs font-semibold rounded-full 
  {{ $status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
  {{ $status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
  {{ $status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
  {{ ucfirst($status) }}
</span>
```

---

### **Component-Specific Guidelines**

**1. Dashboard Views** (`dashboard.blade.php`)
```markdown
**Layout:**
- 3-column grid for summary cards
- Charts/visualizations top-center
- Recent activity feed on right sidebar

**Key Components:**
- Metric cards (small, medium, large variants)
- Interactive charts (Chart.js)
- Quick-action buttons
- Notification panels
```

**2. List/Index Views** (`index.blade.php`)
```markdown
**Structure:**
1. Title + Create button header
2. Filter controls (collapsible on mobile)
3. Data table with pagination
4. Export/action toolbar

**Table Features:**
- Zebra striping (`:nth-child(even)`)
- Hover states on rows
- Responsive stacking on mobile
- Action dropdown menus
```

**3. Detail Views** (`show.blade.php`)
```markdown
**Layout:**
- Header section (title + status badge)
- 3-column content grid:
  Left: Primary content
  Middle: Related items
  Right: Stats/actions
- Tabbed sections for complex data

**Components:**
- Attribute-value pairs in definition lists
- Timeline for audit history
- Related records cards
- Action button cluster
```

**4. Form Views** (`create.blade.php`, `edit.blade.php`)
```markdown
**Structure:**
1. Form title + back button
2. Error summary (top-aligned)
3. Sections with progressive disclosure
4. Save/cancel button group

**Best Practices:**
- Group related fields
- Use cards for distinct sections
- Disable submit during processing
- Smart default values
```

**5. Modals**
```html
<div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
  <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-2xl">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Modal Title</h3>
      <button class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <!-- Content -->
    
    <div class="flex justify-end gap-3 mt-6">
      <button class="secondary-button">Cancel</button>
      <button class="primary-button">Confirm</button>
    </div>
  </div>
</div>
```

---

### **Universal Interactive Elements**

**1. Validation Patterns**
```javascript
// Real-time validation
inputs.forEach(input => {
  input.addEventListener('blur', validateField);
});

// Consolidated error display
@if ($errors->any())
  <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
    <h3 class="font-medium mb-2">Validation Errors</h3>
    <ul class="list-disc pl-5">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
```

**2. Data Loading States**
```html
<div class="animate-pulse">
  <div class="h-4 bg-gray-200 rounded mb-3"></div>
  <div class="h-4 bg-gray-200 rounded w-5/6"></div>
</div>
```

**3. Empty States**
```html
<div class="text-center py-12">
  <div class="text-gray-400 text-5xl mb-4">
    <i class="fas fa-inbox"></i>
  </div>
  <h3 class="text-lg font-medium text-gray-900 mb-1">No records found</h3>
  <p class="text-gray-500 max-w-md mx-auto">
    Get started by creating a new item.
  </p>
  <div class="mt-6">
    <button class="primary-button">
      <i class="fas fa-plus mr-2"></i> Create New
    </button>
  </div>
</div>
```

**4. Notification System**
```javascript
// Success toast
showNotification('success', 'Changes saved successfully');

// Error toast
showNotification('error', 'Failed to save data');
```

**5. Responsive Adaptations**
```css
/* Mobile-first adaptations */
@media (max-width: 768px) {
  .card-grid {
    grid-template-columns: 1fr;
  }
  
  .action-bar {
    flex-direction: column;
    gap: 0.5rem;
  }
}
```

**6. Accessibility Standards**
- Semantic HTML tags
- ARIA roles for interactive elements
- Keyboard navigation support
- Color contrast compliance
- Screen reader-friendly alerts

---

### **Implementation Notes**

1. **Component Library**: Create reusable Blade components for:
   - `<x-card>`, `<x-data-table>`, `<x-form-section>`
   - `<x-status-badge>`, `<x-loading-spinner>`
   - `<x-primary-button>`, `<x-modal>`

2. **Icon System**: Use Font Awesome with consistent icon-class mapping:
   - Create: `fa-plus`
   - Edit: `fa-edit`
   - Delete: `fa-trash`
   - View: `fa-eye`
   - Navigation: `fa-arrow-left`

3. **Animation Guidelines**:
   - Page transitions: Fade 300ms
   - Modal entrance: Slide-down 250ms
   - Hover effects: Smooth color transitions

4. **Dark Mode Support**:
   ```html
   <html class="light-theme">
   <!-- Add dark mode toggle -->
   <button id="theme-toggle">
     <i class="fas fa-moon"></i>
   </button>
   ```
   Use CSS variables for theme colors

This comprehensive guide ensures UI consistency across all application components while providing flexibility for component-specific requirements. The patterns balance aesthetic appeal with functional efficiency, following modern UX best practices.
