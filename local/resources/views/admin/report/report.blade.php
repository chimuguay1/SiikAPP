<?php
$file="reporte.xls";
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file");
?>

<p align="center" style="font-size: 20px">Informar entre {{ $from }} A {{ $to }}</p>

<table width="100%" cellspacing="0" cellpadding="0" border="1">

<tr>
<td>&nbsp;<b>Solicitar ID</b></td>
<td>&nbsp;<b>Fecha de orden</b></td>
<td>&nbsp;<b>Usuario</b></td>
<td>&nbsp;<b>Tienda</b></td>
<td>&nbsp;<b>Repartidor</b></td>
<td>&nbsp;<b>Venta</b></td>
<td>&nbsp;<b>Costo Real</b></td>
<td>&nbsp;<b>Cantidad</b></td>
<td>&nbsp;<b>Costo Siik</b></td>
<td>&nbsp;<b>Total</b></td>
<td>&nbsp;<b>Negocio</b></td>
<td>&nbsp;<b>Ganancia total</b></td>
</tr>

@php($total = [])
@php($subtotal = [])
@php($com = [])
@php($id = 0)
@foreach($data as $row)

@php($total_real = $row['precio_real'] * $row['cantidad'])
@php($totalC = $row['precio_venta'] * $row['cantidad'])
@php($subtotal[] = $row['precio_venta'])
@php($com[] = $user->getCom($row['id'],$row['precio_venta']))
@php($totalReal = $totalC - $total_real)
@php($total[] = $totalReal)

<tr>
@if($id == $row['id'])
<td width="17%"></td>
<td width="17%"></td>
<td width="17%"></td>
<td width="17%"></td>
<td width="17%"></td>
@else
<td width="17%">&nbsp;#{{ $row['id'] }}</td>
<td width="17%">&nbsp;{{ $row['date'] }}</td>
<td width="17%">&nbsp;{{ $row['user'] }}</td>
<td width="17%">&nbsp;{{ $row['store'] }}</td>
<td width="17%">&nbsp;{{ $row['dboy'] }}</td>
@endif
<td width="17%">&nbsp;{{ $row['name_item'] }}</td>
<td width="17%">&nbsp;{{ $row['precio_real'] }}</td>
<td width="17%">&nbsp;{{ $row['cantidad'] }}</td>
<td width="17%">&nbsp;{{ $row['precio_venta'] }}</td>
<td width="17%">&nbsp;{{ $totalC }}</td>
<td width="17%">&nbsp;{{ $total_real }}</td>
<td width="17%">&nbsp;{{ $totalReal }}</td>
</tr>

@php($id = $row['id'])

@endforeach

<tr>
<td width="17%">&nbsp;</td>
<td width="17%">&nbsp;</td>
<td width="17%">&nbsp;<b>Pedidos totales</b></td>
<td width="17%">&nbsp;<b>{{ count($total) }}</b></td>
<td width="17%">&nbsp;<b>Ganancias totales</b></td>
<td width="17%">&nbsp;<b>{{ array_sum($total) }}</b></td>
</tr>

</table>
