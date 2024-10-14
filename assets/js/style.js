$(document).ready(function(){
    $(document).on('click','.oygg67',function(){
        var formdata = $('#myForm').serialize();
        if(formdata != ''){
            $.ajax({
                url:'weather.php',
                type: 'POST',
                data: formdata,
                dataType: 'json',
                success: function (data) {
                    $('.t76wyk90').addClass('t76wyk');
                    $('.t76wyk90').html(data.report);
                    // console.log(data);
                    
                }
            });
        }else{
            alert('City daal pehle');
        }
    });
});