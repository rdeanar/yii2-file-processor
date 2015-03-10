/**
 * Created by deanar on 24/12/14.
 * @author Mikhail Razumovskiy <rdeanar@gmail.com>
 */


file_processor.multi_upload = function (settings) {

    /*
    settings = {
     'identifier'            => '',
     'uploadUrl'             => '',
     'removeUrl'             => '',
     'sortUrl'               => '',
     'additionalData'        => {},
     'alreadyUploadedFiles'  => [],
     'options'               => {},
    }
     */

    var uploadContainer = $('#' + settings.identifier);

    var fileapi_options = {
        url: settings.uploadUrl,
        data: settings.additionalData,

        // Restores the list of files uploaded earlier.
        files: settings.alreadyUploadedFiles,

        // Events
        onSelect: function (evt, data) {
            file_processor.showValidationErrors(evt, data);
        },

        // Remove a file from the upload queue
        onFileRemove: function (evt, file) {
            if (!confirm(file_processor.getMessage('REMOVE_FROM_QUEUE_CONFIRM'))) {
                // Cancel remove
                evt.preventDefault();
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
            } else {
                var filedata = images.filedata;

                file.data = {
                    id: filedata.id,
                    type: filedata.type,
                    type_id: filedata.type_id
                };

                file.$el.addClass('js-sort');
            }
        },


        onFileRemoveCompleted: function (evt, file) {
            evt.preventDefault();

            file.$el
                .attr('disabled', true)
                .addClass('my_disabled')
            ;

            if (confirm(file_processor.getMessage('REMOVE_FILE_WITH_NAME_CONFIRM', {filename: file.name}))) {
                $.post(settings.removeUrl, file.data)
                    .done(function (data) {
                        //TODO indication
                        uploadContainer.fileapi("remove", file);
                    })
                    .fail(function (data) {
                        if (FileAPI.debug) {
                            file_processor.raiseError(file_processor.getMessage('REMOVE_FILE_ERROR_DETAILED', {errors: data.responseText}));
                        } else {
                            file_processor.raiseError(file_processor.getMessage('REMOVE_FILE_ERROR'));
                        }
                    });
            } else {
                file.$el
                    .attr('disabled', false)
                    .removeClass('my_disabled')
                ;
            }
        },

        elements: {
            ctrl: {upload: '.js-upload'},
            empty: {show: '.b-upload__hint'},
            emptyQueue: {hide: '.js-upload', show: '.fp-dragndrop-hint' },
            list: '.js-files',
            file: {
                tpl: '.js-file-tpl',
                preview: {
                    el: '.b-thumb__preview',
                    width: 80,
                    height: 80
                },
                upload: {show: '.progress-upload', hide: '.b-thumb__rotate'},
                complete: {hide: '.progress-upload', show: '.b-thumb__del'},
                progress: '.progress-upload .bar'
            },
            dnd: {
                el: '.b-upload__dnd',
                hover: 'b-upload__dnd_hover',
                fallback: '.b-upload__dnd-not-supported'
            }
        }

    };

    $.extend(fileapi_options, settings.options);
    uploadContainer.fileapi(fileapi_options);

    /*
     File sorting
     */

    var drag_list_container = uploadContainer.find('ul').get(0);

    var sort = new Sortable(drag_list_container, {
        handle: ".b-thumb__preview", // Restricts sort start click/touch to the specified element
        draggable: ".js-sort", // Specifies which items inside the element should be sortable
        ghostClass: "sortable-ghost",
        onUpdate: function (evt) {
            var sort = [];

            uploadContainer.find('li').each(function (i, el) {
                var filedata_id = $(el).data('id');
                var file_data = uploadContainer.fileapi('_getFile', filedata_id).data;
                if (file_data !== undefined) {
                    sort.push(file_data.id);
                }
            });

            $.ajax({
                url: settings.sortUrl,
                data: {sort: sort},
                type: "POST",
                error: function (data, status, e) {
                    file_processor.raiseError(file_processor.getMessage('ORDER_SAVE_ERROR'));
                }
            });

            //var item = evt.item; // the current dragged HTMLElement
        }
    });

};