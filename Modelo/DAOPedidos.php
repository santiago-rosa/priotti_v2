<?php 

session_start();

include_once "Conexion.php";

class DaoPedidos {  
 
    public static function selector($opc){
        if(self::esUsuario()){
            switch($opc){  
                case 1:                    
                return self::getJson();
                break;
                case 2:    
                $a = $_POST["articulo"];     
                return self::agregar($a);
                break;
                case 3:    
                $a = $_POST["articulo"];                                    
                return self::sacar($a);
                break;
                case 4:    
                $a = $_POST["articulo"];                                    
                return self::actCantidad($a);
                break;
                case 5:                                                            
                return self::getHtml();
                break;
                case 6:                                                            
                return self::getJsonHistorico();
                break;                                                 
                default: 
                return "Sin selección";                   
                break;
            }                             
        }
        return 'No autorizado';                       
    }    

    private static function getJson(){
        $items = self::getItems();
        if($items != ""){
            $items = explode(",", $items);
            foreach($items as $item){
                if($item != ""){
                    $aux = explode("&", $item);
                    $aux = ['codigo' => $aux[0], 'marca' => $aux[1], 'cantidad' => $aux[2]];
                    $pedido["data"][] = $aux; //Asi lo lee datatables
                }
            }
            return json_encode($pedido);
        }else{
            return '{ "data": [] }';
        } 
    } //Para datatables

    private static function getHtml(){
        $items = self::getItems();
        $salida = "";
        if($items != ""){
            $items = explode(",", $items);
            $salida .= "<table width='100%' cellspacing ='2' cellpadding='0' border='0' align='center' bgcolor='#999999'><thead><tr><th>Codigo</th><th>Marca</th><th>Cantidad</th></tr></thead>";
            foreach($items as $item){
                if($item != ""){
                    $aux = explode("&", $item);
                    $salida .= "<tr bgcolor='#ffffff'><td align='center'>".$aux[0]."</td><td align='center'>".$aux[1]."</td><td align='center'>".$aux[2]."</td></tr>";
                }
            }
            self::cerrarPedido();
            return $salida."<tfoot><tr><th>Codigo</th><th>Marca</th><th>Cantidad</th></tr></tfoot><table>";           
        }else{
            return false;
        } 
    } //Devuelve los items en formato html para enviar por email

    private static function cerrarPedido(){
        $id = $_SESSION["id"]; 
        $query = 'update pedidos set estado = "LISTO", fechapedido = now() where cliente = '.$id.' and estado = "PENDIENTE"';
        Conexion::set($query);
        return 'Pedido cerrado';
    }

    private static function agregar($art){
        $id = $_SESSION["id"];         
        $existe = false;
        $items = self::getItems(); 
        if($items == ""){
            $items = $art.',';
            self::update($items);
        }else{
            $aux = explode("&", $art);                               
            $itemsexp = explode(",", $items);
            foreach($itemsexp as $item){
                $item = explode("&", $item);
                if($item[0] == $aux[0]){
                    $existe = true;
                    break;
                }
            }
            if(!$existe){
                $items = $art.','.$items;          
                self::update($items);
            }
        }   
        return self::getJson();
    } //El item debe ser formato codigo&marca&cant,codio&marca&cant,...

    public static function actCantidad($art){  
        $id = $_SESSION["id"]; 
        $itemsact = "";      
        $items = self::getItems(); 
        $aux = explode("&", $art);
        if(is_numeric($aux[2]) && $aux[2]>=0){
            $items = explode(",", $items);
            foreach($items as $item){
                if($item != ""){
                    $aux2 = explode("&", $item);
                    if($aux2[0] == $aux[0]){
                        $itemsact .= $art.',';
                    }else{
                        $itemsact .= $item.',';
                    }
                }  
            }
        self::update($itemsact);
        }                                
        return self::getJson();
    } //El item formato codigo&marca&cant,codio&marca&cant,...

    private static function esUsuario(){
        if (isset($_SESSION["usuario"])) return true;
        return false;              
    }   

    private static function getItems(){
        $id = $_SESSION["id"]; 
        self::crearPendiente();        
        $query = 'select items from pedidos where cliente = '.$id.' and estado = "PENDIENTE"';
        $resultset = Conexion::get($query);
        if($resultset != false){
            $row = $resultset->fetch_assoc();
            return $row["items"];
        }else{
            return "";
        }
        
    } //Devuelve string formato: codigo&marca&cant,codigo&marca&cant,...

    private static function crearPendiente(){
        $id = $_SESSION["id"];
        if(!self::hayPendiente($id)){
            $query = 'insert into pedidos (cliente, estado) values ('.$id.', "PENDIENTE")';
            Conexion::set($query);
            return 'Pedido creado';
        }
    } //crea pedido pendiente si no hubiese uno ya creado

    private static function hayPendiente($id){
        $query = 'select idpedidos from pedidos where cliente = '.$id.' and estado = "PENDIENTE"';
        if( Conexion::get($query) == false) return false;
        return true;
    } //true si ya hay un pedido pendiente del cliente

    private static function update($items){
        $id = $_SESSION["id"];
        $query = 'update pedidos set items = "'.$items.'" where cliente = '.$id.' and estado = "PENDIENTE"';
        Conexion::set($query);
        return 'Actualizado'; 
    } //Actualiza el campo items de un pedido

    private static function sacar($codigo){
        $id = $_SESSION["id"]; 
        $itemsact = "";
        $items = self::getItems();                
        $items = explode(",", $items);
        foreach($items as $item){
            if($item != ""){
                $aux = explode("&", $item);
                if($aux[0] != $codigo){
                    $itemsact .= $item.','; 
                }
            }
        }
        self::update($itemsact);        
        return self::getJson();  
    }    

    public static function getJsonHistorico(){
        $id = $_SESSION["id"]; 
        $query = 'select fechapedido, items from pedidos where cliente = '.$id.' and estado = "LISTO"';
        $resultset = Conexion::get($query);
        if($resultset != false){
            while($row = $resultset->fetch_assoc()){
                $row['items'] = str_replace(',','<br>', str_replace('&','//',$row['items']));                
                $pedido["data"][] = ["fecha"=>$row['fechapedido'], "items"=>$row['items']]; //Asi lo lee datatables         
            }
            return json_encode($pedido);
        }else{
            return '{ "data": [] }';
        } 
    }

}

if(isset($_POST["opcpedido"])) echo DaoPedidos::selector($_POST["opcpedido"]);







