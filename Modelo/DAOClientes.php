<?php 

session_start();

include "./Conexion.php";

class DaoClientes {
    
    public static function selector(){
        if(self::esAdmin()){
            if(isset($_POST["opc"])){
                $tipo = $_POST["opc"];                 
                switch($tipo){
                    case 1:                
                    return self::actualizar(); //Update
                    break;  
                    case 2:                    
                    return self::obtenerLista(); //Read
                    break;
                    case 3:                    
                    return self::crear(); //Create
                    break;
                    case 4:                    
                    return self::eliminar(); //Delete
                    break;                                            
                    default:                    
                    break;
                }   
            }else{
               return 0; 
            }  
        }
        return 'No autorizado';                       
    }
    
    private static function obtenerLista(){          
        
        $query = 'select id, nombre, numero, cuit, email, porcentajeaumento, fechaUltimoLogin as ultimo, estado, visitas  
        FROM clientes';                 
        
        $conexion = Conexion::conectar(); 
        try{
            $resultset = $conexion->query($query);            
            if($resultset->num_rows!=0){
                while($row = $resultset->fetch_assoc()) {   
                    $row["nombre"] = utf8_encode($row["nombre"]);                
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

    private static function actualizar(){

        $conexion = Conexion::conectar(); 

        $id = $_POST["id"];
        $nombre = $_POST["nombre"];
        $numero = $_POST["numero"]; //usuario
        $cuit = $_POST["cuit"]; //clave
        $email = $_POST["email"];
        $aumento = $_POST["aumento"]; //coeficiente
        $estado = $_POST["estado"]; //activo-inactivo   

        //VALIDACION DE LOS DATOS
        if(strlen($nombre)>=150 || strlen($cuit)>=50 || strlen($numero)>=150 ){
            return "O nombre, o numero de cliente, o cuit demasiado largo";
        }  

        if($aumento == ""){
            $aumento = 0.00;
        }  

        if(!is_numeric($aumento)){
            return "Formato de aumento no válido. Ej: 5% se ingresa 5.00";
        }

        if($aumento<0){
            return "El aumento no puede ser negativo!";
        }

        $check = self::checkExiste($conexion, $numero);
        if($check != false  && $check != $id){            
            return 'Ese numero de cliente ya existe!';
        }

        $query = "update clientes 
        set nombre = '".$nombre."', numero = '".$numero."', cuit = '".$cuit."', email = '".$email."', porcentajeaumento = '".$aumento."', estado = '".$estado."'  
        where id = '".$id."';";        

        $conexion->query($query);
          
        $conexion->close();
        
        return "Cliente actualizado!";          

    }

    private static function crear(){ 
    
        $conexion = Conexion::conectar(); 

        //$id = $_POST["id"]; Se crea automaticamente con nuevo cliente
        $nombre = $_POST["nombre"];
        $numero = $_POST["numero"]; //usuario
        $cuit = $_POST["cuit"]; //clave
        $email = $_POST["email"];
        $aumento = $_POST["aumento"]; //coeficiente
        //$estado = $_POST["estado"]; //activo-inactivo    
        $estado = "ACTIVO";  

        //VALIDACIONES DE LOS DATOS

        if(strlen($nombre)>=150 || strlen($cuit)>=50 || strlen($numero)>=150 ){
            return "O nombre, o numero de cliente, o cuit demasiado largo";
        } 
        if($aumento == ""){
            $aumento = 0.00;
        }         
        if(!is_numeric($aumento)){
            return "Formato de aumento no válido. Ej: 5% se ingresa 5.00";
        }
        if($aumento<0){
            return "El aumento no puede ser negativo!";
        }
        $query = "select numero, nombre from clientes where numero = '".$numero."'";
        $resultado = $conexion->query($query);

        $check = self::checkExiste($conexion, $numero);
        if($check != false){
            return 'Ese numero de cliente ya existe!';
        }

        $query = "insert into clientes (nombre, numero, cuit, email, porcentajeaumento, fechaAlta) 
        values ('".$nombre."','".$numero."','".$cuit."','".$email."', ".$aumento.", now())";        

        $conexion->query($query);
        
        $conexion->close();
        
        return "Cliente creado!"; 
        
    }

    private static function eliminar(){
        $conexion = Conexion::conectar(); 

        $id = $_POST["id"]; //NO hace falta con cliente nuevo           

        //VALIDACIONES DE LOS DATOS              

        $query = "delete from clientes where id = ".$id.";";        

        $conexion->query($query);
          
        $conexion->close();
        
        return "Cliente eliminado!";
    }

    private static function esAdmin(){
        if (isset($_SESSION["usuario"]) && $_SESSION["es_admin"]==1) return true; 
        return false;        
    }

    private static function checkExiste($conexion, $numero){
        $query = "select id from clientes where numero = '".$numero."'";
        $resultado = $conexion->query($query);
        if($resultado->num_rows != 0){
            $resultado = $resultado->fetch_assoc();
            return $resultado['id'];
        }
        return false;
    }


}

echo DaoClientes::selector();
