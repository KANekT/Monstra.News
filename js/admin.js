/**
 * Created 13.07.13 18:57.
 * User: KANekT
 */
$(function() {
    $("span[data-action=aai]").click(function() {
        $.get('index.php?id=news&image=true&csrf=' + $("#csrf").val(), function(data) {
            $(data).appendTo('.img-upload');
        });
    });

    $("a[data-action=delImg]").click(function() {
        var key = $(this).attr("data-key");
        var dir = $(this).attr("data-dir");
        var csrf = $("#csrf").val();
        $.post('index.php?id=news&delete=image', {id: key, dir: dir, csrf: csrf}, function(data) {
        });
    });

    $("a[data-action=deleteNews]").click(function() {
        var text = $(this).attr('data-confirm');
        if (confirm(text))
        {
            var csrf = $('#csrf').val();
            var items = new Array();
            $('input[name="key"]:checked').each(function() {items.push($(this).val());});

            if (items.length > 0) {
                $.ajax({
                    url: 'index.php?id=news&delete=item&t=' + $.now(),
                    type: "POST",
                    data: {csrf: csrf, items: items},
                    success: function (text) {
                        window.location = 'index.php?id=news';
                    },
                    error: function (jqXhr, textStatus, errorThrown) {
                        alert("Error '" + jqXhr.status + "' (textStatus: '" + textStatus + "', errorThrown: '" + errorThrown + "')");
                    }
                });
            }
        }
        return false;
    });

    $("input[data-action=checked]").click(function() {
        if($(this).prop('checked')){
            $('input[name="key"]').prop('checked',true);
        } else {
            $('input[name="key"]').prop('checked',false);
        }
    });

    $('input[name="key"]').click(function() {
        var rel = $(this).parents("tr").find("td:eq(0) a").attr('rel');
        if (rel != undefined)
        {
            if ($(this).prop('checked')) {
                $('tr[rel="children_'+rel+'"] input[name="key"]').prop('checked',true);
           } else {
                $('tr[rel="children_'+rel+'"] input[name="key"]').prop('checked',false);
           }
        }
    });

});