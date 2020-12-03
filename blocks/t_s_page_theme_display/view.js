$(function() {
    $.ajax({
        type        : 'POST',
        dataType    : 'json',
        data        : new URLSearchParams(window.location.search).toString(),
        url         : actionURL,
        encode      : true,
        error:function(xhr, status, error){
            console.log(error);
        }
    }).then((result,status,xhr) => {
        console.log("RESULT FROM TDISPL: " + result)
        console.log(result)
        $('#page-item-' + bID + '-display-color').css("background-color", result.color)
        $('#page-item-' + bID + '-display-theme').text(result.theme)
    })
})