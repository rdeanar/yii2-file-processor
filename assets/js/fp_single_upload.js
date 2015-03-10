/**
 * Created by deanar on 25/12/14.
 * @author Mikhail Razumovskiy <rdeanar@gmail.com>
 */


file_processor.single_upload = function (settings) {

    /*
     settings = {
     'identifier'            => '',
     'uploadUrl'             => '',
     'removeUrl'             => '',
     'additionalData'        => {},
     'alreadyUploadedFiles'  => [],
     'options'               => {},
     'crop'                  => true,
     'preview'               => true
     }
     */

    var uploadContainer = $('#' + settings.identifier);

    uploadContainer.width(settings.previewSize[0]);
    uploadContainer.height(settings.previewSize[1]);

    uploadContainer.find('.js-controls').css('margin-top', Math.min(parseInt(settings.previewSize[1] * (167 / 200) - 17), settings.previewSize[1] - 40));

    uploadContainer.find('div.js-delete').on('click', function(){

        if (!confirm(file_processor.getMessage('REMOVE_FILE_CONFIRM'))) return;

        var delete_array = [];
        if( typeof uploadContainer.fileapi('widget').files !== "undefined"){

            var files_length = uploadContainer.fileapi('widget').files.length-1;
            for (var file=files_length; file >= 0; file--){
                delete_array.push([uploadContainer.fileapi('widget').files[file]]);
            }

            var removeSuccess = false;
            for (i in delete_array) {
                $.post(settings.removeUrl, delete_array[i][0].data)
                    .done(function (data) {
                        //TODO indication
                        uploadContainer.fileapi('remove', delete_array[i][0]);
                        removeSuccess = true;
                    })
                    .fail(function (data) {
                        if(FileAPI.debug) {
                            file_processor.raiseError(file_processor.getMessage('REMOVE_FILE_ERROR_DETAILED', {errors: data.responseText}));
                        }else{
                            file_processor.raiseError(file_processor.getMessage('REMOVE_FILE_ERROR'));
                        }
                    });
            }
            if (removeSuccess) uploadContainer.find('.js-preview').empty();
        }
    });

    var fileapi_options = {
        url: settings.uploadUrl,
        data: settings.additionalData,

        // Restores the list of files uploaded earlier.
        files: settings.alreadyUploadedFiles,

        elements: {
            //active: { show: '.js-upload', hide: '.js-browse' },
            active: { show: '.js-upload' },
            //complete: { hide: '.js-browse', show: '.js-delete'},
            empty: {
                show: '.js-browse',
                hide: '.js-delete'
            },

            preview: {
                el: '.js-preview',
                width: settings.previewSize[0],
                height: settings.previewSize[1]
            },
            progress: '.js-progress'
        },
        onSelect: function (evt, ui){

            file_processor.showValidationErrors(evt, ui);

            var file = ui.files[0];

            if( !FileAPI.support.transform ) {
                file_processor.raiseError(file_processor.getMessage('FLASH_NOT_SUPPORTED'));
            }
            else if( file ){

                    var bootstrap = true;
                    if (bootstrap) {

                        var modal_html = $('#fp_single_upload_modal_bs').html();
                        var modal_selector = '#' + settings.identifier + '_modal';

                        $(modal_selector).remove();
                        $(modal_html).appendTo('body');
                        $(modal_selector).on('show.bs.modal', function (event) {

                            var modal = $(this);

                            modal.find('.js-upload').on('click', function () {
                                modal.modal('hide');
                                uploadContainer.fileapi('upload');
                            });

                            $('.js-img', modal).cropper({
                                file: file,
                                bgColor: '#fff',
                                maxSize: [$(window).width() - 100, $(window).height() - 100],
                                minSize: settings.previewSize,
                                selection: '90%',
                                onSelect: function (coords) {
                                    uploadContainer.fileapi('crop', file, coords);
                                }
                            });

                        })
                            .modal({keyboard: false, backdrop: 'static'})
                            .modal('show');

                    } else {

                        $('#popup').modal({
                            closeOnEsc: true,
                            closeOnOverlayClick: false,
                            onOpen: function (overlay) {

                                console.log(overlay);

                                $(overlay).on('click', '.js-upload', function () {
                                    $.modal().close();
                                    uploadContainer.fileapi('upload');
                                });

                                $('.js-img', overlay).cropper({
                                    file: file,
                                    bgColor: '#fff',
                                    maxSize: [$(window).width() - 100, $(window).height() - 100],
                                    minSize: [200, 200],
                                    selection: '90%',
                                    onSelect: function (coords) {
                                        uploadContainer.fileapi('crop', file, coords);
                                    }
                                });
                            }
                        }).open();
                    } // no bootstrap
            }
        },
        onFileComplete: function (evt, uiEvt) {
            var file = uiEvt.file;
            var images = uiEvt.result.images;

            if (images === undefined || images.length < 1) {
                if(FileAPI.debug){
                    if(uiEvt.result.errors === undefined){
                        file_processor.raiseError(file_processor.getMessage('UPLOAD_ERROR_DETAILED', {errors: uiEvt.error}));
                    }else {
                        file_processor.raiseError(file_processor.getMessage('UPLOAD_ERROR_DETAILED', {errors: uiEvt.result.errors.join(', ')}));
                    }
                }else{
                    file_processor.raiseError(file_processor.getMessage('UPLOAD_ERROR'));
                }
                uploadContainer.fileapi("remove", file);
                uploadContainer.find('.js-preview').empty();
            } else {
                var json = images.filedata;

                file.data = {
                    id: json.id,
                    type: json.type,
                    type_id: json.type_id
                };

                uploadContainer.find('div.js-delete input').val(json.id);
            }
        }

    };

    $.extend(fileapi_options, settings.options);

    if(!settings.crop)
        fileapi_options.onSelect = function(evt, ui){
            file_processor.showValidationErrors(evt, ui);
        };

    if(!settings.preview)
        fileapi_options.elements = {
            //ctrl: { upload: '.js-send', reset: '.js-reset' }, // maybe later
            name: '.js-name',
            size: '.js-size',
            empty: { show: '.js-browse', hide: '.js-info, .js-delete' }
        };

    uploadContainer.fileapi(fileapi_options);
};