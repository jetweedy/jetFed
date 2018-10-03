/*
RESOURCE: http://www.dropzonejs.com/
*/

var jetFileDropZone = function(options) {
	//// REQUIRED
	this.Options = {FileDrop:{iframe: {url: options.url}}}
	this.Options.dropzoneContainerID = options.dropzoneContainerID;
	this.Options.target = options.target;
	//// OPTIONAL
	this.Options.onThumbClick = (options.onThumbClick!=undefined) ? options.onThumbClick : function() {};
	this.Options.onFileDrop = (options.onFileDrop!=undefined) ? options.onFileDrop : function() {};
	this.Options.progressContainerID = (options.progressContainerID!=undefined) ? options.progressContainerID : false;
	this.Options.thumbsContainerID = (options.thumbsContainerID!=undefined) ? options.thumbsContainerID : false;
	this.Options.extras = (options.extras!=undefined) ? options.extras : false;
	this.init();
};

jetFileDropZone.prototype = {

	init: function() {
		var thisZone = new FileDrop(this.Options.dropzoneContainerID, this.Options.FileDrop);
		fd.byID(this.Options.progressContainerID).style.display = "none";
		thisZone.Options = this.Options;
		thisZone.event('send', function (files) {
			files.jetFileDropZone = this;
			fd.byID(this.Options.progressContainerID).style.display = "block";
			fd.byID(this.Options.progressContainerID).style.width = "0%";
			files.images().each(function (file) {
				file.jetFileDropZone = this.jetFileDropZone;
				file.readData(
				  function (uri) {
					var img = new Image;
					img.src = uri;
					img.className = "thumb";
					thisZone.el.appendChild(img);
				  },
				  function (error) {
					alert('Oh noes! Cannot read your image!')
				  },
				  'uri'
				);
			});
			files.each(function (file) {
				file.jetFileDropZone = this.jetFileDropZone;
				// What to do when finished
				file.event('done', function (xhr) {
					console.log(xhr.responseText);
					fd.byID(this.jetFileDropZone.Options.progressContainerID).style.width = "100%";
					console.log(this);
					if (!!this.jetFileDropZone.Options.onFileDrop) {
						this.jetFileDropZone.Options.onFileDrop(xhr.responseText,this.jetFileDropZone.Options);
					}
				});
				file.event('progress', function (current, total) {
					var width = current / total * 100 + '%';
					console.log(width);
					fd.byID(this.jetFileDropZone.Options.progressContainerID).style.width = width;
				});
				file.sendTo(this.jetFileDropZone.Options.target)
			});

		});
		thisZone.event('iframeDone', function (xhr) {
			console.log(xhr.responseText)
		});
	}
	
	,

	curry: function(fn, scope, args) {
		var scope = scope || window;
		if (!args) { args = []; }
		return function() {
			fn.apply(scope, args);
		};
	}

};