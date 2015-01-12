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
            if (!confirm("Are you sure?")) {
                // Cancel remove
                evt.preventDefault();
            }
        },

        onFileComplete: function (evt, uiEvt) {
            var file = uiEvt.file;
            var images = uiEvt.result.images;

            file.$el.addClass('js-sort');

            if (images === undefined) {
                // TODO display more error details
                file_processor.raiseError('Error uploading');
                uploadContainer.fileapi("remove", file);
            } else {
                var json = images.filedata;

                file.data = {
                    id: json.id,
                    type: json.type,
                    type_id: json.type_id
                };
            }
        },


        onFileRemoveCompleted: function (evt, file) {
            evt.preventDefault();

            file.$el
                .attr('disabled', true)
                .addClass('my_disabled')
            ;

            if (confirm('Delete "' + file.name + '"?')) {
                $.post(settings.removeUrl, file.data);

                uploadContainer.fileapi("remove", file);
                // or so
                evt.widget.remove(file);
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
            emptyQueue: {hide: '.js-upload'},
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
                    file_processor.raiseError("Error while saving order.");
                }
            });

            //var item = evt.item; // the current dragged HTMLElement
        }
    });

};