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
//            $(this).removeClass("editable");
//            $(this).addClass("edited");
//           $(this).html('<div class="md-form">  <textarea id="form7" class="md-textarea form-control" rows="3">'+ $(this).text() + '</textarea></div>');            

//            $(this).html("<textarea>" + $(this).text() + "</textarea>");
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

/**
 * Update a track with search results
 * @param {type} trackid
 * @param {type} data
 * @param {type} status
 * @param {type} accuracy
 * @return {undefined}
 */
function UpdateTrackInformations(trackid, data, status, accuracy, app_id) {
//    $("tr[trackid='" + trackid + "'] td[id='accuracy']").html(accuracy);
    $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='accuracy']").data(accuracy);
    
    $("tr[trackid='" + trackid + "'] td[id='accuracy']").addClass("signal"+accuracy+"on6");
    $("tr[trackid='" + trackid + "'] td[id='accuracy']").addClass("signal");
    
    deezerlogo = '<img src="/img/favicon.png" width="16" height="16" alt="">';
    switch (status) {
        case 1:
            $("#DeezerImportTracksNumber").text(parseInt($("#DeezerImportTracksNumber").text(), 10) + 1);
            $("#ButtonImportToDeezer").removeClass("invisible");
            trackimage = data.album.cover;
            $("tr[trackid='" + trackid + "']").removeClass("deezererror");
            $("tr[trackid='" + trackid + "']").removeClass("deezerwarning");
            $("tr[trackid='" + trackid + "']").attr('deezerid', data.id);
            song = $("tr[trackid='" + trackid + "'] td[id='song'] .editable").text();
            album = $("tr[trackid='" + trackid + "'] td[id='album'] .editable").text();
            artist = $("tr[trackid='" + trackid + "'] td[id='artist'] .editable").text();
            //deezerplayerlink='<iframe scrolling="no" frameborder="0" allowTransparency="true" src="https://www.deezer.com/plugins/player?format=square&autoplay=false&playlist=false&width=200&height=200&color=ff0000&layout=dark&size=medium&type=tracks&id=' + data.id + '&app_id=' + app_id + '" width="200" height="200"></iframe>'
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='image']").data('<button type="button" class="btn btn-link"><a href="' + data.link + '" target="_blank" contenteditable="false"><img class="deezertrackimage" src="' + trackimage + '" alt=""></a></button>');
//            $("tr[trackid='" + trackid + "'] td[id='image']").html('<a href="' + data.link + '" target="_blank"><img class="deezertrackimage" src="' + trackimage + '" alt=""></a>');

            original_value = $("tr[trackid='" + trackid + "'] td[id='song']").attr('original_value');
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='song']").data('<span class="editable">' + song + '</span><br><button type="button" class="btn btn-link"><a href="' + data.link + '" target="_blank" contenteditable="false">' + deezerlogo + " " + data.title_short + '</a></button>');
//            $("tr[trackid='" + trackid + "'] td[id='song']").html('<span class="editable">' + song + '</span><br><a href="' + data.link + '" target="_blank">' + deezerlog + data.title_short + '</a>');
            $("tr[trackid='" + trackid + "'] td[id='song']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='song']").removeClass("edited");

            original_value = $("tr[trackid='" + trackid + "'] td[id='album']").attr('original_value');
            albumurl = data.album.tracklist.replace("api.deezer.com", "www.deezer.com").replace(/\/tracks$/, "");
            ;
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='album']").data('<span class="editable">' + album + '</span><br><button type="button" class="btn btn-link"><a href="' + albumurl + '" target="_blank" contenteditable="false">' + deezerlogo + " " + data.album.title + '</a></button>');
//            $("tr[trackid='" + trackid + "'] td[id='album']").html('<span class="editable">' + album + '</span><br><a href="' + data.album.tracklist + '" target="_blank">' + deezerlog + data.album.title + '</a>');
            $("tr[trackid='" + trackid + "'] td[id='album']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='album']").removeClass("edited");

            original_value = $("tr[trackid='" + trackid + "'] td[id='artist']").attr('original_value');
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='artist']").data('<span class="editable">' + artist + '</span><br><button type="button" class="btn btn-link"><a href="' + data.artist.link + '" target="_blank" contenteditable="false">' + deezerlogo + " " + data.artist.name + '</a></button>');
//            $("tr[trackid='" + trackid + "'] td[id='artist']").html('<span class="editable">' + artist + '</span><br><a href="' + data.artist.link + '" target="_blank">' + deezerlog + data.artist.name + '</a>');
            $("tr[trackid='" + trackid + "'] td[id='artist']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='artist']").removeClass("edited");

            original_value = $("tr[trackid='" + trackid + "'] td[id='duration']").attr('original_value');
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='duration']").data(SecondsToHms(data.duration))
            //            $("tr[trackid='" + trackid + "'] td[id='duration']").html(SecondsToHms(data.duration));
            $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration', data.duration * 1000);
            $("tr[trackid='" + trackid + "'] td[id='duration']").addClass("text-primary");
            if (accuracy > 2) {
                progressvalue = parseInt($("#deezerrecognationsucess").attr('aria-valuenow'), 10) + 1;
                progresstotal = parseInt($("#deezerrecognationsucess").attr('aria-valuemax'), 10);

                $("#deezerrecognationsucess").attr('aria-valuenow', progressvalue);
                progressstyle = "width: " + Math.round(progressvalue / progresstotal * 10000)/100 + "%";
                $("#deezerrecognationsucess").attr('style', progressstyle);
            } else {
                $("tr[trackid='" + trackid + "']").addClass("deezerwarning");
                progressvalue = parseInt($("#deezerrecognationwarning").attr('aria-valuenow'), 10) + 1;
                progresstotal = parseInt($("#deezerrecognationwarning").attr('aria-valuemax'), 10);

                $("#deezerrecognationwarning").attr('aria-valuenow', progressvalue);
                progressstyle = "width: " + Math.round(progressvalue / progresstotal * 10000)/100 + "%";
                $("#deezerrecognationwarning").attr('style', progressstyle);
            }

            break;
        default:
            $("tr[trackid='" + trackid + "']").addClass("deezererror");
            progressvalue = parseInt($("#deezerrecognationerror").attr('aria-valuenow'), 10) + 1;
            progresstotal = parseInt($("#deezerrecognationerror").attr('aria-valuemax'), 10);

            $("#deezerrecognationerror").attr('aria-valuenow', progressvalue);
            progressstyle = "width: " + Math.round(progressvalue / progresstotal * 10000)/100 + "%";
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
//        if (!(typeof deezerid === "undefined" || deezerid === false || deezerid === "undefined")) {
        if (deezerid !== 'null') {
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
function DeezerLookup(trackid, artist, album, song, duration, app_id) {
    var formdata = new FormData();
    formdata.append('trackid', trackid);
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
                UpdateTrackInformations(trackid, null, 0, 0, null);
            } else {

                if (!("info" in postdata)) {
                    UpdateTrackInformations(trackid, null, 0, 0, postdata.app_id);

                } else {
                    if (postdata.info.data.length === 0 || postdata.info.total === 0) {
                        UpdateTrackInformations(trackid, null, 0, 0, postdata.app_id);
                    } else {
//                        console.log(postdata);
                        //UpdateTrackInformations(DeezersearchResults[i].trackid, DeezersearchResults[i].info.data[0], 1);
                        UpdateTrackInformations(trackid, postdata.info.data[0], 1, postdata.accuracy, postdata.app_id);
                    }

                }
            }
        },
        error: function (e) {
            console.log("Timefor track : " + trackid + " " + artist + " " + album + " " + song + " " + duration);
            UpdateTrackInformations(trackid, null, 0, 0, null);
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
            UpdateTrackInformations(DeezersearchResults[i].trackid, null, 0, 0);
        } else {
            UpdateTrackInformations(DeezersearchResults[i].trackid, DeezersearchResults[i].info.data[0], 1, DeezersearchResults[i].accuracy);
        }
    }
    $("#SearchOnDeezerButtonWheel").addClass("invisible");
}

