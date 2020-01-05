var Catalog = function () {

    var handler_click_ShowPlaylist = function () {
        $('body').on('click', '[id^=ShowPlaylist_]', function () {
            $('#songlist').empty();
            playlistid = $(this).attr('id').split('_')[1];
            console.log("Should display playlist : " + playlistid);
            $.get('/playlist/' + playlistid + '.json', function (playlist) {
                for (const item of playlist) {
                    console.log(item.ID);
                    $.get('/playlist/song/'+item.ID+'.html', function (data){
                        $('#songlist').append(data);
                    });
                    console.log(item);
                }

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