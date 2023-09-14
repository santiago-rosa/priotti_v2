<?php 
session_start();

include_once "./Conexion.php";

class DaoProductos {
    
    public static function selector(){
        
            if(isset($_POST["opc"])){
                $tipo = $_POST["opc"];                 
                switch($tipo){
                    case 1:   
                    if(self::esAdmin()) return self::actualizarUno(); //Actualizar producto            
                    return 'No autorizado';      
                    break;  
                    case 2:                    
                    return self::obtenerLista(2); //Lista simple
                    break;  
                    case 3:
                    if(self::esUsuario()) return self::obtenerLista(3);  //Lista de Ofertas              
                    return 'No autorizado';
                    break;
                    case 4:                    
                    return self::obtenerLista(4); // Lista de Novedades
                    break;  
                    case 5:
                    if(self::esUsuario()) return self::descargarLista();  //Descarga .xlsx
                    return 'No autorizado';
                    break;                      
                    default:  
                    return 'No se eligió opción';                  
                    break;
                }   
            }else{
               return 0; 
            }                                 
    }
    
    private static function obtenerLista($tipo){
        $busqueda = $_POST["busqueda"];

        $cont = 0;
        
        switch($tipo){
            case 2:
            $limite = 'limit 100';
            $tipo = 'vigente = 1';
            break;
            case 3:
            $limite = '';
            $tipo = 'precio_oferta > 0 AND vigente = 1';
            break;
            case 4:
            $limite = '';
            $tipo = 'fecha_agregado > date_sub( now(), interval 2 month) AND vigente = 1 order by fecha_agregado desc';
            break;            
        }         
        
        $p = explode(" ", $busqueda);            
        
        $query = 'select codigo, aplicacion, marca, rubro, info, precio_lista, precio_oferta, imagen FROM productos WHERE ';    
        
        foreach($p as $clave){
            if($cont == 5) break;        
            $query .= '(codigo LIKE "%'.$clave.'%" OR  aplicacion Like "%'.$clave.'%" OR
                marca LIKE "%'.$clave.'%" OR  rubro LIKE "%'.$clave.'%" OR  info LIKE "%'.$clave.'%") and ';
            $cont++;
        }

        $query .= $tipo.' '.$limite;        

        $conexion = Conexion::conectar(); 
        try{
            $resultset = $conexion->query($query);            
            if($resultset->num_rows!=0){
                while($row = $resultset->fetch_assoc()) {                    
                    $row["info"] = utf8_encode($row["info"]);
                    $tabla["data"][] = $row;                                                            
                }
            }else{
                return '{ "data": [] }';            
            }
 
        }catch(Exception $e){
            return $e;
        };       
                  
        $resultset->free();
          
        $conexion->close();

        return json_encode($tabla);        
    }
    
    private static function esAdmin(){
        if (isset($_SESSION["usuario"]) && $_SESSION["es_admin"]==1) return true; 
        return false;        
    }

    private static function esUsuario(){
        if (isset($_SESSION["usuario"])) return true;
        return false;               
        
    }

    private static function actualizarUno(){      

        $conexion = Conexion::conectar(); 

        $codigo = $_POST["cod"];

        $equivalencia = utf8_decode($_POST["equiv"]);

        $oferta = $_POST["ofe"];

        //VALIDACIONES DE LOS DATOS

        if($codigo == ""){
            return "Debe seleccionar un producto!";
        }   
        if($oferta == ""){
            $oferta = 0;
        }     
        if(!is_numeric($oferta)){
            return "Sólo pueden ingresar números en el campo 'oferta'";
        }
        if($oferta<0){
            return "La oferta no puede ser un numero negativo!";
        }         

        $query = "update productos set info = '".$equivalencia."', fecha_modif = now(), precio_oferta = '".$oferta."'  where codigo = '".$codigo."';";        

        $conexion->query($query);
          
        $conexion->close();
        
        return "Producto actualizado!";             

    }

    private static function vaciaruploads(){
        $files = glob('../Resources/uploads/*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file)) unlink($file); // delete file
        }   
    }

    private static function descargarLista(){
        include_once("../Utils/xlsxwriter.class.php");
            self::vaciaruploads();
            if(isset($_SESSION['coeficiente'])){
                $coeficiente = $_SESSION['coeficiente']; //El coeficiente del cliente para descargar la lista
            } else {
                $coeficiente = 1;
            }           
            try{                
                $conexion = Conexion::conectar();
                $tabla[] = ["codigo"=>"CODIGO", "marca"=>"MARCA", "rubro"=>"RUBRO", "aplicacion"=>"APLICACION", "precio_lista"=>"PRECIO"];         
                $query = "select codigo, marca, rubro, aplicacion, precio_lista from productos where vigente != 0 order by marca, rubro, codigo";
                $resultset = $conexion->query($query);
                while($row = $resultset->fetch_assoc()) {
                    $row['precio_lista'] = number_format($row['precio_lista'] * $coeficiente, 2, ',', ''); 
                    $row['aplicacion'] = str_replace('=', 'IDEM ', $row['aplicacion']);           
                    $tabla[] = $row;                                                            
                }                     
                $writer = new XLSXWriter();
                $writer->writeSheet($tabla);                
                $date = date("d.m.y");                
                $fecha = 'listapriotti_'.$date;            
                $writer->writeToFile('../Resources/uploads/'.$fecha.'.xlsx');
                $conexion->close();
                $resultset->free();
                return $fecha;
            }catch(Exception $e){
                return "Ocurrio un error al crear el archivo";
            }      
    }  
}

echo DaoProductos::selector();

//echo DaoProductos::obtenerLista();


