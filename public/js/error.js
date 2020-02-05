function post_writeconfig() {
    var dzapi = $('#DeezerApplicationID').val();
    var dzsecret = $('#DeezerSecretKey').val();
    var spotifyapi = $('#SpotifyApplicationID').val();
    var spotifysecret = $('#SpotifySecretKey').val();
    var sitename = $('#sitename').val();

    $.post("/configuration/writesetup", {dzapi: dzapi, dzsecret: dzsecret, sitename: sitename,spotifyapi:spotifyapi,spotifysecret:spotifysecret}, function (data) {
        console.log(data);
        window.location.href = "/";
    });
}


$(document).ready(function() {
    sitename = window.location.href;
    sitename= sitename.replace(/\/+$/, "");
    $('#sitename').val(sitename)
});
