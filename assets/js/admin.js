// Confirmation dialog for delete actions
function confirmDelete(eventId, message = 'Are you sure you want to delete this event? This action cannot be undone.') {
    if (confirm(message)) {
        // Get the current path to determine if we're in admin or not
        const currentPath = window.location.pathname;
        const basePath = currentPath.includes('/admin/') ? '' : '../admin/';
        window.location.href = basePath + 'delete-event.php?id=' + eventId;
    }
    return false;
}

// Generic confirmation dialog
function confirmAction(message, actionUrl) {
    if (confirm(message)) {
        window.location.href = actionUrl;
    }
    return false;
} 