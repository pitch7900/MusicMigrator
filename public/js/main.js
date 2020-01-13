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

            $(this).html("<textarea>" + $(this).html() + "</textarea>");

        });
    };
    return {
        init: function () {
            handler_click_ShowPlaylist();
            handler_click_ModifiyPlaylist();

        }
    };
}();

function secondsToHms(d) {
    d = Number(d);
    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    var s = Math.floor(d % 3600 % 60);

    var hDisplay = h > 0 ? h + (h === 1 ? ":" : ":") : "";
    var mDisplay = m > 0 ? m + (m === 1 ? ":" : ":") : "";
    var sDisplay = s > 0 ? s + (s === 1 ? "" : "") : "";
    return hDisplay + mDisplay + sDisplay;
}

$(document).ready(function () {
    Catalog.init();
});

var counter = 0;

function deezerlookup(trackid, artist, album, song, duration) {
//    console.log("Searching on Deezer for : " + trackid + " " + artist + " " + album + " " + song + " " + duration);

    var formdata = new FormData();
    formdata.append('artist', artist);
    formdata.append('album', album);
    formdata.append('song', song);
    formdata.append('duration', duration);

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
//                console.log(postdata);
                if (!("data" in postdata)) {
//                    console.log("No data found for trackid : "+trackid);
                    $("tr[trackid='" + trackid + "']").addClass("deezererror");
                    progressvalue = $("#deezerrecognationerror").attr('aria-valuenow');
                    progresstotal = $("#deezerrecognationerror").attr('aria-valuemax');
                    progressvalue++;
                    $("#deezerrecognationerror").attr('aria-valuenow', progressvalue);
                    progressstyle = "width: " + Math.trunc(progressvalue / progresstotal * 100) + "%";
                    $("#deezerrecognationerror").attr('style', progressstyle);
                } else {
                    if (postdata.data.lenght === 0) {
                        ("tr[trackid='" + trackid + "']").addClass("deezererror");
                        progressvalue = $("#deezerrecognationerror").attr('aria-valuenow');
                        progresstotal = $("#deezerrecognationerror").attr('aria-valuemax');
                        progressvalue++;
                        $("#deezerrecognationerror").attr('aria-valuenow', progressvalue);
                        progressstyle = "width: " + Math.trunc(progressvalue / progresstotal * 100) + "%";
                        $("#deezerrecognationerror").attr('style', progressstyle);
                    } else {
                        trackimage = postdata.data[0].album.cover;
                        $("tr[trackid='" + trackid + "']").removeClass("deezererror");
                        $("tr[trackid='" + trackid + "']").attr('deezerid', postdata.data[0].id);
                        $("tr[trackid='" + trackid + "'] td[id='image']").html('<a href="' + postdata.data[0].link + '" ><img src="' + trackimage + '" alt=""></a>');
                        $("tr[trackid='" + trackid + "'] td[id='song']").html('<a href="' + postdata.data[0].link + '" >' + postdata.data[0].title_short + '</a>');
                        $("tr[trackid='" + trackid + "'] td[id='song']").addClass("editable");
                        $("tr[trackid='" + trackid + "'] td[id='song']").removeClass("edited");
                        $("tr[trackid='" + trackid + "'] td[id='album']").html('<a href="' + postdata.data[0].album.tracklist + '" >' + postdata.data[0].album.title + '</a>');
                        $("tr[trackid='" + trackid + "'] td[id='album']").addClass("editable");
                        $("tr[trackid='" + trackid + "'] td[id='album']").removeClass("edited");
                        $("tr[trackid='" + trackid + "'] td[id='artist']").html('<a href="' + postdata.data[0].artist.link + '" >' + postdata.data[0].artist.name + '</a>');
                        $("tr[trackid='" + trackid + "'] td[id='artist']").addClass("editable");
                        $("tr[trackid='" + trackid + "'] td[id='artist']").removeClass("edited");
                        $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration', postdata.data[0].duration * 1000);
                        $("tr[trackid='" + trackid + "'] td[id='duration']").html(secondsToHms(postdata.data[0].duration));
                        $("tr[trackid='" + trackid + "'] td[id='duration']").addClass("text-primary");
                        progressvalue = $("#deezerrecognationsucess").attr('aria-valuenow');
                        progresstotal = $("#deezerrecognationsucess").attr('aria-valuemax');
                        progressvalue++;
                        $("#deezerrecognationsucess").attr('aria-valuenow', progressvalue);
                        progressstyle = "width: " + Math.trunc(progressvalue / progresstotal * 100) + "%";
                        $("#deezerrecognationsucess").attr('style', progressstyle);
                    }

                }
            }
        },
        error: function (e) {
//                    console.log("ERROR : ", e);
        }
    });
}

function SearchOnDeezer() {

    $("table > tbody  > tr").each(function (trackid) {
        song = $("tr[trackid='" + trackid + "'] td[id='song']").text();

        album = $("tr[trackid='" + trackid + "'] td[id='album']").text();

        artist = $("tr[trackid='" + trackid + "'] td[id='artist']").text();
        duration = $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration');

        var deezerid = $("tr[trackid='" + trackid + "']").attr("deezerid");
//        console.log(typeof deezerid);
        // For some browsers, `attr` is undefined; for others,
        // `attr` is false.  Check for both.
        if (typeof deezerid !== "undefined" || deezerid !== false) {
            deezerlookup(trackid, artist, album, song, duration);
        } else {
//            console.log(song + " " + album + " " + artist + " " + duration + " already searched");
        }



    });
}



function refreshDeezer(trackid, artist, album, song, duration) {
    deezerlookup(trackid, artist, album, song, duration);
}