 function do_lottery(product_id){
    jQuery.ajax({
        url: my_ajax_object.ajax_url,
        type: "post",
        data:{
            action:'send_notification',
            product_id: product_id,
            nonce: my_ajax_object.nonce
        },
        success:function(response){
            console.log(response);
        }
    })
}