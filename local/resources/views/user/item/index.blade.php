@extends('user.layout.main')

@section('title') Administrar artículos @endsection

@section('icon') mdi-silverware-fork-knife @endsection


@section('content')

<section class="pull-up">
<div class="container">
<div class="row ">
<div class="col-md-12">
<div class="card py-3 m-b-30">

<div class="row">
<div class="col-md-12" style="text-align: right;">
<a href="{{ Asset('export') }}" class="btn m-b-15 ml-2 mr-2 btn-rounded btn-info">Exportar Excel</a>
<a href="{{ Asset('import') }}" class="btn m-b-15 ml-2 mr-2 btn-rounded btn-success">Subir Excel</a>
<a href="{{ Asset($link.'add') }}" class="btn m-b-15 ml-2 mr-2 btn-rounded btn-warning">Agregar nuevo</a>

</div>
</div>


<div class="card-body">
<table class="table table-hover ">
<thead>
<tr>
<th>Imagen</th>
<th>Categoría</th>
<th>Nombre</th>
<th>Estado</th>
<th style="text-align: right">Opción</th>
</tr>

</thead>
<tbody>

@foreach($data as $row)

<tr>
<td width="15%">@if($row->img) <img src="{{ Asset('upload/item/'.$row->img) }}" height="50"> @endif</td>
<td width="12%">{{ $row->cate }}</td>
<td width="17%">{{ $row->name }}</td>
<td width="12%">

@if($row->status == 0)

<button type="button" class="btn btn-sm m-b-15 ml-2 mr-2 btn-success" onclick="confirmAlert('{{ Asset($link.'status/'.$row->id) }}')">Activo</button>

@else

<button type="button" class="btn btn-sm m-b-15 ml-2 mr-2 btn-danger" onclick="confirmAlert('{{ Asset($link.'status/'.$row->id) }}')">Deshabilitado</button>

@endif

</td>

<td width="22%" style="text-align: right">

<a href="javascript::void()" class="btn m-b-15 ml-2 mr-2 btn-md  btn-rounded-circle btn-info" data-toggle="modal" data-target="#slideRightModal{{ $row->id }}"><i class="mdi mdi-link"></i></a>

<a href="{{ Asset($link.$row->id.'/edit') }}" class="btn m-b-15 ml-2 mr-2 btn-md  btn-rounded-circle btn-success" data-toggle="tooltip" data-placement="top" data-original-title="@lang('admin.Edit This Entry')"><i class="mdi mdi-border-color"></i></a>

<button type="button" class="btn m-b-15 ml-2 mr-2 btn-md  btn-rounded-circle btn-danger" data-toggle="tooltip" data-placement="top" data-original-title="@lang('admin.Delete This Entry')" onclick="deleteConfirm('{{ Asset($link."delete/".$row->id) }}')"><i class="mdi mdi-delete-forever"></i></button>


</td>
</tr>

@include('user.item.addon')

@endforeach

</tbody>
</table>

</div>
</div>
</div>
</div>
</div>
</section>

@endsection
