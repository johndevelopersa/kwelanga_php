jQuery.fn.extend({
  disable: function(state) {
      return this.each(function() {
          this.disabled = state;
      });
  }
});

var signatureArea = {
    
    canvas : false,
    ctx : false,
    saveParams : false,
    rootPath : false,
    
    initialise : function(saveParams, rootPath) {
      try {
      signatureArea.saveParams = saveParams;
      signatureArea.rootPath = rootPath;
      signatureArea.canvas = document.getElementById('canvas');
      signatureArea.ctx = signatureArea.canvas.getContext("2d");
      
      //Mouse events
      signatureArea.canvas.addEventListener('mousedown', this.eventSignPad, false);
      signatureArea.canvas.addEventListener('mousemove', this.eventSignPad, false);
      signatureArea.canvas.addEventListener('mouseup', this.eventSignPad, false);
      
      //Touch screen events
      signatureArea.canvas.addEventListener('touchstart', this.eventTouchPad, false);
      signatureArea.canvas.addEventListener('touchmove', this.eventTouchPad, false);
      signatureArea.canvas.addEventListener('touchend', this.eventTouchPad, false);
      
      sign = new this.signCap();
      
      /*
      signatureArea.canvas.addEventListener("mousedown", signatureArea.pointerDown, false);
      signatureArea.canvas.addEventListener("mouseup", signatureArea.pointerUp, false);
      
      signatureArea.canvas.addEventListener('touchstart', signatureArea.pointerDown, false);
      signatureArea.canvas.addEventListener('touchend', signatureArea.pointerUp, false);
      */
      
      
      signatureArea.ctx.strokeStyle = "#0000AA";
      signatureArea.ctx.lineWidth = 1;
      $('#btnSaveSignature').disable(true);
      $('#btnSaveSignature').on('click',signatureArea.saveSignature);
      } catch(e) {
        alert(e.message);
      }
    },
    
    signCap: function()  {
      var sign = this;
      this.draw = false;
      this.start = false;
      
      this.mousedown = function(event) {
          signatureArea.ctx.beginPath();
          signatureArea.ctx.arc(event._x, event._y,1,0*Math.PI,2*Math.PI);
          signatureArea.ctx.fill();
          signatureArea.ctx.stroke();
          signatureArea.ctx.moveTo(event._x, event._y);
          sign.draw = true;
          $('#btnSaveSignature').disable(false);
      };

      this.mousemove = function(event) {
          if (sign.draw) {
            signatureArea.ctx.lineTo(event._x, event._y);
            signatureArea.ctx.stroke();
          }
      };

      this.mouseup = function(event) {
          if (sign.draw) {
              sign.mousemove(event);
              sign.draw = false;
          }
      };
      
      this.touchstart = function(event) {
        signatureArea.ctx.beginPath();
        signatureArea.ctx.arc(event._x, event._y,1,0*Math.PI,2*Math.PI);
        signatureArea.ctx.fill();
        signatureArea.ctx.stroke();
        signatureArea.ctx.moveTo(event._x, event._y);
        sign.start = true;
        $('#btnSaveSignature').disable(false);
      };

      this.touchmove = function(event) {
          event.preventDefault(); 
          if (sign.start) {
            signatureArea.ctx.lineTo(event._x, event._y);
            signatureArea.ctx.stroke();
          }
      };

      this.touchend = function(event) {
          if (sign.start) {
              sign.touchmove(event);
              sign.start = false;
          }
      };
        
  },

  eventSignPad: function(event) {
      if (event.offsetX || event.offsetX == 0) {
          event._x = event.offsetX;
          event._y = event.offsetY;
      } else if (event.layerX || event.layerX == 0) {
          event._x = event.layerX;
          event._y = event.layerY;
      }
      
      var func = sign[event.type];
      if (func) {
          func(event);
      }
 
  },
  
  eventTouchPad: function(event) {
    /*
    try {
      // var mySign = signatureArea.canvas;
      var mySign = Ext.get("signature");
      
      //in the case of a mouse there can only be one point of click
      //but when using a touch screen you can touch at multiple places
      //at the same time. Here we are only concerned about the first
      //touch event. Next we get the canvas element's left and Top offsets 
      //and deduct them from the current coordinates to get the position
      //relative to the canvas 0,0 (x,y) reference.
      event._x = event.targetTouches[0].pageX - mySign.getX();
      event._y = event.targetTouches[0].pageY - mySign.getY();
      
      var func = sign[event.type];
      if (func) {
          func(event);
      }
    
    } catch(e) {
      //alert(e.message);
    }
      
    */
      event._x = event.targetTouches[0].pageX - $('#canvas').position().left;
      event._y = event.targetTouches[0].pageY - $('#canvas').position().top;
      
      var func = sign[event.type];
      if (func) {
          func(event);
      }
  },
  
    /*
    THESE HAVE BEEN COMMENTED OUT BECAUSE THE METHOD OF DOING AN ARC ABOVE IS SMOOTHER
    
    pointerDown : function(evt) {
      signatureArea.ctx.beginPath();
      signatureArea.ctx.moveTo(evt.offsetX, evt.offsetY);
      signatureArea.canvas.addEventListener("mousemove", signatureArea.paint, false);
      signatureArea.canvas.addEventListener("touchmove", signatureArea.paint, false);
    },
    
    pointerUp : function(evt) {
      signatureArea.canvas.removeEventListener("mousemove", signatureArea.paint);
      signatureArea.canvas.removeEventListener("touchmove", signatureArea.paint);
      signatureArea.paint(evt);
    },

    paint : function(evt) {
      signatureArea.ctx.lineTo(evt.offsetX, evt.offsetY);
      signatureArea.ctx.stroke();
    },
    */
    
    clearSignatureArea : function() {
      try {
        signatureArea.ctx.clearRect(0, 0, signatureArea.canvas.width, signatureArea.canvas.height);
        $('#btnSaveSignature').disable(true);
        signatureArea.applyWatermark();
      } catch(e) {
        alert(e.message);
      }
    },
    
    applyWatermark : function() {
      try {
        // use an embedded image as the dataURI approach does not work on android for some reason
        signatureArea.ctx.drawImage($('#imgWatermark').get(0),0,0);
        
        /*
        // var img = new Image();
        // img.crossOrigin = 'anonymous';
        
        $('<img />', {
          src: 'data:image/png;base64,'+signatureArea.watermark,
          crossOrigin : 'anonymous'
        }).on('load',function() {
          signatureArea.ctx.drawImage($(this).get(),0,0);
        }).each(function() {
          if(this.complete) $(this).load();
          else if(this.error) {
            alert('error on img load');
          }// this is necessary as sometimes the load doesnt work because its already loaded
        }); 
        //.appendTo('body');
        
        return;
        
        $(img).on("load", function() {
          signatureArea.ctx.drawImage(img,0,0);
        }).each(function() {
          if(this.complete) $(this).load();
          else if(this.error) {
            alert('error on img load');
          }// this is necessary as sometimes the load doesnt work because its already loaded
        });
        $(img).attr('src','data:image/png;base64,'+signatureArea.watermark);
        //img.src = 'http://192.168.0.107/eclipse/RetailtradingTest/multilink/images/error-icon-big.png';
        
        */
      } catch(e) {
        alert(e.message);
      }
      
    },
    
    getSignatureImage : function() {
      try {
        var dataString = signatureArea.canvas.toDataURL("image/png");
        var index = dataString.indexOf( "," )+1;
        dataString = dataString.substring( index );
        
        //var data = signatureArea.ctx.getImageData(0, 0, signatureArea.canvas.width, signatureArea.canvas.height).data;
        //var signatureData = data.replace(/^data:image\/(png|jpg);base64,/, "");
      } catch(e) {
        alert(e.message);
      }
      
      return dataString;
    },
    
    saveSignature : function() {
      var imageData = signatureArea.getSignatureImage();

      if (signatureArea.alreadySubmitted) {
        alert('You have already clicked on submit... If you are sure the capture has NOT been stored then you may click submit again after 2 minutes.');
        return;
      }
      signatureArea.alreadySubmitted=true;

      var params='';

      params+='&'+signatureArea.saveParams;
      params+='&IMAGEDATA='+imageData;

      params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
      $.ajax({
        global: false,
        type: "POST",
        data: params,
        dataType: "html",
        cache: false,
        timeout: 120000,
        url: signatureArea.rootPath+'functional/transaction/signatureSubmit.php', 
        crossDomain: true,
        success: function (result) {
          signatureArea.alreadySubmitted = false;
          alert(result);
        },
        error: function (xhr, textStatus, error) {
          signatureArea.alreadySubmitted = false;
          alert(result);
        }
    });
      
  }
    
}


