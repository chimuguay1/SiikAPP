@extends('admin.layout.main')

@section('title') Usuarios de la aplicación @endsection

@section('icon') mdi-home @endsection


@section('content')

<section class="pull-up">
<div class="container">
<div class="row ">
<div class="col-md-12">
<div class="card py-3 m-b-30">

<div class="card-body">
<table class="table table-hover ">
<thead>
<tr>
<th>Usuario</th>
<th>Correo electrónico</th>
<th>Teléfono</th>
<th>Fecha de registro</th>
<th>Orden total</th>
<th>Estado</th>
<th>Opciones</th>
</tr>

</thead>
<tbody>

@foreach($data as $row)

<tr>
<td width="10%">{{ $row->name }}</td>
<td width="10%">{{ $row->email }}</td>
<td width="10%">{{ $row->phone }}</td>
<td width="10%">{{ date('d-M-Y',strtotime($row->created_at)) }}</td>
<td width="10%">{{ $row->countOrder($row->id) }}</td>
<td width="10%">
    @if ($row->estado == 1)
      Activado
    @else
      Desactivado
    @endif
</td>
<td>
    
<a href="" class="btn m-b-15 ml-2 mr-2 btn-md  btn-rounded-circle btn-success" data-toggle="tooltip" data-placement="top" data-original-title=""><i class="mdi mdi-border-color"></i></a>

<button type="button" class="btn m-b-15 ml-2 mr-2 btn-md  btn-rounded-circle btn-danger" data-toggle="tooltip" data-placement="top" data-original-title="" onclick=""><i class="mdi mdi-delete-forever"></i></button>


</td>
    
</tr>

@endforeach

</tbody>
</table>

</div>
</div>

{!! $data->links() !!}

</div>
</div>
</div>
</section>

@endsection
