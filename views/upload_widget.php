<div id="<?=$identifier?>">
    <form class="b-upload b-upload_multi" action="<?=$uploadUrl?>" method="POST"
          enctype="multipart/form-data">

        <input type="hidden" name="test" value="valuetest"/>

        <div class="b-upload__dnd">Drag and drop, automatic upload</div>
        <div class="b-upload__dnd-not-supported">
            <div class="btn btn-success js-fileapi-wrapper">
                <span>Choose files</span>
                <input type="file" name="filedata" multiple/>
            </div>
        </div>

        <div class="b-upload__hint">Добавить файлы в очередь загрузки, например изображения ;]</div>

        <div class="clearfix js-files b-upload__files">

            <div class="js-file-tpl b-thumb" data-id="<%= uid %>" title="<% -name %>, <% -sizeText %>">
                <div data-fileapi="file.remove" class="b-thumb__del">✖</div>
                <div class="b-thumb__preview">
                    <div class="b-thumb__preview__pic"></div>
                </div>
                <% if( /^image/.test(type) ){ %>
                    <div data-fileapi="file.rotate.cw" class="b-thumb__rotate"></div>
                <% } %>
                <div class="b-thumb__progress progress-upload progress-small">
                    <div class="bar"></div>
                </div>
                <div class="b-thumb__name"><% -name %></div>
            </div>

        </div>

        <hr/>
        <div class="btn btn-success btn-small js-fileapi-wrapper">
            <span>Add</span>
            <input type="file" name="filedata"/>
        </div>
        <div class="js-upload btn btn-success btn-small">
            <span>Upload</span>
        </div>
    </form>
</div>