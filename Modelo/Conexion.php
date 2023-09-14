<?php 
class Conexion {    

    static $mysqli;
    static $conexion;
    static $resultset;

    public static function conectar(){     

        /*$user = "root";
        $pass = "Tato1432";
        $server = "127.0.0.1";
        $db = "priotti";*/

        /*$user = "id2833154_santiagorrosa";
        $pass = "Tato1432";
        $server = "localhost";
        $db = "id2833154_priotti";*/

        $user = "felipepr_spauser";
        $pass = "mariorosa1432";
        $server = "localhost";
        $db = "felipepr_wbsiteapp";
        //Comentario
    
        self::$mysqli = new mysqli($server, $user, $pass, $db);

        if (self::$mysqli->connect_errno) {            
            echo "Erro de conexion a la base de datos";       
            
        }

        return self::$mysqli;
    } 

    public static function get($query){
        //Nuevo metodo que reemplaza a "conectar()"
        self::$conexion = self::conectar();
        self::$resultset = self::$conexion->query($query);         
        if( self::$resultset->num_rows < 1){                     
            return false;
        } 
        //$resultset->free();          
        //$conexion->close();               
        return self::$resultset;
    }

    public static function set($query){
        self::$conexion = self::conectar();
        self::$conexion->query($query);        
        self::$conexion->close();               
    }

    public static function cerrar(){
        self::$resultset->free(); 
        self::$conexion->close();
    }
    
} 

?> 