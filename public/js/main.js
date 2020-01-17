var Catalog = function () {

    var handler_click_ShowPlaylist = function () {
        $('body').on('click', '#ShowPlaylist', function () {
            $('#songlist').empty();
            playlistid = $(this).attr('playlistid'); //.split('_')[1];
//            console.log("Should display playlist : " + playlistid);
            $.get('/playlist/' + playlistid + '.html', function (data) {
                $('#songlist').html(data);
            });
        });
    };
    var handler_click_ModifiyPlaylist = function () {
        $('body').on('click', '.editable', function () {
//            console.log("Should modify this");
            $(this).removeClass("editable");
            $(this).addClass("edited");
            $(this).html("<textarea>" + $(this).text() + "</textarea>");
        });
    };
    var handler_click_ImportToDeezerDropDownSelection = function () {
        $('body').on('click', '#dropdownMenuPlaylistItems>a.dropdown-item', function () {
            $('#dropdownMenuPlaylist').html($(this).text());
            playlistid = $(this).attr('playlistid');
            $('#dropdownMenuPlaylist').attr('playlistid', playlistid);
            if (playlistid === '0') {
                $('#InputGroupDeezerPlaylistName').removeClass('invisible');
//                console.log("Should make input field visible");
            } else {
                $('#InputGroupDeezerPlaylistName').addClass('invisible');
//                console.log("Should make input field invisible");
            }
        });
    };
    var handler_select_iTunes_File = function () {
        $('input[type="file"]').change(function (e) {
            var filename = $('input[type=file]').val().replace(/C:\\fakepath\\/i, '');
            $('#itunesfilename').text(filename);
        });
    };
    return {
        init: function () {
            handler_click_ShowPlaylist();
            handler_click_ModifiyPlaylist();
            handler_click_ImportToDeezerDropDownSelection();
            handler_select_iTunes_File();
        }
    };
}();
$(document).ready(function () {
    Catalog.init();
});



function UpdateTrackInformations(trackid, data, status) {
    switch (status) {
        case 1:
            trackimage = data.album.cover;
            $("tr[trackid='" + trackid + "']").removeClass("deezererror");
            $("tr[trackid='" + trackid + "']").attr('deezerid', data.id);
            $("tr[trackid='" + trackid + "'] td[id='image']").html('<a href="' + data.link + '" ><img src="' + trackimage + '" alt=""></a>');
            $("tr[trackid='" + trackid + "'] td[id='song']").html('<a href="' + data.link + '" >' + data.title_short + '</a>');
            $("tr[trackid='" + trackid + "'] td[id='song']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='song']").removeClass("edited");
            $("tr[trackid='" + trackid + "'] td[id='album']").html('<a href="' + data.album.tracklist + '" >' + data.album.title + '</a>');
            $("tr[trackid='" + trackid + "'] td[id='album']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='album']").removeClass("edited");
            $("tr[trackid='" + trackid + "'] td[id='artist']").html('<a href="' + data.artist.link + '" >' + data.artist.name + '</a>');
            $("tr[trackid='" + trackid + "'] td[id='artist']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='artist']").removeClass("edited");
            $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration', data.duration * 1000);
            $("tr[trackid='" + trackid + "'] td[id='duration']").html(SecondsToHms(data.duration));
            $("tr[trackid='" + trackid + "'] td[id='duration']").addClass("text-primary");
            progressvalue = $("#deezerrecognationsucess").attr('aria-valuenow');
            progresstotal = $("#deezerrecognationsucess").attr('aria-valuemax');
            progressvalue++;
            $("#deezerrecognationsucess").attr('aria-valuenow', progressvalue);
            progressstyle = "width: " + Math.trunc(progressvalue / progresstotal * 100) + "%";
            $("#deezerrecognationsucess").attr('style', progressstyle);
            break;
        default:
            $("tr[trackid='" + trackid + "']").addClass("deezererror");
            progressvalue = $("#deezerrecognationerror").attr('aria-valuenow');
            progresstotal = $("#deezerrecognationerror").attr('aria-valuemax');
            progressvalue++;
            $("#deezerrecognationerror").attr('aria-valuenow', progressvalue);
            progressstyle = "width: " + Math.trunc(progressvalue / progresstotal * 100) + "%";
            $("#deezerrecognationerror").attr('style', progressstyle);
            break;

    }

}


