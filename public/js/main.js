var searched = 0;
var total = 0;

var Catalog = function () {
    /**
     * Display the playsit choosen in the main right window
     * @return {undefined}
     */
    var handler_click_ShowPlaylist = function () {
        $('body').on('click', '#ShowPlaylist', function () {
            $('#songlist').empty();
            $(this).parents("li").parent().children().removeClass('selected');
            $(this).parents("li").parent().children().addClass('notselected');
            $(this).parent("li").addClass("selected");
            $(this).parent("li").removeClass("notselected");
            playlistid = $(this).attr('playlistid');
            //Display the spinner and then start the load of the playlist items
            $.get('/spinner.html', function (spinnerdata) {
                $('#songlist').html(spinnerdata);
                $.get('/playlist/' + playlistid + '.html', function (data) {
                    $('#songlist').html(data);
                });
            });
        });
    };

    /**
     * Decide to display an input boy named "#InputGroupDeezerPlaylistName" for giving 
     * a spefic name to the playsit imported
     * @return {undefined}
     */
    var handler_click_ImportToDeezerDropDownSelection = function () {
        $('body').on('click', '#dropdownMenuPlaylistItems>a.dropdown-item', function () {
            $('#dropdownMenuPlaylist').html($(this).text());
            playlistid = $(this).attr('playlistid');
            $('#dropdownMenuPlaylist').attr('playlistid', playlistid);
            if (playlistid === '0') {
                $('#InputGroupDeezerPlaylistName').removeClass('invisible');
            } else {
                $('#InputGroupDeezerPlaylistName').addClass('invisible');
            }
        });
    };
    /**
     * Allow to display properly the name of the iTunes library after upload
     * @return {undefined}
     */
    var handler_select_iTunes_File = function () {
        $('input[type="file"]').change(function (e) {
            var filename = $('input[type=file]').val().replace(/C:\\fakepath\\/i, '');
            $('#itunesfilename').text(filename);
        });
    };

    /**
     * Handle the click on remove a row
     * @return {undefined}
     */
    var handler_click_delete_row_on_table = function () {
        $('body').on('click', '.table-remove', function () {
            console.log("Should remove this line");
            console.log($(this).parents("#track").attr("trackid"));
            $(this).parents("#track").remove();
        });
    };
    /**
     * Check every minute if we're still have a valid Deezer Token.
     * Otherwise force a page refresh for login.
     * @return {undefined}
     */
    var checkLoginStatus = function () {

        window.setInterval(function () {
            $.ajax({
                type: 'get',
                url: '/deezer/me/about.json',
                cache: false,
                statusCode: {
                    401: function (response) {
                        //User is logged of
                        //force a refresh of the page, if we're not already on an error page
                        if ($("#ErrorPage").length!==0 ) {
                            window.location.reload();
                        }   
                    },
                    300: function (response) {
                        //User is logged of
                        //force a refresh of the page
                        window.location.reload();
                    }
                }, success: function () {

                }
            });


        }, 60000);
    };

    return {
        init: function () {
            handler_click_ShowPlaylist();
            handler_click_ImportToDeezerDropDownSelection();
            handler_select_iTunes_File();
            handler_click_delete_row_on_table();
            checkLoginStatus();
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
function UpdateTrackInformations(trackid, data, status, accuracy) {
    $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='accuracy']").data(accuracy);
    //update the accuracy cell with the detection value
    $("tr[trackid='" + trackid + "'] td[id='accuracy']").addClass("signal" + accuracy + "on6");
    $("tr[trackid='" + trackid + "'] td[id='accuracy']").addClass("signal");

    deezerlogo = '<img src="/img/favicon.png" width="16" height="16" alt="">';
    //Success - a track has been found
    switch (status) {
        case 1:
            $("#DeezerImportTracksNumber").text(parseInt($("#DeezerImportTracksNumber").text(), 10) + 1);
            $("#ButtonImportToDeezer").removeClass("invisible");
            trackimage = data.album.cover;
            //Remove previous search results
            $("tr[trackid='" + trackid + "']").removeClass("deezererror");
            $("tr[trackid='" + trackid + "']").removeClass("deezerwarning");
            //Add the deezer track id found
            $("tr[trackid='" + trackid + "']").attr('deezerid', data.id);
            //get the informations in the text cell that can be edited
            song = $("tr[trackid='" + trackid + "'] td[id='song'] .editable").text();
            album = $("tr[trackid='" + trackid + "'] td[id='album'] .editable").text();
            artist = $("tr[trackid='" + trackid + "'] td[id='artist'] .editable").text();
            //Update the image cell
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='image']").data('<button type="button" class="btn btn-link">' +
                    '<a href="' + data.link + '" target="_blank" contenteditable="false">' +
                    '<img class="deezertrackimage" src="' + trackimage + '" alt="">' +
                    '</a>' +
                    '</button>' +
                    '<span class="table-remove"><button type="button" class="btn btn-danger btn-rounded btn-sm my-0">Remove</button></span>');

            //Update the song name cell
            original_value = $("tr[trackid='" + trackid + "'] td[id='song']").attr('original_value');
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='song']").data('<span class="editable">' + song + '</span><br><button type="button" class="btn btn-link"><a href="' + data.link + '" target="_blank" contenteditable="false">' + deezerlogo + " " + data.title_short + '</a></button>');
            $("tr[trackid='" + trackid + "'] td[id='song']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='song']").removeClass("edited");
            //update the album name cell
            original_value = $("tr[trackid='" + trackid + "'] td[id='album']").attr('original_value');
            albumurl = data.album.tracklist.replace("api.deezer.com", "www.deezer.com").replace(/\/tracks$/, "");
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='album']").data('<span class="editable">' + album + '</span><br><button type="button" class="btn btn-link"><a href="' + albumurl + '" target="_blank" contenteditable="false">' + deezerlogo + " " + data.album.title + '</a></button>');
            $("tr[trackid='" + trackid + "'] td[id='album']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='album']").removeClass("edited");
            //Update the artist name cell
            original_value = $("tr[trackid='" + trackid + "'] td[id='artist']").attr('original_value');
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='artist']").data('<span class="editable">' + artist + '</span><br><button type="button" class="btn btn-link"><a href="' + data.artist.link + '" target="_blank" contenteditable="false">' + deezerlogo + " " + data.artist.name + '</a></button>');
            $("tr[trackid='" + trackid + "'] td[id='artist']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='artist']").removeClass("edited");
            //Update the track duration cell
            original_value = $("tr[trackid='" + trackid + "'] td[id='duration']").attr('original_value');
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='duration']").data(SecondsToHms(data.duration))
            $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration', data.duration * 1000);
            $("tr[trackid='" + trackid + "'] td[id='duration']").addClass("text-primary");
            //If accuracy score is strcily above 2 consider we have a pretty decent detection
            if (accuracy > 2) {
                progressvalue = parseInt($("#deezerrecognationsucess").attr('aria-valuenow'), 10) + 1;
                progresstotal = parseInt($("#deezerrecognationsucess").attr('aria-valuemax'), 10);

                $("#deezerrecognationsucess").attr('aria-valuenow', progressvalue);
                progressstyle = "width: " + Math.round(progressvalue / progresstotal * 10000) / 100 + "%";
                $("#deezerrecognationsucess").attr('style', progressstyle);
            } else {
                //Else, change the cell color to put a warning
                $("tr[trackid='" + trackid + "']").addClass("deezerwarning");
                progressvalue = parseInt($("#deezerrecognationwarning").attr('aria-valuenow'), 10) + 1;
                progresstotal = parseInt($("#deezerrecognationwarning").attr('aria-valuemax'), 10);

                $("#deezerrecognationwarning").attr('aria-valuenow', progressvalue);
                progressstyle = "width: " + Math.round(progressvalue / progresstotal * 10000) / 100 + "%";
                $("#deezerrecognationwarning").attr('style', progressstyle);
            }

            break;
        default:
            $("tr[trackid='" + trackid + "']").addClass("deezererror");
            progressvalue = parseInt($("#deezerrecognationerror").attr('aria-valuenow'), 10) + 1;
            progresstotal = parseInt($("#deezerrecognationerror").attr('aria-valuemax'), 10);
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='image']").data('<i class="far fa-question-circle"></i>' +
                    '</button>' +
                    '<span class="table-remove"><button type="button" class="btn btn-danger btn-rounded btn-sm my-0">Remove</button></span>');
            $("#deezerrecognationerror").attr('aria-valuenow', progressvalue);
            progressstyle = "width: " + Math.round(progressvalue / progresstotal * 10000) / 100 + "%";
            $("#deezerrecognationerror").attr('style', progressstyle);
            break;

    }
    //remove the spinner
    progressvalue = parseInt($("#deezerrecognationsucess").attr('aria-valuenow'), 10) + parseInt($("#deezerrecognationerror").attr('aria-valuenow'), 10) + parseInt($("#deezerrecognationwarning").attr('aria-valuenow'), 10);
    progresstotal = parseInt($("#deezerrecognationsucess").attr('aria-valuemax'), 10);
    if (progressvalue === progresstotal) {
        $("#spinnerplace").empty();
    }


}

