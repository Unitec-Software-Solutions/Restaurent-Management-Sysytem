// Role Management JavaScript

function loadRoleTemplate(templateName, permissions) {
    // Uncheck all permissions first
    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });

    // Check the permissions from the template
    permissions.forEach(permission => {
        const checkbox = document.querySelector(`input[name="permissions[]"][value="${permission}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });

    // Update template indicator
    document.getElementById('selectedTemplate').textContent = templateName;

    // Optional: Scroll to permissions section
    document.getElementById('permissionsSection').scrollIntoView({ behavior: 'smooth' });
}

function applyRoleTemplate(templateKey) {
    // Get the template data from the server
    fetch(`/admin/roles/template/${templateKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.permissions) {
                // Uncheck all existing permissions
                document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                    checkbox.checked = false;
                });

                // Check the permissions from the template
                data.permissions.forEach(permission => {
                    const checkbox = document.querySelector(`input[name="permissions[]"][value="${permission}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                // Show success message
                showNotification('Template applied successfully', 'success');
            }
        })
        .catch(error => {
            console.error('Error applying template:', error);
            showNotification('Error applying template', 'error');
        });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        'bg-blue-500'
    } text-white`;
    notification.textContent = message;

    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Organization/Branch dynamic loading
function updateBranchOptions() {
    const organizationId = document.getElementById('organization_id').value;
    const branchSelect = document.getElementById('branch_id');

    if (!branchSelect) return;

    // Clear current options
    branchSelect.innerHTML = '<option value="">Organization-wide</option>';

    if (organizationId) {
        fetch(`/admin/organizations/${organizationId}/branches`)
            .then(response => response.json())
            .then(branches => {
                branches.forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch.id;
                    option.textContent = branch.name;
                    branchSelect.appendChild(option);
                });
            });
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    const organizationSelect = document.getElementById('organization_id');
    if (organizationSelect) {
        organizationSelect.addEventListener('change', updateBranchOptions);
    }
});
