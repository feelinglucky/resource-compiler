jQuery(function(a){var d=a("#J_Message");d.hide(),timer=null;function c(e,f){d.html(e).fadeIn();clearTimeout(timer);timer=setTimeout(function(){d.fadeOut()},f||1500)}var b=new qq.FileUploader({debug:false,action:"./compiler/",fileParam:"upload_file",element:document.getElementById("uploader"),onSubmit:function(f,e){d.html("<em></em>Uploading<br />&lt;"+e+"&gt;").fadeIn()},sizeLimit:10000*100,onComplete:function(j,i,h){d.hide();if(h.success){var e=Math.round(h.original_size/1000),f=Math.round(h.minized_size/1000),g=Math.round(e-f);c("Compiled Success<br />&lt;"+i+"&gt;<br/><br />saved "+g+"kb",5000);setTimeout(function(){location.href=h.url},500)}else{h.message&&c(h.message)}},allowedExtensions:["js","css"],showMessage:c,messages:{typeError:"Only aollowed to upload {extensions} file.",sizeError:"The {file} is too large.",emptyError:"The {file} is empty.",onLeave:"The files are being uploaded, if you leave now the upload will be cancelled."}})});