/**
 * Import a playlist to deezer.
 * 
 * @param {array} tracklist
 * @param {string} Deezer playlist ID
 * @return {undefined}
 */
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
 * If the playlist already exist, then add track to the deezer playlist ID
 * Otherwise, Create the playlist first
 * @return {undefined}
 */
function ImportToDeezer() {
    $('#DeezerImport_modal').modal('hide');
    tracklist = [];
    $("table > tbody  > tr").each(function () {
        deezerid = $(this).attr('deezerid');
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
    
    var hDisplay = h > 0 ? ("0" + h + ":").slice(-2) : "";
    var mDisplay = m > 0 ? ("0" + m + ":" ).slice(-2) : "";
    var sDisplay =  ("0" + s ).slice(-2) ;
    return hDisplay + mDisplay + sDisplay;
}



/**
 * Do the Deezer lookup on the backend and call the function to update the table
 * @param {type} trackid
 * @param {type} artist
 * @param {type} album
 * @param {type} song
 * @param {type} duration
 * @return {undefined}
 */
function DeezerLookup(trackid, artist, album, song, duration) {
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
                UpdateTrackInformations(trackid, null, 0, 0);
            } else {

                if (!("info" in postdata)) {
                    UpdateTrackInformations(trackid, null, 0, 0);

                } else {
                    if (postdata.info.data.length === 0 || postdata.info.total === 0) {
                        UpdateTrackInformations(trackid, null, 0, 0);
                    } else {

                        UpdateTrackInformations(trackid, postdata.info.data[0], 1, postdata.accuracy);
                    }

                }
            }
        },
        error: function (e) {
            console.log("Timefor track : " + trackid + " " + artist + " " + album + " " + song + " " + duration);
            UpdateTrackInformations(trackid, null, 0, 0);
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
        },
        success: function (postdata) {
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
    //Add a spinner and do the search
    $.get('/spinner.html', function (spinnerdata) {
        $('#spinnerplace').html(spinnerdata);
        $("table > tbody  > tr").each(function () {
            total++;
            trackid = $(this).attr('trackid');
            song = $("tr[trackid='" + trackid + "'] td[id='song'] .editable").text();
            album = $("tr[trackid='" + trackid + "'] td[id='album'] .editable").text();
            artist = $("tr[trackid='" + trackid + "'] td[id='artist'] .editable").text();
            duration = $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration');
            var deezerid = $(this).parent().attr("deezerid");
            if (deezerid !== 'null') {
                $("tr[trackid='" + trackid + "'] td[id='accuracy']").html('<span id="SearchOnDeezerButtonWheel" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                DeezerLookup(trackid, artist, album, song, duration);
            }
        });
    });
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