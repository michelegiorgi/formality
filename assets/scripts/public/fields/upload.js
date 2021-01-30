import { el } from '../core/helpers'
import uiux from '../core/uiux'

export default {
  init() {
    this.build();
  },
  build() {
    $(el("field", true, "--upload :input")).change(function () {
      const input = $(this);
      if (this.files && this.files[0]) {
        var reader = new FileReader();
        var formats = ["jpeg", "jpg", "png", "gif", "svg", "webp"];
        reader.fileName = this.files[0].name;
        reader.fileSize = this.files[0].size;
        reader.fileFormat = this.files[0].name.split('.').pop().toLowerCase();
        reader.onload = function (e) {
          console.log(formatBytes(e.target.fileSize), e.target.fileName, )
          //$().addClass("filled");
          if(formats.indexOf(e.target.fileFormat) == -1 ) {
            //not an image
          } else {
            //is an image => preview
            input.find("img").attr('src', e.target.result);
          }
          //print info
          //e.target.fileName
          //formatBytes(e.target.fileSize);
          //$().removeClass("dragging").removeClass('highlight');
        }
        reader.readAsDataURL(this.files[0]);
      } else {
        //$().removeClass("filled");
      }
    });
    function formatBytes(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}
    $(el("field", true, "--upload .formality__file-toggle")).click(function(e){
      $(this).prev().focus();
    })
    var drag_timer;
    $(document).on('dragover', function(e){
      var dt = e.originalEvent.dataTransfer;
      if(dt.types && (dt.types.indexOf ? dt.types.indexOf('Files') != -1 : dt.types.contains('Files'))){
        //$().addClass("dragging");
        window.clearTimeout(drag_timer);
      }
    }).on('dragleave', function(e){
      drag_timer = window.setTimeout(function(){
        //$().removeClass("dragging");
      }, 50);
    });

    $(el("field", true, "--upload")).on('dragenter', function(){
      //$().addClass('highlight');
    }).on('dragleave', function(){
      //$().removeClass('highlight');
    });
  },
}
