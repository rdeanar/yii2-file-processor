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
     }
     */

    var uploadContainer = $('#' + settings.identifier);

    uploadContainer.find('div.js-delete').on('click', function(){

        var delete_array = [];
        if( typeof uploadContainer.fileapi('widget').files !== "undefined"){

            var files_length = uploadContainer.fileapi('widget').files.length-1;
            for (var file=files_length; file >= 0; file--){

                delete_array.push([uploadContainer.fileapi('widget').files[file]]);
            }

            for(i in delete_array){
                $.post(settings.removeUrl, delete_array[i][0].data); //TODO indication + remove on ajax callback
                uploadContainer.fileapi('remove', delete_array[i][0]);
            }
            uploadContainer.find('.js-preview').empty();
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
                width: 200,
                height: 200
            },
            progress: '.js-progress'
        },
        onSelect: function (evt, ui){
            //console.log(evt);
            //console.log(ui);
            var file = ui.files[0];

            if( !FileAPI.support.transform ) {
                alert('Your browser does not support Flash :(');
            }
            else if( file ){
                var tst = true;

                    //console.log( $.modal.noConflict() );
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
                                minSize: [200, 200],
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

            if (images === undefined) {
                file_processor.raiseError('Error uploading');
                uploadContainer.fileapi("remove", file);
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

    uploadContainer.fileapi(fileapi_options);
};