function CheckSearchStatusList() {
    $.get('/deezer/searchlist.json', function (data) {
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
//            window.setInterval(function () {
//                CheckSearchStatusList();
//            }, 1000);
        },
        success: function (postdata) {
//            clearInterval();
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
        song = $("tr[trackid='" + trackid + "'] td[id='song'] .editable").text();
        album = $("tr[trackid='" + trackid + "'] td[id='album'] .editable").text();
        artist = $("tr[trackid='" + trackid + "'] td[id='artist'] .editable").text();
        duration = $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration');
        var deezerid = $(this).parent().attr("deezerid");//  $("tr[trackid='" + trackid + "']").attr("deezerid");
        // For some browsers, `attr` is undefined; for others,
        // `attr` is false.  Check for both.
//        if ((typeof deezerid === "undefined" || deezerid === false)) {
//        console.log(deezerid);
        if (deezerid !== 'null') {
            $("tr[trackid='" + trackid + "'] td[id='accuracy']").html('<span id="SearchOnDeezerButtonWheel" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
//            console.log("Searchgin for : " + trackid + " " + artist + " " + album + " " + song + " " + duration)
            DeezerLookup(trackid, artist, album, song, duration);
//            }
//            track = {"trackid": trackid, "song": song, "album": album, "artist": artist, "duration": duration}
//            list[trackid] = track;
        }
    });

//    DeezerLookupList(list);
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
    song = $("tr[trackid='" + trackid + "'] td[id='song'] .editable").text();
    album = $("tr[trackid='" + trackid + "'] td[id='album'] .editable").text();
    artist = $("tr[trackid='" + trackid + "'] td[id='artist'] .editable").text();
    duration = $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration');
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