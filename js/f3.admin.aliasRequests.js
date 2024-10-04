$(document).ready(function() {
    document.querySelectorAll('input[type="submit"]').forEach(function(button) {
        button.addEventListener('click', function(event) {
            document.getElementById('action').value = button.getAttribute('data-action');
            document.getElementById('memberId').value = button.getAttribute('data-primary-id');
            document.getElementById('associatedMemberId').value = button.getAttribute('data-alias-id');
        });
    });
});