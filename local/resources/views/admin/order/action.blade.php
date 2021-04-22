@include('admin.order.dispatch')


@if($row->status == 0)

<span style="font-size: 12px">Elegido por <br> {{ $row->dboy }},  <br>inicio :{{ $row->status_time }}<br> finalizo:{{ $row->status_time }}<br></span>

<a href="{{ Asset(env('admin').'/order/print/'.$row->id) }}" target="_blank">Imprimir factura</a>

<div class="btn-group" role="group">
<button id="btnGroupDrop{{ $row->id }}" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Opciones </button>

<div class="dropdown-menu" aria-labelledby="btnGroupDrop{{ $row->id }}" style="padding: 10px 10px">

<a href="{{ Asset(env('admin').'/order/edit/'.$row->id) }}">Editar orden</a><hr>
<a href="{{ Asset(env('admin').'/orderStatus?id='.$row->id.'&status=1') }}" onclick="return confirm('Are you sure?')">Confirmar pedido</a><hr>

<a href="{{ Asset(env('admin').'/orderStatus?id='.$row->id.'&status=2') }}" onclick="return confirm('Are you sure?')">Cancelar orden</a><hr>

</div>
</div>

@elseif($row->status == -1)

<a href="{{ Asset(env('admin').'/order/assing/delivery/'.$row->id) }}" class="btn btn-rounded btn-success">Asignar</a><hr>

@elseif($row->status == 1)

<span style="font-size: 12px">Elegido por <br> {{ $row->dboy }} <br>inicio :{{ $row->created_at }}<br> finalizo:{{ $row->status_time }} <br></span>

<a href="{{ Asset(env('admin').'/order/print/'.$row->id) }}" target="_blank">Imprimir factura</a>

@if(!$row->dboy)
<div class="btn-group" role="group">
<button id="btnGroupDrop{{ $row->id }}" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Opciones </button>

<div class="dropdown-menu" aria-labelledby="btnGroupDrop{{ $row->id }}" style="padding: 10px 10px">

<a href="{{ Asset(env('admin').'/order/edit/'.$row->id) }}">Editar orden</a><hr>

@if($row->type == 2)

<a href="{{ Asset(env('admin').'/orderStatus?id='.$row->id.'&status=4') }}" onclick="return confirm('Are you sure?')">Dar orden</a><hr>

@else

<a href="javascript::void()" data-toggle="modal" data-target="#slideRightModal{{ $row->id }}">Asignar repartidor</a><hr>


@endif

<a href="{{ Asset(env('admin').'/order/print/'.$row->id) }}" target="_blank">Imprimir factura</a><hr>

<a href="{{ Asset(env('admin').'/orderStatus?id='.$row->id.'&status=2') }}" onclick="return confirm('Are you sure?')" style="color:red">Cancelar orden</a>

</div>
</div>
@endif


@elseif($row->status == 2)

<span style="font-size: 12px">Cancelado a las<br>{{ $row->status_time }}</span>

@elseif($row->status == 3)

<span style="font-size: 12px">Elegido por {{ $row->dboy }} <br>inicio :{{ $row->created_at }}<br> finalizo:{{ $row->status_time }} <br></span>


<a href="{{ Asset(env('admin').'/order/print/'.$row->id) }}" target="_blank">Imprimir factura</a>
@elseif($row->status == 6)
<span style="font-size: 12px">Elegido por {{ $row->dboy }}<br>inicio :{{ $row->created_at }}<br> finalizo:{{ $row->status_time }} <br></span>


<a href="{{ Asset(env('admin').'/order/print/'.$row->id) }}" target="_blank">Imprimir factura</a>

@endif
