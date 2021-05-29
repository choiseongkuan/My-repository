$(document).ready(()=>{
   // $(window).scroll(function(){
   //    if( $(window).scrollTop() < 52 ){
   //       $('header').addClass('big');
   //    } else {
   //       $('header').removeClass('big');
   //    }
   // })
   var qrcode = new QRCode("qrcode");
   qrcode.makeCode("Hello");
})
