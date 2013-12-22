/**
 * Created 13.07.13 22:43.
 * User: KANekT
 */
$(function() {
    $(".fileupload-preview > a").each(function() {
        $('.fileupload').addClass('fileupload-exists').removeClass('fileupload-new');
    });
});