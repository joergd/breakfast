MovieClip.prototype.loadjpg = function(picName, holderName) {
    // holderName can be passed in case needed for progress indicator
    // if not passed, use 'holder' as default
	
    var h = holderName==undefined ? "holder" : holderName;
    this.createEmptyMovieClip(h, 1);
    this._visible = false;
    this[h].loadMovie(picName);
    var id = setInterval(function (mc) {
        if (mc[h].getBytesLoaded() > 1 && 
              mc[h].getBytesLoaded() > mc[h].getBytesTotal()-10 && 
              mc[h]._width > 0) {
            mc._alpha = 99;
            clearInterval(id);
            mc._visible = true;
            mc.onComplete();
        } else {
            mc.onLoading();
        }
    }, 80, this);  
};
