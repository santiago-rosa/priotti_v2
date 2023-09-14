<?php

class Importador{

    public static function importar(){        
        if (isset($_FILES['file']['name'])) {
            if (0 < $_FILES['file']['error']) {
                return 'Error en la carga' . $_FILES['file']['error'];
            } else {
                if (file_exists('../Resources/uploads/' . $_FILES['file']['name'])) {
                    return 'El archivo ya existe : uploads/' . $_FILES['file']['name'];
                } else {
                    move_uploaded_file($_FILES['file']['tmp_name'], '../Resources/uploads/' . $_FILES['file']['name']);
                    return 'Subido correctamente : uploads/' . $_FILES['file']['name'];
                }
            }
        } else {
            return 'Seleccione un archivo';
        }
    }

}

echo Importador::importar();
 


