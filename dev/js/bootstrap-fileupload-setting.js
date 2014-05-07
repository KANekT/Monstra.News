/**
 * Created 13.07.13 22:43.
 * User: KANekT
 */
$(function() {
    $(".fileinput-preview > a").each(function() {
        $('.fileinput').addClass('fileinput-exists').removeClass('fileinput-new');
    });
});