function ImportPlaylist(tracklist, playlistid) {
    var formdata = new FormData();
    formdata.append('tracklist', JSON.stringify(tracklist));
    $.ajax({
        type: 'post',
        enctype: 'multipart/form-data',
        data: formdata,
        url: "/deezer/playlist/" + playlistid + "/addsongs",
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        beforeSend: function () {
        },
        success: function (postdata) {
            if (postdata.success === false) {
                console.log("No data recieved");
            } else {

                if (postdata.lenght === 0) {
                    console.log("No data recieved");
                } else {
                    console.log("Should be OK");

                }

            }

        },
        error: function (e) {
            console.log("ERROR : ", e);
        }
    });
}

/**
 * Import to Deezer from the Modal dialog
 * @return {undefined}
 */
function ImportToDeezer() {
    $('#DeezerImport_modal').modal('hide');
    tracklist = [];
    $("table > tbody  > tr").each(function () {
        deezerid = $(this).attr('deezerid');
        if (!(typeof deezerid === "undefined" || deezerid === false || deezerid === "undefined")) {
            tracklist.push(deezerid);
        }
    });
    console.log(tracklist);

    playlistid = $('#dropdownMenuPlaylist').attr('playlistid');
    if (playlistid === '0') {
        CreatePlaylistAndImport(tracklist);
    } else {
        ImportPlaylist(tracklist, playlistid);
    }
}


/**
 * Filter Tracks in error in the table
 * @return {undefined}
 */
function OnlyShowErrors() {
    $("table > tbody  > tr").each(function () {
        trackid = $(this).attr('trackid');
        if ($("#ShowErrors").is(":checked")) {
            if (!$("tr[trackid='" + trackid + "']").hasClass('deezererror')) {
                $("tr[trackid='" + trackid + "']").addClass('collapse');
            }
        } else {
            $("tr[trackid='" + trackid + "']").removeClass('collapse');
        }
    });
}

/**
 * Convert a duration from seconds to HMS
 * @param {type} d
 * @return {undefined|String}
 */
function SecondsToHms(d) {
    d = Number(d);
    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    var s = Math.floor(d % 3600 % 60);
    var hDisplay = h > 0 ? h + (h === 1 ? ":" : ":") : "";
    var mDisplay = m > 0 ? m + (m === 1 ? ":" : ":") : "";
    var sDisplay = s > 0 ? s + (s === 1 ? "" : "") : "";
    return hDisplay + mDisplay + sDisplay;
}



/**
 * Do the Deezer lookup on the backend
 * @param {type} trackid
 * @param {type} artist
 * @param {type} album
 * @param {type} song
 * @param {type} duration
 * @return {undefined}
 */
function DeezerLookup(trackid, artist, album, song, duration) {
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

                if (!("data" in postdata)) {
                    UpdateTrackInformations(trackid, null, 0);

                } else {
                    if (postdata.data.length === 0 || postdata.total === 0) {
                        UpdateTrackInformations(trackid, null, 0);
                    } else {
                        UpdateTrackInformations(trackid, postdata.data[0], 1);
                    }

                }
            }
        },
        error: function (e) {
//                    console.log("ERROR : ", e);
        }
    });
}


/**
 * Parse data returned after the Lookup and update the table
 * @param {type} DeezersearchResults
 * @return {undefined}
 */
function UpdateTrackListView(DeezersearchResults) {
    for (var i = 0; i < DeezersearchResults.length; i++) {
        if (DeezersearchResults[i].info.total === 0) {
            UpdateTrackInformations(DeezersearchResults[i].trackid, null, 0);
        } else {
            UpdateTrackInformations(DeezersearchResults[i].trackid, DeezersearchResults[i].info.data[0], 1);
        }
    }
    $("#SearchOnDeezerButtonWheel").addClass("invisible");
}

