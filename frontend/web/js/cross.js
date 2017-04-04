jQuery( document ).ready(function() {
    jQuery('.field input').keyup(function(){
        var val = jQuery(this).val();
        var l = val.length;
        for(var i=0;i<l;i++) {
            if (val.slice(1) != '')
                val = val.slice(1);
        }
        jQuery(this).val(val);
        if(/[^a-zA-Zа-яА-ЯёЁ]/i.test(jQuery(this).val())){
            jQuery(this).val('');
            return false;
        }
        jQuery(this).val(jQuery(this).val().toUpperCase());
        if(jQuery(this).val()==jQuery(this).parent('span').parent('td').find('.letter input').val()) {
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

    jQuery(document).on('change','#crosscategorylist-id',function(){
        jQuery('#section').empty();
        if(jQuery(this).val()!='')
            jQuery.ajax({
                url: "index.php?r=cross/sections",
                dataType: "json",
                method: "POST",
                data: {id:jQuery(this).val()},
                success: function(data){
                    jQuery('#section').append(data.select);
                },
                error: function () {
                    alert("ERROR");
                }
            });
    });
});