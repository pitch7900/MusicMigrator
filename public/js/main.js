var Catalog = function () {

    var handler_click_ShowPlaylist = function () {
        $('body').on('click', '#ShowPlaylist', function () {
//        $('#ShowPlaylist').on('click', function () {
            $('#songlist').empty();
            playlistid = $(this).attr('playlistid');//.split('_')[1];
            console.log("Should display playlist : " + playlistid);
            $.get('/playlist/' + playlistid + '.html', function (data) {
                $('#songlist').html(data);
            });
        });
    };

    return {
        init: function () {
            handler_click_ShowPlaylist();
        }
    };
}();


$(document).ready(function () {
    Catalog.init();
});

function SearchOnDeezer(){
    console.log("Searching on Deezer");
}