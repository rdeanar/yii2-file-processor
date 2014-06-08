<div id="<?=$identifier?>" class="b-upload b-upload_multi">

    <input type="hidden" name="fp_hash[]" value="<?=$hash?>"/>

        <div class="b-upload__dnd">Drag and drop, automatic upload</div>
        <div class="b-upload__dnd-not-supported">
            <div class="btn btn-success js-fileapi-wrapper">
                <span>Choose files</span>
                <input type="file" name="filedata" multiple/>
            </div>
        </div>

        <div class="b-upload__hint">Добавить файлы в очередь загрузки</div>

        <ul class="clearfix js-files b-upload__files" id="tstdnd">

            <li class="js-file-tpl b-thumb js-sort" data-id="<%=uid%>" title="<%-name%>, <%-sizeText%>">
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
                <div class="b-thumb__name"><%-name%></div>
            </li>

        </ul>

        <hr/>
        <div class="btn btn-success btn-small js-fileapi-wrapper">
            <span>Add File</span>
            <input type="file" name="filedata"/>
        </div>
        <div class="js-upload btn btn-success btn-small">
            <span>Upload</span>
        </div>
    <hr/>
</div>