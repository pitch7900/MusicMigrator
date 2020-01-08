var Catalog = function () {

    var handler_click_ShowPlaylist = function () {
        $('body').on('click', '#ShowPlaylist', function () {
            $('#songlist').empty();
            playlistid = $(this).attr('playlistid');//.split('_')[1];
            console.log("Should display playlist : " + playlistid);
            $.get('/playlist/' + playlistid + '.html', function (data) {
                $('#songlist').html(data);
            });
        });
    };
var handler_click_ModifiyPlaylist = function () {
        $('body').on('click', '.editable', function () {
            console.log("Should modify this");
            $(this).removeClass("editable");
            $(this).addClass("edited");
            
               $(this).html("<textarea>"+$(this).html()+"</textarea>");
            
        });
    };
    return {
        init: function () {
            handler_click_ShowPlaylist();
            handler_click_ModifiyPlaylist();
        }
    };
}();


$(document).ready(function () {
    Catalog.init();
});

function SearchOnDeezer() {
    console.log("Searching on Deezer");
}

function refreshDeezer(trackid, artist, album, song) {
    console.log("Searching on Deezer for : " + trackid + " " + artist + " " + album + " " + song);
    var formdata = new FormData();
    formdata.append('artist', artist);
    formdata.append('album', album);
    formdata.append('song', song);
    $.ajax({
        type: 'post',
        enctype: 'multipart/form-data',
        data: formdata,
        url: "/deezer/search.json",
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        beforeSend: function () {
        },
        success: function (postdata) {
            if (postdata.success === false) {
//                        console.log("NOK");
            } else {
                console.log(postdata);
                
                trackimage=postdata.data[0].album.cover;
                $("tr[trackid='"+trackid+"'] td[id='image']").html('<a href="'+postdata.data[0].link+'" ><img src="'+trackimage+'" alt=""></a>');
                $("tr[trackid='"+trackid+"'] td[id='song']").html('<a href="'+postdata.data[0].link+'" >'+postdata.data[0].title_short+'</a>');
                $("tr[trackid='"+trackid+"'] td[id='song']").addClass("editable");
                $("tr[trackid='"+trackid+"'] td[id='song']").removeClass("edited");
                $("tr[trackid='"+trackid+"'] td[id='album']").html('<a href="'+postdata.data[0].tracklist+'" >'+postdata.data[0].album.title+'</a>' );
                $("tr[trackid='"+trackid+"'] td[id='album']").addClass("editable");
                $("tr[trackid='"+trackid+"'] td[id='album']").removeClass("edited");
                $("tr[trackid='"+trackid+"'] td[id='artist']").html('<a href="'+postdata.data[0].artist.link+'" >'+postdata.data[0].artist.name+'</a>');
                $("tr[trackid='"+trackid+"'] td[id='artist']").addClass("editable");
                $("tr[trackid='"+trackid+"'] td[id='artist']").removeClass("edited");
//                $("tr[trackid='"+trackid+"'] td[id='refresh']").html("");
            }
        },
        error: function (e) {
//                    console.log("ERROR : ", e);
        }
    });
}