function CheckSearchStatusList() {
    $.get('/deezer/searchlist.json',function(data){
        console.log(data);
    });
}

/**
 * Search in Deezer for the tracklist passed in parameters
 * @param {type} list
 * @return {undefined}
 */
function DeezerLookupList(tracklist) {
    var formdata = new FormData();
    formdata.append('tracklist', JSON.stringify(tracklist));
    $("#SearchOnDeezerButtonWheel").removeClass("invisible");


    $.ajax({
        type: 'post',
        enctype: 'multipart/form-data',
        data: formdata,
        url: "/deezer/searchlist.json",
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        beforeSend: function () {
            window.setInterval(function () {
                CheckSearchStatusList();
            }, 1000);
        },
        success: function (postdata) {
            clearInterval();
            if (postdata.success === false) {
                console.log("No data recieved");
            } else {

                if (postdata.length === 0 || postdata.total === 0) {
                    console.log("No data recieved");
                } else {
                    console.log("Should be OK");
                    console.log(postdata);
                    UpdateTrackListView(postdata);
                }

            }

        },
        error: function (e) {
            console.log("ERROR : ", e);
        }
    });
}
/***
 * Trigger to do a Full lookup on all entries in the table
 * @return {undefined}
 */
function SearchOnDeezer() {
    list = {};
    $("table > tbody  > tr").each(function () {
        trackid = $(this).attr('trackid');

        song = $("tr[trackid='" + trackid + "'] td[id='song']").text();
        album = $("tr[trackid='" + trackid + "'] td[id='album']").text();
        artist = $("tr[trackid='" + trackid + "'] td[id='artist']").text();
        duration = $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration');
        var deezerid = $("tr[trackid='" + trackid + "']").attr("deezerid");
        // For some browsers, `attr` is undefined; for others,
        // `attr` is false.  Check for both.
        if ((typeof deezerid === "undefined" || deezerid === false)) {
            // DeezerLookup(trackid, artist, album, song, duration);
            track = {"trackid": trackid, "song": song, "album": album, "artist": artist, "duration": duration}
            list[trackid] = track;
        }
    });

    DeezerLookupList(list);
}


/**
 * Single click on deezer refresh icon
 * @param {type} trackid
 * @param {type} artist
 * @param {type} album
 * @param {type} song
 * @param {type} duration
 * @return {undefined}
 */
function RefreshDeezer(trackid, artist, album, song, duration) {
    DeezerLookup(trackid, artist, album, song, duration);
}

/**
 * Request a playlist creation on Deezer
 * and import the travlist passed in parameter
 * @return the created playlist id
 */
function CreatePlaylistAndImport(tracklist) {

    playlistid = $('#dropdownMenuPlaylist').attr('playlistid');
    if (playlistid === '0') {
        name = $('#InputDeezerPlaylistName').val();
    } else {
        name = $('#deezer_playlist_'.playlistid).attr('playlistname');
    }
    var formdata = new FormData();
    formdata.append('name', name);
    formdata.append('public', 'public');
    formdata.append('tracklist', tracklist)

    $.ajax({
        type: 'post',
        enctype: 'multipart/form-data',
        data: formdata,
        url: "/deezer/me/createplaylist",
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        beforeSend: function () {
        },
        success: function (postdata) {
            if (postdata.success === false) {
                console.log("No data recieved");
            } else {

                if (postdata.lenght === 0) {
                    console.log("No data recieved");
                } else {
                    playlistid = postdata.id;
                    console.log("Should be OK. Playlist created under ID " + playlistid);
                    ImportPlaylist(tracklist, playlistid);
                    console.log(tracklist + " data should be imported to " + playlistid);
//                    return postdata.id;
                }

            }

        },
        error: function (e) {
            console.log("ERROR : ", e);
        }
    });
}