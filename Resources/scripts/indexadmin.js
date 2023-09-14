$(document).ready(function () {

    var coef = 1;
    var tabla = null;
    var tablac = null;
    var busqueda = "";
    var busquedaux = "";
    var timeout = null;
    var tipolista = 2;
    var confotos = false;
    var op = false;
    

    //INICIO   

    clickednav($("#productos"));   
    
    //Compruebo sesión existente en recarga de pagina
    checkreload();

    //NAV BAR

    //nav bar productos
    $("#productos").on("click", function (e) {
        clickednav($(this));
        togglebotones(true);
        showtablac(false);
        showtablap(true);
        setjumbo("Productos", "Administrador");
        return false;
    });

    $("#clientes").on("click", function (e) {
        clickednav($(this));
        togglebotones(false);
        showtablap(false);
        showtablac(true);
        setjumbo("Clientes", "Administrador");
        return false;
    });

    //SELECTORES

    //Buscador de productos 
    $("#buscador").on("change paste keyup", function () {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            busqueda = $("#buscador").val();
            busqueda = busqueda.trim();            
            if (busqueda != busquedaux) {
                busquedaux = busqueda;
                tabla.ajax.reload();
            }
        }, 500);
    });

    //Boton Iniciar Sesion
    $("#entrar").on("click", function (e) {
        var datos = $("#datosingreso").serialize() + '&opc=2';
        $("#usuario").val("");
        $("#clave").val("");
        $.ajax({
            method: "POST",
            url: "../Controlador/ControlSesion.php",
            data: datos,
            success: function (data) {
                iniciarsesion(data);
            },
            error: function () {
                toastr.error("Error de servidor", "Error");
            }
        });
        return false;
    });

    //Cerrar Sesion
    $("#salir").on("click", function (e) {
        cerrarsesion();
    });    

    //SELECTORES CLIENTES

    //Checkbox para editar lista de clientes
    $('#checkeditcli').on("click", function () {
        if ($('#checkeditcli').is(':checked')) {
            $('#bnuevocliente').fadeIn("oculto");
            toastr.warning("Modo Editable");
        } else {
            $('#bnuevocliente').fadeOut("oculto");
        }
    });

    //Modificacion de Clientes (sacando tbody del selector funciono el .on "click")
    $('#tablac').on('click', 'tr', function () {
        console.log(tablac.row(this).data());
        if ($('#checkeditcli').is(':checked')) {
            var fila = tablac.row(this).data();
            $.confirm({
                title: 'Editar: ' + fila['nombre'],
                content: '' +
                    '<form class="formName" id="actcliente">' +
                    '<div class="form-group">' +
                    '<label>Nº Cliente(usuario)</label>' +
                    '<input type="text" class="form-control numero" name="numero" value="' + fila['numero'] + '" />' +
                    '<label>Cuit(clave)</label>' +
                    '<input type="text" class="form-control cuit" name="cuit" value="' + fila['cuit'] + '" />' +
                    '<label>Nombre</label>' +
                    '<input type="text" class="form-control nombre" name="nombre" value="' + fila['nombre'] + '" />' +
                    '<label>Coeficiente</label>' +
                    '<input type="text" class="form-control" name="aumento" value="' + fila['porcentajeaumento'] + '" />' +
                    '<label>Email</label>' +
                    '<input type="text" class="form-control" name="email" value="' + fila['email'] + '" />' +
                    '<select name="estado"> <option value="ACTIVO">Activo</option> <option value="INACTIVO">Inactivo</option> </select>' +
                    '</div>' +
                    '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Editar',
                        btnClass: 'btn-blue',
                        action: function () {
                            var nombre = this.$content.find('.nombre').val();
                            var numero = this.$content.find('.numero').val();
                            var cuit = this.$content.find('.cuit').val();
                            if (!nombre || !numero || !cuit) {
                                $.alert('Nombre, numero y cuit no puede ser vacíos');
                                return false;
                            }
                            var datos = $('#actcliente').serialize() + '&opc=1' + '&id=' + fila['id'];
                            $.ajax({
                                method: "POST",
                                url: "../Modelo/DAOClientes.php",
                                data: datos,
                                success: function (data) {
                                    if (data.includes('actualizado')) {
                                        toastr.success(data);
                                        tablac.ajax.reload(null, false);
                                    } else {
                                        toastr.error(data, "Error");
                                    }
                                },
                                error: function () {
                                    toastr.error("No se pudo conectar al servidor", "Error");
                                }
                            });
                        }
                    },
                    cancel: function () {
                        //close
                    },
                }
            });
        }
        return false;
    });

    //Boton para agregar nuevo cliente
    $('#bnuevocliente').on("click", function () {
        $.confirm({
            title: 'Nuevo Cliente: ',
            content: '' +
                '<form class="formName" id="actcliente">' +
                '<div class="form-group">' +
                '<label>Nº Cliente(usuario)</label>' +
                '<input type="text" class="form-control numero" name="numero"/>' +
                '<label>Cuit(clave)</label>' +
                '<input type="text" class="form-control cuit" name="cuit"/>' +
                '<label>Nombre</label>' +
                '<input type="text" class="form-control nombre" name="nombre"/>' +
                '<label>Coeficiente</label>' +
                '<input type="text" class="form-control" name="aumento"/>' +
                '<label>Email</label>' +
                '<input type="text" class="form-control" name="email"/>' +
                '</div>' +
                '</form>',
            buttons: {
                formSubmit: {
                    text: 'Editar',
                    btnClass: 'btn-blue',
                    action: function () {
                        var nombre = this.$content.find('.nombre').val();
                        var numero = this.$content.find('.numero').val();
                        var cuit = this.$content.find('.cuit').val();
                        if (!nombre || !numero || !cuit) {
                            $.alert('Nombre, numero y cuit no puede ser vacíos');
                            return false;
                        }
                        var datos = $('#actcliente').serialize() + '&opc=3';
                        $.ajax({
                            method: "POST",
                            url: "../Modelo/DAOClientes.php",
                            data: datos,
                            success: function (data) {
                                if (data.includes('creado')) {
                                    toastr.success(data);
                                    tablac.ajax.reload();
                                } else {
                                    toastr.error(data, "Error");
                                }
                            },
                            error: function () {
                                toastr.error("No se pudo conectar al servidor", "Error");
                            }
                        });
                    }
                },
                cancel: function () {
                    //close
                },
            }
        });
        return false;
    });    

    //SELECTORES PRODUCTOS 
    
    //Ver solo ofertas
    $("#bofertas").on("click", function () {
        unpressed();
        $(this).addClass("active btn-info");
        if (tipolista != 3) {
            tipolista = 3;
            tabla.ajax.reload();
        }
        return false;
    });
    //Ver todos los precios
    $("#btodos").on("click", function () {
        unpressed();
        $(this).addClass("active btn-info");
        if (tipolista != 2) {
            tipolista = 2;
            tabla.ajax.reload();
        }
        return false;
    });
    //Ver solo novedades
    $("#bnovedades").on("click", function () {
        unpressed();
        $(this).addClass("active btn-info");
        if (tipolista != 4) {
            tipolista = 4;
            tabla.ajax.reload();
        }
        return false;
    });

    //Toggles de las columnas de la tabla de productos
    $('.toggle-vis').on('click', function (e) {
        //e.preventDefault();

        // Get the column API object
        var column = tabla.column($(this).attr('data-column'));

        // Toggle the visibility
        if ($(this).attr('data-column') == 8) {
            togglefotos();
        }
        column.visible(!column.visible());
    });     

    //Checkbox para editar lista de productos (equiv y ofertas)
    $('#checkeditprod').on("click", function () {
        if ($('#checkeditprod').is(':checked')) {
            toastr.warning("Lista editable!");
        }
    });

    //Ventana emergente con editor de ofertas y equiv
    $('#tabla').on('click', 'tr', function () {        
        console.log(tabla.row(this).data());
        if ($('#checkeditprod').is(':checked')) {
            var fila = tabla.row(this).data();
            $.confirm({
                title: 'Editar: ' + fila['codigo'],
                content: '' +
                    '<form class="formName" id="actproducto">' +
                    '<div class="form-group">' +
                    '<label>Precio Oferta:</label>' +
                    '<input type="text" class="form-control" name="ofe" value="' + fila['precio_oferta'] + '" />' +
                    '<label>Equivalencias:</label>' +
                    '<textarea name="equiv" cols="50" rows="10" class="form-control" type="text">' + fila['info'] + '</textarea>' +
                    '</div>' +
                    '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Aplicar',
                        btnClass: 'btn-blue',
                        action: function () {
                            var datos = $('#actproducto').serialize() + '&opc=1' + '&cod=' + fila['codigo'];
                            $.ajax({
                                method: "POST",
                                url: "../Modelo/DAOProductos.php",
                                data: datos,
                                success: function (data) {
                                    if (data.includes("actualizado")) {
                                        toastr.success(data);
                                        tabla.ajax.reload(null, false);
                                    } else {
                                        toastr.error(data, "Error");
                                    }
                                },
                                error: function () {
                                    toastr.error("No se pudo conectar al servidor", "Error");
                                }
                            });
                        }
                    },
                    cancel: function () {
                        //close
                    },
                }
            });
        }
        return false;
    });
    
    //boton para mostrar modulo actualizador
    $("#bimportador").on("click", function () {
        if (!$("#importador").is(":visible")) {
            $("#importador").slideDown();
        } else {
            $("#importador").slideUp();
        }
        return false;
    });   

    //actualizar todo con aprecios, arubros, alineas.txt
    $("#importarlista").on("click", function () {
        $.confirm({
            title: 'Confirme:',
            content: '¿Actualizar lista de precios?',
            buttons: {
                confirmar: function () {                                       
                    $.ajax({
                        method: "POST",
                        url: "../Modelo/DAOActualizar.php",
                        data: "opc=1",
                        beforeSend: function() {
                            $('#msjprocesando').removeClass('oculto'); 
                        },
                        complete: function() {
                            $('#msjprocesando').addClass('oculto');
                        },
                        success: function (data) {
                            if (data.includes("actualizada")) {
                                toastr.success(data);
                                actfechas();
                            } else {
                                toastr.error(data, "Error");
                            }
                        },
                        error: function () {
                            toastr.error("Problemas de conexion", "Error");
                        }
                    });                    
                },
                cancelar: function () {
                    //$.alert('Canceled!');
                }
            }
        });
        return false;
    });

    //actualizar ofertas con ofertas.txt
    $("#importarofertas").on("click", function () {
        $.confirm({
            title: 'Confirme:',
            content: '¿Actualizar ofertas?',
            buttons: {
                confirmar: function () {
                    $.ajax({
                        method: "POST",
                        url: "../Modelo/DAOActualizar.php",
                        data: "opc=2",
                        success: function (data) {
                            if (data.includes("Faltan")) {
                                toastr.success(data, "Ofertas actualizadas");
                                actfechas();
                            } else { toastr.error(data, "Error"); }
                        },
                        error: function () {
                            toastr.error("Problemas de conexion", "Error");
                        }
                    });
                },
                cancelar: function () {
                    //$.alert('Canceled!');
                }
            }
        });
        return false;
    }); 


    $('#upload').on('click', function () {
        subirarchivo();
    });

    //Boton descargar lista .xlsx
    $("#bdescargarlista").on("click", function () {
        descargarxlsx();
        return false;
    });

    //FUNCION DATATABLES

    function tablaproductos() {
        if(tablac == null){
            tabla = $("#tabla").DataTable({
                "bFilter": false,
                "aaSorting": [],
                "language": {                
                    "emptyTable": "No hay coincidencias",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ resultados",
                    "infoEmpty": "No hay resultados",
                    //"infoFiltered": "(filtered from _MAX_ total entries)",
                    //"infoPostFix": "",
                    //"thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ por hoja",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    //"search": "Search:",
                    //"zeroRecords": "No matching records found",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                },
                "responsive": true,
                "ajax": {
                    "method": "POST",
                    "async": false,
                    "url": "../Modelo/DAOProductos.php",
                    "data": function (d) {
                        d.opc = tipolista;
                        d.busqueda = busqueda;
                    }
                },
                "columns": [
                    { "data": "codigo" },
                    { "data": "aplicacion" },
                    { "data": "marca" },
                    { "data": "rubro" },
                    { "data": "info" },
                    { "data": "precio_lista" },
                    { "data": "precio_oferta" },
                    { "data": "precio_oferta" }, //Neto
                    { "data": "imagen" }
                ],
                "columnDefs": [
                    {
                        "targets": [4],
                        "visible": false,
                        "searchable": true
                    },
                    {
                        "targets": [5], //Precio de lista
                        "render": function (data, type, row) {
                            if (row.precio_oferta == 0) {
                                return '$' + Number(data * coef).toFixed(2);
    
                            } else {
                                return '<div class="tachado"> $ ' + Number(data * coef).toFixed(2) + '</div>';
                            }
                        },
                        "visible": true,
                        "searchable": false
                    },
                    {
                        "targets": [6], //Precio de oferta
                        "render": function (data, type, row) {
                            if (row.precio_oferta == 0) {
                                return "---";
                            } else {
                                return '$' + Number(data).toFixed(2);
                            }
    
                        },
                        "visible": true,
                        "searchable": false
                    },
                    {
                        "targets": [7], //Precio neto
                        "render": function (data, type, row) {
                            if (row.precio_oferta == 0) {
                                return "---";
                            } else {
                                return '$' + Number(data * 0.5643).toFixed(2);
                            }
    
                        },
                        "visible": true,
                        "searchable": false
                    },
                    {
                        "targets": [8],
                        "render": function (data, type, row) {
                            if (confotos == 0) return row.imagen;
                            var error = "javascript:this.src='../Resources/fotos/default.jpg'";
                            return '<img src="../Resources/fotos/' + row.imagen + '.jpg" onerror="' + error + '" id="myImg">';                            
                        },
                        "visible": false,
                        "searchable": false
                    }
                ]
            });
        }        
    }

    function tablaclientes() {
        if(tablac == null){
            tablac = $("#tablac").DataTable({
                "responsive": true,
                "language": {                
                    "emptyTable": "No hay coincidencias",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ resultados",
                    "infoEmpty": "No hay resultados",
                    //"infoFiltered": "(filtered from _MAX_ total entries)",
                    //"infoPostFix": "",
                    //"thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ por hoja",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    //"search": "Search:",
                    //"zeroRecords": "No matching records found",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                },
                "ajax": {
                    "method": "POST",
                    "async": true,
                    "url": "../Modelo/DAOClientes.php",
                    "data": function (d) {
                        d.opc = 2;
                    }
                },
                "columns": [
                    { "data": "id" },
                    { "data": "nombre" },
                    { "data": "numero" },
                    { "data": "cuit" },
                    { "data": "email" },
                    { "data": "porcentajeaumento" },
                    { "data": "ultimo" },
                    { "data": "visitas" },
                    { "data": "estado" }
                ],
                "columnDefs": [
                    {
                        "targets": [0],
                        "visible": false,
                        "searchable": false
                    },
                    {
                        "targets": [5],
                        "searchable": false
                    },
                    {
                        "targets": [6],
                        "searchable": true
                    },
                    {
                        "targets": [7],
                        "searchable": false
                    },
                    {
                        "targets": [8],
                        "searchable": false
                    }
                ],
                "order": [[1, 'asc']]
            });
        }        
    }

    //FUNCIONES

    function showtablac(op) {
        if (op === true) {
            $("#tablaclientes").fadeIn();
        } else {
            $("#tablaclientes").hide();
        }
    }

    function showtablap(op) {
        if (op) {
            $("#tablaproductos").fadeIn();
        } else {
            $("#tablaproductos").hide();
        }
    }

    function unpressed() {
        $("#btodos").removeClass("active btn-info");
        $("#bofertas").removeClass("active btn-info");
        $("#bnovedades").removeClass("active btn-info");
    }

    function iniciarsesion(data) {
        if (data.includes('Usuario')) {            
            $("#datosingreso").slideUp();
            $("#datossesion p").text(data);
            $("#datossesion").removeClass("oculto");
            $("#productos").removeClass("oculto");
            $("#clientes").removeClass("oculto");   
            setjumbo("Productos", "Administrador");         
            toastr.success("Bienvenido!");
            toastr.warning("Ojo! =O", "Contenido editable");
            tablaproductos();            
            tablaclientes(); 
            showtablap(true);             
            actfechas();            
            togglebotones(true);
        } else if (data.includes("no iniciada")) {
            $("#datosingreso").slideDown(500);
        } else {
            toastr.error(data, "Error");
        }
    }

    function cerrarsesion() {        
        $.ajax({
            method: "POST",
            url: "../Controlador/ControlSesion.php",
            data: "opc=3",
            success: function (data) {
                if (data.includes('cerrada')) {
                    $("#datossesion").addClass("oculto");
                    showtablac(false);
                    showtablap(false);   
                    setjumbo('Módulo Administrador', '');  
                    togglebotones(false);
                    $("#importador").hide(); 
                    $('#checkeditprod').prop("checked", false);            
                    $("#datossesion p").text("");
                    $("#datosingreso").slideDown(500);
                    $("#usuario").val("");
                    $("#clave").val("");
                    $("#productos").addClass("oculto");
                    $("#clientes").addClass("oculto");                                         
                    toastr.info("Se cerró sesión");
                }
            }
        });
    }

    function descargarxlsx() {
        $.ajax({
            method: "POST",
            url: "../Modelo/DAOProductos.php",
            data: "opc=5",
            async: false,
            success: function (data) {
                if (data.includes('listapriotti')) {
                    data = data.trim();
                    toastr.success("Descargando...", "Lista generada");
                    var path = '../Resources/uploads/' + data + '.xlsx';
                    window.location.href = path;
                } else {
                    toastr.error(data, "Error");
                }
            },
            error: function () {
                toastr.error("Error de conexion", "Error");
            }
        });
    }

    //fechas de act lista y ofertas
    function actfechas() {
        $.ajax({
            method: "POST",
            url: "../Modelo/DAOActualizar.php",
            data: "opc=3",
            success: function (data) {
                data = JSON.parse(data);
                $('#ultimaoferta').text(' ' + data[0]);
                $('#ultimalista').text(' ' + data[1]);
            },
            error: function () {
                toastr.error("Problemas de conexion", "Error");
            }
        });
    }

    function cargarofertas() {
        var file_data = $('#file').prop('files')[0];
        var form_data = new FormData();
        form_data.append('file', file_data);
        $.ajax({
            url: '../Modelo/Importador.php', // point to server-side PHP script 
            dataType: 'text', // what to expect back from the PHP script
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function (response) {
                $('#msg').html(response); // display success response from the PHP script
            },
            error: function (response) {
                $('#msg').html(response); // display error response from the PHP script
            }
        });
    }

    function toggleprecios() {
        if (tablaexiste()) {
            var column = tabla.column(5);
            column.visible(!column.visible());
            var column = tabla.column(6);
            column.visible(!column.visible());
            var column = tabla.column(7);
            column.visible(!column.visible());
        }
    }

    function toggleequiv() {
        if (tablaexiste()) {
            var column = tabla.column(4);
            column.visible(!column.visible());
        }
    }

    function resettogglescolumnas(){
        $('#fotos').prop('checked', false);
        $('#aplicacion').prop('checked', true);
        $('#marca').prop('checked', true);
        $('#rubro').prop('checked', true);
        $('#equivalencias').prop('checked', false);
    }

    function togglefotos() {
        if (tablaexiste()) {            
            confotos = !confotos;
            tabla.ajax.reload();
        }
    }

    function tablaexiste() {
        if (tabla != null) {
            return true;
        } else {
            return false
        }
    }

    function subirarchivo() {
        var file_data = $('#file').prop('files')[0];
        var form_data = new FormData();
        form_data.append('file', file_data);
        $.ajax({
            url: '../Modelo/Importador.php', // point to server-side PHP script 
            dataType: 'text', // what to expect back from the PHP script
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function (response) {
                $('#msg').html(response); // display success response from the PHP script
            },
            error: function (response) {
                $('#msg').html(response); // display error response from the PHP script
            }
        });
    }

    function checkreload() {
        $.ajax({
            method: "POST",
            url: "../Controlador/ControlSesion.php",
            data: "opc=5",
            success: function (data) {
                if(data.includes("otra sesión")){
                    toastr.error(data);
                }else{
                    resettogglescolumnas();
                    iniciarsesion(data);
                }                
            },
            error: function () {
                alert("error de servidor");
            }
        });
    }

    function setjumbo(dis, subdis) {
        $(".jumbotron #titulo").fadeOut();
        $(".jumbotron #subtitulo").fadeOut();
        $(".jumbotron #titulo").text(dis);
        $(".jumbotron #subtitulo").text(subdis);
        $(".jumbotron #titulo").fadeIn();
        $(".jumbotron #subtitulo").fadeIn();
    }

    //muestra u oculta los botones de productos
    function togglebotones(op) {
        if (op === true) {
            $("#botonesprod").fadeIn();
        } else {
            $("#botonesprod").fadeOut();
        }
    }

    //clickea los botones del nav
    function clickednav(sel) {
        $('#productos').removeClass('amarillo');
        $('#empresa').removeClass('amarillo');
        $('#clientes').removeClass('amarillo');
        $('#contacto').removeClass('amarillo');
        sel.addClass('amarillo');
    }

    //VARIOS

    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-center",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

     //BACK TO TOP EN LA PAGINA CUANDO SE PASA A LA SIGUIENTE HOJA
     $('#tablac').on( 'page.dt', function () {
        $('html, body').animate({
            scrollTop: 300
        }, 300);
    } );

    $('#tabla').on( 'page.dt', function () {
        $('html, body').animate({
            scrollTop: 300
        }, 300);
    } );








});





































