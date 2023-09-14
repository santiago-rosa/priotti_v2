<?php

session_start();

include "../Modelo/Conexion.php";

class ControlSesion {

    public static function selector(){
        $opc = $_POST["opc"];
        switch($opc){
            case 1:
            return self::loginCliente();
            break;
            case 2:
            return self::loginAdmin();
            break;
            case 3:
            return self::cerrarSesion();
            break;
            case 4:
            return self::datosCliente();
            break;
            case 5:
            return self::datosUsuario();
            break;
            default:
            return 'No autorizado';
            break;
        }
    }   

    private static function loginCliente(){

        $conexion = Conexion::conectar();        

        //Los privilegios son 0=administrador, 1=usuario, 2=cliente jejejo

        $usuario = strtolower($_POST["usuario"]);        
        $clave = strtolower($_POST["clave"]);           
        $query = "SELECT id, nombre, numero, cuit, porcentajeaumento, estado FROM clientes WHERE numero = '".$usuario."'";
        $resultset = $conexion->query($query);  //Consulto por cliente

        if($resultset->num_rows==1){                
            $row = $resultset->fetch_assoc();
            if ($row["cuit"] == $clave) {
                if($row["estado"] == "ACTIVO"){
                    $conexion->query("update clientes set fechaUltimoLogin = now(), visitas = visitas+1 where id = ".$row["id"]);
                    $_SESSION["id"] = $row["id"];  
                    $_SESSION["usuario"] = $row["numero"];
                    $_SESSION["nombre"] = $row["nombre"];             
                    $_SESSION["coeficiente"] = $row["porcentajeaumento"]/100+1; 
                    $_SESSION["pedido"] = []; 
                    return self::datosCliente();
                } else {
                    return "Usuario inactivo";
                }                    
            } else {
                return "Datos incorrectos";
            }
        }else{
            return "Datos incorrectos";
        }
 
    }

    private static function loginAdmin(){
        $conexion = Conexion::conectar();     
        $usuario = $_POST["usuario"];        
        $clave = $_POST["clave"];                        
        $query= 'select idusuario, nombre, apellido, clave, es_admin FROM usuario WHERE idusuario = "'.$usuario.'"';  
        $resultset = $conexion->query($query);  //Consulto por usuario  
        if($resultset->num_rows==1){            
            $row = $resultset->fetch_assoc();
            if ($row["clave"] == $clave) {
                $_SESSION["usuario"] = $usuario;
                $_SESSION["nombre"] = $row["nombre"];
                $_SESSION["apellido"] = $row["apellido"];
                $_SESSION["es_admin"] = $row["es_admin"];                
                return self::datosUsuario();
            } else {
                return "Datos incorrectos";
            }     
        } else {
            return "Datos incorrectos";
        }

    }

    private static function cerrarSesion() {
        if (isset($_SESSION["usuario"])) {            
            session_unset();
            session_destroy();                   
        }
        return "Sesión cerrada";
      
    }

    private static function datosUsuario() {        
        if (isset($_SESSION["usuario"])) { 
            if(isset($_SESSION["es_admin"])){            
                $usuario = $_SESSION["usuario"];
                $nombre = $_SESSION["nombre"];
                $apellido = $_SESSION["apellido"];             
                return 'Usuario: '.$nombre.' '.$apellido; 
            }else{
                return 'Existe otra sesión abierta, ciérrale primero';
            }                                                   
        }else{
            return "Sesion no iniciada";
        } 
    }

    private static function datosCliente(){
        if (isset($_SESSION["usuario"])) {
            if(!isset($_SESSION["es_admin"])){             
                $usuario = $_SESSION["usuario"];
                $nombre = $_SESSION["nombre"];            
                $coeficiente = $_SESSION["coeficiente"];
                $datos = array ($nombre, $coeficiente); //Se devuelven los datos del cliente
                return json_encode($datos); 
            }else{
                return 'Existe otra sesión abierta, ciérrale primero';
            }                                
        }else{
            return "Sesión no iniciada";
        } 
    }
}

echo ControlSesion::selector();

