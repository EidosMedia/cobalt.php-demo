$(document).ready(function() {
    var parsedUrl = window.location.href.match('[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{4}') || [];
    if (parsedUrl.length > 0) {
        $('#frmAddComment').attr('action', parsedUrl[0] + '/comments/add');
    }
});