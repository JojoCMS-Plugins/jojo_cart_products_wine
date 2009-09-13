$(document).ready(function() {
$('.buynowbutton').bind('click', function(e) {
    var data = {};
    data[$('input', this).attr('id')] = 'go';
    jojo_cart_widget_update(data);

    /* Don't go to the cart page */
    return false;
});

});
