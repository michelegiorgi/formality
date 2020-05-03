// emergence.formality.js
// This is a lite version of emergence.js
// emergence.js v1.1.2 | (c) 2017 @xtianmiller | https://github.com/xtianmiller/emergence.js

/* eslint-disable */

(function(root, factory) {
  // AMD
  if (typeof define === 'function' && define.amd) {
    define(function() {
      return factory(root);
    });
  } else if (typeof exports === 'object') {
    // Node.js or CommonJS
    module.exports = factory;
  } else {
    // Browser globals
    root.emergence = factory(root);
  }
})(this, function(root) {

  'use strict';

  var emergence = {};
  var poll, container, throttle, offsetTop, offsetBottom, offsetY, selector;
  var callback = function() {};

  var updatePercentage = function() {
    if(/^\d+(\.\d+)?%$/.test(offsetY)) {
      offsetTop = parseInt((container.innerHeight/100)*parseFloat(offsetY));
      offsetBottom = parseInt((container.innerHeight/100)*parseFloat(offsetY));
    }
  };

  var getElemOffset = function(elem) {
    var h = elem.offsetHeight;
    var topPos = 0;
    do {
      if (!isNaN(elem.offsetTop)) {
        topPos += elem.offsetTop;
      }
    } while ((elem = elem.offsetParent) !== null);
    return {
      height: h,
      top: topPos
    };
  };


  var getContainerSize = function(container) {
    var h;
    if (container !== window) {
      h = container.clientHeight;
    } else {
      h = window.innerHeight || document.documentElement.clientHeight;
    }
    return {
      height: h
    };
  };


  var getContainerScroll = function(container) {
    if (container !== window) {
      return {
        y: container.scrollTop + getElemOffset(container).top
      };
    } else {
      return {
        y: window.pageYOffset || document.documentElement.scrollTop
      };
    }
  };


  var isVisible = function(elem) {

    if (elem.offsetParent === null) { return false; }

    var elemOffset = getElemOffset(elem);
    var containerSize = getContainerSize(container);
    var containerScroll = getContainerScroll(container);

    var elemHeight = elemOffset.height;
    var elemTop = elemOffset.top;
    var elemBottom = elemTop + elemHeight;

    var checkBoundaries = function() {
      var cTop = containerScroll.y + offsetTop;
      var cBottom = containerScroll.y - offsetBottom + containerSize.height;
      return (elemTop < cBottom && elemBottom > cTop);
    };

    return checkBoundaries();
  };


  var emergenceThrottle = function() {
    if (!!poll) {
      return;
    }
    clearTimeout(poll);
    poll = setTimeout(function() {
      emergence.engage(selector);
      poll = null;
    }, throttle);
  };

  emergence.init = function(options) {
    options = options || {};

    // Function to return an integer
    var optionInt = function(option, fallback) {
      if(/^\d+(\.\d+)?%$/.test(option)) {
        let totheight = 0;
        if(container == window) {
          totheight = container.innerHeight;
        } else {
          totheight = container.offsetHeight;
        }
        let height = parseInt((totheight/100)*parseFloat(option))
        return height;
      } else {
        return parseInt(option || fallback, 10);
      }
    };

    // Function to return a floating point number
    var optionFloat = function(option, fallback) {
      return parseFloat(option || fallback);
    };

    // Default options
    container = options.container || window; // window or document by default
    throttle = 100; // 250 by default
    offsetY = options.offsetY;
    offsetTop = optionInt(offsetY, 0); // 0 by default
    offsetBottom = optionInt(offsetY, 0); // 0 by default
    callback = options.callback || callback;
    selector = options.selector || "[data-emergence]";

    // If browser doesnt pass feature test
    if (window.addEventListener) {
      
      window.addEventListener('load', emergenceThrottle, false);
      container.addEventListener('scroll', emergenceThrottle, false);
      container.addEventListener('resize', updatePercentage, false);
      container.addEventListener('resize', emergenceThrottle, false);

    } else {

      document.attachEvent('onreadystatechange', function() {
        if (document.readyState === 'complete') { emergenceThrottle(); }
      });
      container.attachEvent('onscroll', emergenceThrottle);
      container.attachEvent('onresize', updatePercentage);
      container.attachEvent('onresize', emergenceThrottle);

    }
  };

  // Engage emergence
  emergence.engage = function(selector) {
    var nodes = document.querySelectorAll(selector);
    var length = nodes.length;
    var elem;

    for (var i = 0; i < length; i++) {
      elem = nodes[i];

      if (isVisible(elem)) {
        callback(elem, 'visible');
      }
    }
    
  };
  
  return emergence;
});

/* eslint-enable */