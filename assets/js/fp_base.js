/**
 * Created by deanar on 24/12/14.
 * @author Mikhail Razumovksiy <rdeanar@gmail.com>
 */


var file_processor = file_processor || {
        languageMessages: {},

        showValidationErrors: function(evt, data){
            setTimeout(function () { // do not remember why i use timeout, maybe error do not raised without it
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
            if (errors.maxFiles)  this.raiseError('Can not add file "' + file.name + '". Too much files.');
            if (errors.maxSize)   this.raiseError('Can not add file "' + file.name + '". File bigger that need by ' + this.bytesToSize(errors.maxSize) + '.');

            // min dimension
            if (errors.minWidth)  this.raiseError('Can not add file "' + file.name + '". File thinner than need by ' + errors.minWidth + ' pixels.');
            if (errors.minHeight) this.raiseError('Can not add file "' + file.name + '". File lower than need by ' + errors.minHeight + ' pixels.');

            // max dimension
            if (errors.maxWidth)  this.raiseError('Can not add file "' + file.name + '". File wider than need by  ' + errors.maxWidth + ' pixels.');
            if (errors.maxHeight) this.raiseError('Can not add file "' + file.name + '". File higher than need by ' + errors.maxHeight + ' pixels.');
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

