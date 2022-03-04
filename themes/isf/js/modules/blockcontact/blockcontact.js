$(document).ready(function()
{
    $('#client').on('change', function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: baseUri + '?rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType: 'json',
            data: 'controller=changeclient&ajax=true'
                + '&client='
                + $(this).val()
                + '&token='+static_token
                + '&allow_refresh=1',
            success: function() {
                document.location.href = baseUri;
            }
        });
    });
});
