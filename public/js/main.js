$("input[type=file]").change(function () {
  var fieldVal = $(this).val();
  if (fieldVal != undefined || fieldVal != "") {
    $(this).next(".custom-file-label").text(fieldVal);
  }
});

