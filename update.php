<?php
session_start();
include "./Modelo/Conexion.php";

$data = json_decode(file_get_contents('php://input', true));
setDate($data->{'novelties'});
delete($data->{'delete'});
insert($data->{'insert'});
update($data->{'update'});

function insert($list)
{
  $query = "INSERT INTO productos(codigo, marca, rubro, aplicacion, precio_lista, precio_oferta, imagen, fecha_agregado, info) values ";
  foreach ($list as $it) {
    $codigo = $it->{'codigo'};
    $descripcion = $it->{'descripcion'};
    $marca = $it->{'marca'};
    $rubro = $it->{'rubro'};
    $precio = $it->{'precio'};
    $precio_oferta = $it->{'precio_oferta'};
    $info = $it->{'info'};
    $imagen = $it->{'imagen'};
    $query = $query . "('$codigo','$marca','$rubro','$descripcion',$precio,$precio_oferta,'$imagen', now(), '$info'),";
  }
  $query = substr($query, 0, -1);
  $query = $query . ' ON DUPLICATE KEY UPDATE ' .
    'marca = VALUES(marca), ' .
    'rubro = VALUES(rubro), ' .
    'aplicacion = VALUES(aplicacion), ' .
    'precio_lista = VALUES(precio_lista), ' .
    'precio_oferta = VALUES(precio_oferta), ' .
    'imagen = VALUES(imagen), ' .
    'fecha_agregado = VALUES(fecha_agregado), ' .
    'info = VALUES(info);';
  $conexion = Conexion::conectar();
  $conexion->query($query);
  $conexion->close();
}

function update($list)
{
  $query = "";
  foreach ($list as $item) {
    $query = $query . createUpdateLine($item);
  }
  $conexion = Conexion::conectar();
  $conexion->multi_query($query);
  $conexion->close();
}

function createUpdateLine($item)
{
  $line = "UPDATE productos set ";
  $array = [
    "marca" => "marca",
    "rubro" => "rubro",
    "descripcion" => "aplicacion",
    "precio" => "precio_lista",
    "precio_oferta" => "precio_oferta",
    "info" => "info",
    "imagen" => "imagen"
  ];
  foreach ($item as $key => $value) {
    if ($key != 'codigo') {
      $column = $array[$key];
      if (in_array($key, ["precio", "precio_oferta"])) {
        $line = $line . "$column = $value, ";
      } else {
        $line = $line . "$column = '$value', ";
      }
    }
  }
  $codigo = $item->{'codigo'};
  $line = $line . "fecha_modif = now() WHERE codigo = '$codigo'; ";
  return $line;
}

function delete($list)
{
  $query = "";
  foreach ($list as $it) {
    $codigo = $it->{'codigo'};
    $query = $query . "DELETE FROM productos WHERE codigo = '$codigo';";
  }
  $conexion = Conexion::conectar();
  $conexion->multi_query($query);
  $conexion->close();
}

function setDate($list)
{
  $string = "";
  foreach ($list as $it) {
    $string .= $it . ", ";
  }
  $conexion = Conexion::conectar();
  $conexion->query("INSERT into act_lista(fecha, cambios) values(now(),'$string')");
  $conexion->close();
}