/*

(function (ns) {
    "use strict";
 
    ns.SignatureControl = function (options) {
        var containerId = options && options.canvasId || "container",
            callback = options && options.callback || {},
            label = options && options.label || "Signature",
            cWidth = options && options.width || "300px",
            cHeight = options && options.height || "300px",
            btnClearId,
            btnAcceptId,
            canvas,
            ctx;
 
        function initCotnrol() {
            createControlElements();
            wireButtonEvents();
            canvas = document.getElementById("signatureCanvas");
            canvas.addEventListener("mousedown", pointerDown, false);
            canvas.addEventListener("mouseup", pointerUp, false);
            ctx = canvas.getContext("2d");            
        }
 
        function createControlElements() {            
            var signatureArea = document.createElement("div"),
                labelDiv = document.createElement("div"),
                canvasDiv = document.createElement("div"),
                canvasElement = document.createElement("canvas"),
                buttonsContainer = document.createElement("div"),
                buttonClear = document.createElement("button"),
                buttonAccept = document.createElement("button");
 
            labelDiv.className = "signatureLabel";
            labelDiv.textContent = label;
 
            canvasElement.id = "signatureCanvas";
            canvasElement.clientWidth = cWidth;
            canvasElement.clientHeight = cHeight;
            canvasElement.style.border = "solid 2px black";
 
            buttonClear.id = "btnClear";
            buttonClear.textContent = "Clear";
 
            buttonAccept.id = "btnAccept";
            buttonAccept.textContent = "Accept";
 
            canvasDiv.appendChild(canvasElement);
            buttonsContainer.appendChild(buttonClear);
            buttonsContainer.appendChild(buttonAccept);
 
            signatureArea.className = "signatureArea";
            signatureArea.appendChild(labelDiv);
            signatureArea.appendChild(canvasDiv);
            signatureArea.appendChild(buttonsContainer);
 
            document.getElementById(containerId).appendChild(signatureArea);
        }
 
        function pointerDown(evt) {
            ctx.beginPath();
            ctx.moveTo(evt.offsetX, evt.offsetY);
            canvas.addEventListener("mousemove", paint, false);
        }
 
        function pointerUp(evt) {
            canvas.removeEventListener("mousemove", paint);
            paint(evt);
        }
 
        function paint(evt) {
            ctx.lineTo(evt.offsetX, evt.offsetY);
            ctx.stroke();
        }
 
        function wireButtonEvents() {
            var btnClear = document.getElementById("btnClear"),
                btnAccept = document.getElementById("btnAccept");
            btnClear.addEventListener("click", function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }, false);
            btnAccept.addEventListener("click", function () {
                callback();
            }, false);
        }
 
        function getSignatureImage() {
            return ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        }
 
        return {
            init: initCotnrol,
            getSignatureImage: getSignatureImage
        };
    }
})(this.ns = this.ns || {});

function loaded() {
    var signature = new ns.SignatureControl({ containerId: 'container', callback: function () {
            alert('hello');
        } 
    });
    signature.init();
}

window.addEventListener('DOMContentLoaded', loaded, false);

*/