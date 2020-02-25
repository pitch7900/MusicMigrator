
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
            source = $(this).attr('source');
            //Display the spinner and then start the load of the playlist items
            $.get('/spinner.html', function (spinnerdata) {
                $('#songlist').html(spinnerdata);
                $.get('/' + source + '/playlist/' + playlistid + '.html', function (data) {
                    $('#songlist').html(data);
                });
            });
        });
    };
    

    /**
     * Decide to display an input boy named "#InputGroupPlaylistName" for giving 
     * a spefic name to the playsit imported
     * @return {undefined}
     */
    var handler_click_ImportToDropDownSelection = function () {
        $('body').on('click', '#dropdownMenuPlaylistItems>a.dropdown-item', function () {
            $('#dropdownMenuPlaylist').html($(this).text());
            playlistid = $(this).attr('playlistid');
            $('#dropdownMenuPlaylist').attr('playlistid', playlistid);
            if (playlistid === '0') {
                $('#InputGroupPlaylistName').removeClass('invisible');
            } else {
                $('#InputGroupPlaylistName').addClass('invisible');
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
     * Handle the click on remove a row
     * @return {undefined}
     */
    var listener_addplaylistUrl = function () {
        $('input[id="CustomPlaylistLink"]').change(function (e) {
            console.log("Change triggered");
            setTimeout(function () {
                changeInputCustomPlaylistURL();
            }, 100);
        });
        $('body').on('paste keydown', '#CustomPlaylistLink', function () {
            console.log("Paste or keydown triggered");
            setTimeout(function () {
                changeInputCustomPlaylistURL();
            }, 100);
        });
        
    };

    /**
     * Check every minute if we're still have a valid Deezer Token.
     * Otherwise force a page refresh for login.
     * @return {undefined}
     */
    var checkLoginStatus = function () {

//        window.setInterval(function () {
//            $.ajax({
//                type: 'get',
//                url: '/deezer/me/about.json',
//                cache: false,
//                statusCode: {
//                    401: function (response) {
//                        //User is logged of
//                        //force a refresh of the page, if we're not already on an error page
//                        if ($("#ErrorPage").length !== 0) {
//                            window.location.reload();
//                        }
//                    },
//                    300: function (response) {
//                        //User is logged of
//                        //force a refresh of the page
//                        window.location.reload();
//                    }
//                }, success: function () {
//
//                }
//            });
//
//
//        }, 60000);
    };

    return {
        init: function () {
            handler_click_ShowPlaylist();

            handler_click_ImportToDropDownSelection();
            handler_select_iTunes_File();
            listener_addplaylistUrl();
            handler_click_delete_row_on_table();
            checkLoginStatus();
        }
    };
}();
$(document).ready(function () {
    Catalog.init();
});


/**
     * Display the playsit choosen in the main right window
     * @return {undefined}
     */
    function click_AddCustomPlaylist() {
//        $('body').on('click', '#AddCustomPlaylist', function () {
            console.log($('#CustomPlaylistLink').val());
            parseInputCustomPlaylistURL(function (parsedinfo) {
            console.log(parsedinfo['source'] + " " + parsedinfo['playlistid']);
            $('#songlist').empty();
            $.get('/spinner.html', function (spinnerdata) {
                $('#songlist').html(spinnerdata);
                $.get('/' + parsedinfo['source'] + '/playlist/' + parsedinfo['playlistid'] + '.html', function (data) {
                    $('#songlist').html(data);
                });
            });

        });
    };

function parseInputCustomPlaylistURL(callbackFunction) {
    var informationurl = new Object();
    var a = $('<a>', {
        href: $('#CustomPlaylistLink').val()
    });
    informationurl['error'];
    var playlistid = a.prop('pathname').substring(a.prop('pathname').lastIndexOf('/') + 1);
    console.log("Playlist ID " + playlistid);
    if (a.prop('hostname').includes("spotify") && playlistid.length !== 0) {
        informationurl['source'] = "spotify";
        informationurl['playlistid'] = playlistid;
        informationurl['icon'] = '<span class="badge badge-pill badge-default">spotify</span>';
    }
    if (a.prop('hostname').includes("deezer") && playlistid.length !== 0) {
        informationurl['source'] = "deezer";
        informationurl['playlistid'] = playlistid;
        informationurl['icon'] = '<span class="badge badge-pill badge-default">deezer</span>';
    }
    $.get('/' + informationurl['source'] + '/playlist/' + informationurl['playlistid'] + '/info.json', function (data) {
        if (data['tracks'] !== null) {
            informationurl['playlistname'] = data['name'];
            informationurl['description'] = data['description'];
            informationurl['tracks'] = data['tracks'];
            informationurl['image'] = data['image'];
        } else {
            informationurl['source'] = "error";
        }
        console.log(informationurl);
        if (typeof callbackFunction === 'function')
        {
            callbackFunction.call(this, informationurl);
        }
    });

}


function changeInputCustomPlaylistURL() {
    var url = $('input[id="CustomPlaylistLink"]').val();
    console.log("change triggered " + url);
    parseInputCustomPlaylistURL(function (parsedinfo) {
        if (parsedinfo['source'] !== "error") {
            $('#AddCustomPlaylist').removeClass("invisible");
            $('#CustomPlaylistLinkWrapping').html(parsedinfo['icon']);
        } else {
            $('#AddCustomPlaylist').addClass("invisible");
            $('#CustomPlaylistLinkWrapping').html("URL");
            
        }
    });


    //$('#CustomPlaylistLink').text('<span class="badge badge-pill badge-default">' + url + '</span>');
}


/**
 * Update a track with search results
 * @param {type} trackid
 * @param {type} data
 * @param {type} status
 * @param {type} accuracy
 * @return {undefined}
 */
function UpdateTrackInformations(trackid, data, status, accuracy, destination) {
    $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='accuracy']").data(accuracy);
    //update the accuracy cell with the detection value
    $("tr[trackid='" + trackid + "'] td[id='accuracy']").addClass("signal" + accuracy + "on6");
    $("tr[trackid='" + trackid + "'] td[id='accuracy']").addClass("signal");
    switch (destination) {
        case "deezer":
            destination_logo = '<img src="/img/deezer.png" width="16" height="16" alt="">';
            break;
        case "spotify":
            destination_logo = '<img src="/img/Spotify.svg" width="16" height="16" alt="">';
            break;
        default:
            destination_logo = '<i class="far fa-question-circle"></i>';
            break;
    }

    //Success - a track has been found
    switch (status) {
        case 1:
            $("#DestinationImportTracksNumber").text(parseInt($("#DestinationImportTracksNumber").text(), 10) + 1);
            $("#ButtonImportTo").removeClass("invisible");
            trackimage = data.album.picture;
            //Remove previous search results
            $("tr[trackid='" + trackid + "']").removeClass("searcherror");
            $("tr[trackid='" + trackid + "']").removeClass("searchwarning");
            //Add the deezer track id found
            $("tr[trackid='" + trackid + "']").attr('destinationid', data.track.id);
            $("tr[trackid='" + trackid + "']").attr('destination', destination);
            //get the informations in the text cell that can be edited
            song = $("tr[trackid='" + trackid + "'] td[id='song'] .editable").text();
            album = $("tr[trackid='" + trackid + "'] td[id='album'] .editable").text();
            artist = $("tr[trackid='" + trackid + "'] td[id='artist'] .editable").text();
            //Update the image cell

            $("[imagetrackid='" + trackid + "']").html('<img class="trackimage" src="' + trackimage + '" alt="">');
            $("[linktrackid='" + trackid + "']").attr('href', data.track.link);

            //Update the track name cell
            original_value = $("tr[trackid='" + trackid + "'] td[id='song']").attr('original_value');
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='song']").data('<span class="editable">' + song + '</span><br><button type="button" class="btn btn-link"><a href="' + data.track.link + '" target="_blank" contenteditable="false">' + destination_logo + " " + data.track.name + '</a></button>');
            $("tr[trackid='" + trackid + "'] td[id='song']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='song']").removeClass("edited");
            //update the album name cell
            original_value = $("tr[trackid='" + trackid + "'] td[id='album']").attr('original_value');

            albumurl = data.album.link;

            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='album']").data('<span class="editable">' + album + '</span><br><button type="button" class="btn btn-link"><a href="' + albumurl + '" target="_blank" contenteditable="false">' + destination_logo + " " + data.album.name + '</a></button>');
            $("tr[trackid='" + trackid + "'] td[id='album']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='album']").removeClass("edited");
            //Update the artist name cell
            original_value = $("tr[trackid='" + trackid + "'] td[id='artist']").attr('original_value');
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='artist']").data('<span class="editable">' + artist + '</span><br><button type="button" class="btn btn-link"><a href="' + data.artist.link + '" target="_blank" contenteditable="false">' + destination_logo + " " + data.artist.name + '</a></button>');
            $("tr[trackid='" + trackid + "'] td[id='artist']").addClass("editable");
            $("tr[trackid='" + trackid + "'] td[id='artist']").removeClass("edited");
            //Update the track duration cell
            original_value = $("tr[trackid='" + trackid + "'] td[id='duration']").attr('original_value');
            $('#playlisttable').DataTable().cell("tr[trackid='" + trackid + "'] td[id='duration']").data(SecondsToHms(data.track.duration))
            $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration', data.track.duration * 1000);
            $("tr[trackid='" + trackid + "'] td[id='duration']").addClass("text-primary");
            //If accuracy score is strcily above 2 consider we have a pretty decent detection
            if (accuracy > 2) {
                progressvalue = parseInt($("#destination_recognationsucess").attr('aria-valuenow'), 10) + 1;
                progresstotal = parseInt($("#destination_recognationsucess").attr('aria-valuemax'), 10);

                $("#destination_recognationsucess").attr('aria-valuenow', progressvalue);
                progressstyle = "width: " + Math.round(progressvalue / progresstotal * 10000) / 100 + "%";
                $("#destination_recognationsucess").attr('style', progressstyle);
            } else {
                //Else, change the cell color to put a warning
                $("tr[trackid='" + trackid + "']").addClass("searchwarning");
                progressvalue = parseInt($("#destination_recognationwarning").attr('aria-valuenow'), 10) + 1;
                progresstotal = parseInt($("#destination_recognationwarning").attr('aria-valuemax'), 10);

                $("#destination_recognationwarning").attr('aria-valuenow', progressvalue);
                progressstyle = "width: " + Math.round(progressvalue / progresstotal * 10000) / 100 + "%";
                $("#destination_recognationwarning").attr('style', progressstyle);
            }

            break;
        default:
            $("tr[trackid='" + trackid + "']").addClass("searcherror");
            progressvalue = parseInt($("#destination_recognationerror").attr('aria-valuenow'), 10) + 1;
            progresstotal = parseInt($("#destination_recognationerror").attr('aria-valuemax'), 10);

            $("#destination_recognationerror").attr('aria-valuenow', progressvalue);
            progressstyle = "width: " + Math.round(progressvalue / progresstotal * 10000) / 100 + "%";
            $("#destination_recognationerror").attr('style', progressstyle);
            break;

    }
    //remove the spinner
    progressvalue = parseInt($("#destination_recognationsucess").attr('aria-valuenow'), 10) + parseInt($("#destination_recognationerror").attr('aria-valuenow'), 10) + parseInt($("#destination_recognationwarning").attr('aria-valuenow'), 10);
    progresstotal = parseInt($("#destination_recognationsucess").attr('aria-valuemax'), 10);
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
function ImportPlaylist(tracklist, playlistid, destination) {
    var formdata = new FormData();
    formdata.append('tracklist', JSON.stringify(tracklist));
    sUrl = "/" + destination + "/playlist/" + playlistid + "/addsongs";
    $.ajax({
        type: 'post',
        enctype: 'multipart/form-data',
        data: formdata,
        url: sUrl,
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
function ImportTo(destination) {
    $('#DeezerImport_modal').modal('hide');
    tracklist = [];
    $("table > tbody  > tr").each(function () {
        destinationid = $(this).attr('destinationid');
        if (destinationid !== 'null') {
            tracklist.push(destinationid);
        }
    });
    console.log(tracklist);

    playlistid = $('#dropdownMenuPlaylist').attr('playlistid');
    if (playlistid === '0') {
        CreatePlaylistAndImport(tracklist, destination);
    } else {
        ImportPlaylist(tracklist, playlistid, destination);
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
            if (!$("tr[trackid='" + trackid + "']").hasClass('searcherror')) {
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
    var mDisplay = m > 0 ? ("0" + m + ":").slice(-2) : "";
    var sDisplay = ("0" + s).slice(-2);
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
function DestinationLookup(trackid, artist, album, song, duration, destination) {
    console.log("(DestinationLookup) Searching for " + trackid + " " + artist + " " + album + " " + song + " " + duration + " " + destination);
    var formdata = new FormData();
    formdata.append('trackid', trackid);
    formdata.append('artist', artist);
    formdata.append('album', album);
    formdata.append('song', song);
    formdata.append('duration', duration);
    searchurl = "/" + destination + "/search.json";
    $.ajax({
        type: 'post',
        enctype: 'multipart/form-data',
        data: formdata,
        url: searchurl,
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        beforeSend: function () {
        },
        success: function (postdata) {
            if (postdata.success === false) {
                UpdateTrackInformations(trackid, null, 0, 0, destination);
            } else {

                if (!("info" in postdata)) {
                    UpdateTrackInformations(trackid, null, 0, 0, destination);

                } else {
                    if (postdata.length === 0 || postdata.info.total === 0) {
                        UpdateTrackInformations(trackid, null, 0, 0, destination);
                    } else {

                        UpdateTrackInformations(trackid, postdata.info, 1, postdata.accuracy, destination);
                    }

                }
            }
        },
        error: function (e) {
            console.log("Timefor track : " + trackid + " " + artist + " " + album + " " + song + " " + duration);
            UpdateTrackInformations(trackid, null, 0, 0, destination);
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
    $("#SearchButtonWheel").addClass("invisible");
}



/**
 * Search in Deezer for the tracklist passed in parameters
 * @param {type} list
 * @return {undefined}
 */
function DeezerLookupList(tracklist) {
    var formdata = new FormData();
    formdata.append('tracklist', JSON.stringify(tracklist));
    $("#SearchButtonWheel").removeClass("invisible");


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
function SearchOnDestination(destination) {
    list = {};
    console.log("Batch searching on " + destination);
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
            var destinationid = $(this).parent().attr("destinationid");
            if (destinationid !== 'null') {
                $("tr[trackid='" + trackid + "'] td[id='accuracy']").html('<span id="SearchButtonWheel" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                DestinationLookup(trackid, artist, album, song, duration, destination);
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
function RefreshDestination(trackid, destination) {
    song = $("tr[trackid='" + trackid + "'] td[id='song'] .editable").text();
    album = $("tr[trackid='" + trackid + "'] td[id='album'] .editable").text();
    artist = $("tr[trackid='" + trackid + "'] td[id='artist'] .editable").text();
    duration = $("tr[trackid='" + trackid + "'] td[id='duration']").attr('duration');
    DestinationLookup(trackid, artist, album, song, duration, destination);
}

/**
 * Request a playlist creation on Deezer
 * and import the travlist passed in parameter
 * @return the created playlist id
 */
function CreatePlaylistAndImport(tracklist, destination) {

    playlistid = $('#dropdownMenuPlaylist').attr('playlistid');
    if (playlistid === '0') {
        name = $('#InputPlaylistName').val();
    } else {
        name = $('#destination_playlist_'.playlistid).attr('playlistname');
        name = $('#destination_playlist_'.playlistid).attr('playlistname');
    }
    var formdata = new FormData();
    formdata.append('name', name);
    formdata.append('public', 'public');
    formdata.append('tracklist', tracklist)
    sUrl = "/" + destination + "/me/createplaylist";
    $.ajax({
        type: 'post',
        enctype: 'multipart/form-data',
        data: formdata,
        url: sUrl,
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
                    ImportPlaylist(tracklist, playlistid, destination);
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