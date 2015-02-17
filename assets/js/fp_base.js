/**
 * Created by deanar on 24/12/14.
 * @author Mikhail Razumovksiy <rdeanar@gmail.com>
 */


var file_processor = file_processor || {
        languageMessages: {},

        showValidationErrors: function(evt, data){
            setTimeout(function () { // don't remember why i use timeout, maybe error does not raise without it
                $(data.all).each(function (i, file) {
                    if (file.$el === undefined) {
                        file_processor.showValidationErrorsByFile(file);
                    } else {
                        file.$el.removeClass('js-sort');
                    }
                });
            }, 300);
        },

        showValidationErrorsByFile: function (file) {
            var errors = file.errors;

            if (errors === undefined) return true;

            // count and size
            if (errors.maxFiles)  this.raiseError(file_processor.getMessage('MAX_FILES', {filename: file.name}));
            if (errors.maxSize)   this.raiseError(file_processor.getMessage('MAX_SIZE', {filename: file.name, maxSize: this.bytesToSize(errors.maxSize) }));

            // min dimension
            if (errors.minWidth)  this.raiseError(file_processor.getMessage('MIN_WIDTH', {filename: file.name, minWidth: errors.minWidth}));
            if (errors.minHeight) this.raiseError(file_processor.getMessage('MIN_HEIGHT', {filename: file.name, minHeight: errors.minHeight}));

            // max dimension
            if (errors.maxWidth)  this.raiseError(file_processor.getMessage('MAX_WIDTH', {filename: file.name, maxWidth: errors.maxWidth}));
            if (errors.maxHeight) this.raiseError(file_processor.getMessage('MAX_HEIGHT', {filename: file.name, maxHeight: errors.maxHeight}));
        },

        raiseError: function (msg) {
            //console.log(msg);
            alert(msg);
        },

        // used decimal, not binary
        bytesToSize: function (bytes) {
            if (bytes == 0) return '0 Byte';
            var k = 1000;
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
        },

        addMessages: function(message_array){
            jQuery.extend(file_processor.languageMessages, message_array);
        },

        getMessage: function(key, options){
            if(file_processor.languageMessages){
                var message = file_processor.languageMessages[key] || key;
                if(options){
                    for( var arg in options ) {
                        message = message.replace("{" + arg + "}", options[arg]);
                    }
                }
                return message;
            }
            return key;
        }
    };

