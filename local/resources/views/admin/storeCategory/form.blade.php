@include('admin.language.header')
<br>

<div class="tab-content" id="myTabContent1">
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Nombre</label>
                {!! Form::text('name',null,['placeholder' => 'Nombre de categoria','class' => 'form-control'])!!}
            </div>

            <div class="form-group col-md-6">
                <label>Estado</label>
                <select name="status" class="form-control">
                    <option value="0" @if($data->status === 0) selected @endif>Habilitado</option>
                    <option value="1" @if($data->status === 1) selected @endif>Deshabilitado</option>
                </select>
            </div>

            <div class="form-group">
                {!! Form::file('img', ['id' => 'img']) !!}

                <hr>

                @if(isset($data->img) && $data->img != "")
                    <div id="actualImg">
                        <label class="mt-2">Imagen actual:</label>
                        <div style="position:relative; align-content: center">
                            <a type="button" class="close" style="left:170px;height:27px;width:27px;font-size:40px;position: absolute;" onclick="deleteImage()">
                                <span>&times;</span>
                            </a>
                            <img id="categoryImg" src="{{ Asset('upload/user/category/'.$data->img) }}" style="max-width:200px;"><br>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<button type="submit" class="btn btn-success btn-cta">Guardar cambios</button>
