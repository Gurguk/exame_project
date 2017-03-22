jQuery( document ).ready(function() {
    jQuery('.field input').keyup(function(){
        jQuery(this).val(jQuery(this).val().toUpperCase());
        if(jQuery(this).val()==jQuery(this).parent('span').parent('td').find('.letter').text()) {
            jQuery(this).css('background', '#b4dcaf');
        }
        else
            if(jQuery(this).val()=='')
                jQuery(this).css('background','#EEEEEE');
            else
                jQuery(this).css('background','#d09292');
    });
    jQuery(document).on('click','.show_letter',function(){
        jQuery('.letter').toggle();
        jQuery('.field input').toggle();
    });
});