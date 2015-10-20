function cargandoSitio(opciones, mensaje, duracion) {
  $("#cargandoSitio").show();
   if(opciones!=null) {
    var opts = opciones;
  }
  else {
    var opts = {
      lines: 13, // The number of lines to draw
      length: 11, // The length of each line
      width: 5, // The line thickness
      radius: 17, // The radius of the inner circle
      corners: 1, // Corner roundness (0..1)
      rotate: 0, // The rotation offset
      color: '#FFF', // #rgb or #rrggbb
      speed: 1, // Rounds per second
      trail: 60, // Afterglow percentage
      shadow: false, // Whether to render a shadow
      hwaccel: false, // Whether to use hardware acceleration
      className: 'spinner', // The CSS class to assign to the spinner
      zIndex: 2e9, // The z-index (defaults to 2000000000)
      top: 'auto', // Top position relative to parent in px
      left: 'auto' // Left position relative to parent in px
    };
  }
  var target = $("#loaderSitio");
  var spinner = new Spinner(opts).spin(target);
  if(mensaje==null){
    mensaje = "Cargando, por favor espere";
  }
  if(duracion==null) {
    duracion=null;
  }
  var notification = iosOverlay({
    text: mensaje,
    duration: duracion,
    spinner: spinner
  });

  return notification;
}

function sitioCargado(notificacion){
   if(notificacion!=null){
      notificacion.hide();
   }
   $("#cargandoSitio").fadeOut(200);
}

function lanzarModal(titulo, textoCuerpo, textoBoton) {
  var myModal = $('#myModalGeneral');
  
  myModal.modal('hide');
  
  modalTitle = myModal.find('#modal-title-general');
  modalTitle.html(titulo);
  
  modalBody = myModal.find('#modal-body-general');
  modalBody.html(textoCuerpo);
  
  modalBody = myModal.find('#modal-button-general');
  modalBody.html(textoBoton);
  
  myModal.modal('show');
}

    function mostrarAlumnos(id,section,codigo,alumno){
       //alert(alumno+"gg");
        var obj_data = {"codigo" : codigo,"alumno" : alumno,"parcial":$("#hdparcial").val()};
        $.ajax
        ({
            type: 'post',
            url: section,
            data: obj_data,
            dataType: "json",
            beforeSend: function( )
            { $( "#content-"+id ).html("Cargando, espere por favor...");
                //$( "#content-"+id ).html( "<div id='loading-bar-spinner-relative'><div class='spinner-icon'></div></div>" );
                
            },
            success: function(data) 
            {
               
		if(data.error === true){
                    $( "#loading-bar-spinner-relative" ).remove();
                    msg_alert = alert_bootstrap( id, 'Atenci&oacute;n', data.msg, 'sm', 'alert');
                    $( "#content-"+id ).append( msg_alert );
                    $('#modal-'+id).modal('show');
                } else if(data.modalOverBody) //MODAL SOBRE EL CUERPO DE LA PAGINA
                    {
                            var title = data.title;
                            var content = data.html;
                            var type = data.typeModalOverBody || 'alert';
                            var size = data.sizeModalOverBody || 'md';

                            createModalOverBody(title, content, size, type);
                    }
                
                else{
                    $( "#content-"+id ).html(data.html);
                    }
            }
        });
    }
	
function confirm(form, item)
    {     //alert("55");
        var msg_alert = "", msg = "", functions = "";
        
        $( "#modal-"+form ).remove();
        
        switch(form)
        {   
            case 'actualizaAsis':
               
                msg = "<center>"
                        + " Esta Seguro que desea actualizar sus asistencias?"
                      +"</center>";
                
                functions = ["send_form2('"+form+"')"];
                 msg_alert= alert_bootstrap( form, 'Confirmaci&oacute;n', msg, 'md', 'confirm', functions);
                
                break;
            
           case 'ingresoAsis':
               
                msg = "<center>"
                        + " Esta Seguro que desea guardar sus asistencias?"
                      +"</center>";
                
                functions = ["send_form2('"+form+"')"];
                 msg_alert= alert_bootstrap( form, 'Confirmaci&oacute;n', msg, 'md', 'confirm', functions);
                
                break;
            case 'ingresonotas':
               
                msg = "<center>"
                        + " Esta Seguro que desea guardar sus notas?"
                      +"</center>";
                
                functions = ["send_form2('"+form+"')"];
               
                 msg_alert= alert_bootstrap( form, 'Confirmaci&oacute;n', msg, 'md', 'confirm', functions);
                
                break;
        }
             //alert(msg_alert+"22");
             $('#modal-'+form).modal({backdrop: false});
        $('#form-'+form ).append( msg_alert );
        $('.modal').css("background","rgba(0, 0, 0, 0)");
        $('#modal-'+form).modal('show');
        $('.modal').css("background","rgba(0, 0, 0, 0)");

        
        return false;
    }
    
           function formularioCargar(id,section){
	
        
          close_modal(id);
		//var obj_data = {'tipo':$("#tipo_agendamiento").val()};
                var obj_data = {'tipo':"hola"};
                 
		$.ajax
        ({
            type: 'post',
            url: section,
            data: obj_data,
            dataType: "html",
            beforeSend: function( )
            {
                $( "#content-"+id ).html( "<div id='loading-bar-spinner-relative'><div class='spinner-icon'></div></div>" );
            },
            success: function(data) 
            {
				$( "#content-"+id ).html( data );
            }
        });
        return false;
	} 
        
   function send_form(form, removeModalBody)
    { 
        formSubmitted = form;
        
        $.validator.setDefaults
        ({
            submitHandler: function()
            { 
				var mensaje_cedula = '';
				
				mensaje_cedula = valida_cedula();
			
				if(mensaje_cedula != '')
				{
					$( "#modal-"+form ).remove();
					$( "#content-"+form ).css('display','');

					msg_alert = alert_bootstrap( form, 'Atenci&oacute;n', mensaje_cedula, 'sm', 'alert');
					$( "#form-"+form ).append( msg_alert );
					$('#modal-'+form).modal('show');
				}
				else
				{
					var modal_size = '';
					var form_user = $( "#form-"+form );
					var form_data = form_user.serialize();
					var form_action = form_user.attr('action');
					var deleteModalBody = removeModalBody || 'S';

					var loading_bar = '<div id="loading-bar-spinner">'
										+ '<div class="spinner-icon"></div>'
									+ '</div>';
					
					var backdrop_modal = '<div id="modal-'+form+'" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">'
											+ '<div class="modal-dialog '+modal_size+'">'
											+ '</div>'
										+ '</div>';
									
					var msg_alert = '';
					
					$.ajax
					({
						type: 'post',
						url: form_action,
						data: form_data,
						dataType: "json",
						beforeSend: function( )
						{
							if(deleteModalBody == 'S')
							{
								deleteModalOverBody();
							}
							
							$( "#modal-"+form ).remove();
							
							if($("#content-main-"+form).length)
							{
								$( "#content-main-"+form ).append( backdrop_modal );
								$( "#content-main-"+form ).append( loading_bar );
							}
							else
							{
								$( "#content-"+form ).append( backdrop_modal );
								$( "#content-"+form ).append( loading_bar );
							}
							
							$('#modal-'+form).modal('show');
						},
						success: function(data) 
						{
							modal_size = data.btnSize || 'sm';
							
							$( "#modal-"+form ).remove();

							if(data.error) //SI EXISTE ALGÚN ERROR
							{
								$( "#content-"+form ).css('display','');

								msg_alert = alert_bootstrap( form, 'Atenci&oacute;n', data.msg, modal_size, 'alert');
								$( "#form-"+form ).append( msg_alert );
								$('#modal-'+form).modal('show');
                                                                
							}
							else if(data.anotherDivError) //SI EXISTE ALGÚN ERROR
							{
								$( "#content-"+form ).css('display','');

								msg_alert = alert_bootstrap( form, 'Atenci&oacute;n', data.msg, modal_size, 'alert');
								$( "#content-main-"+form ).append( msg_alert );
								$('#modal-'+form).modal('show');
							}
							else if(data.redirect) //SI SE REQUIERE UNA REDIRECCIÓN HA ALGUNA PÁGINA ESPECÍFICA
							{	
								location.href = data.msg;
							}
							else if(data.withoutModal) //CARGAR EL CONTENIDO SIN MOSTRAR MODALS
							{	
								$( "#content-"+form ).html( data.html );
							}
							else if(data.modalOverBody) //MODAL SOBRE EL CUERPO DE LA PAGINA
							{
								var title = data.title;
								var content = data.html;
								var type = data.typeModalOverBody || 'alert';
								var size = data.sizeModalOverBody || 'md';

								createModalOverBody(title, content, size, type);
							}
							else if(data.isHtml) //CARGAR EL CONTENIDO ANTES DE MOSTRAR EL MODAL
							{	
								msg_alert = alert_bootstrap( form, 'Confirmaci&oacute;n', data.msg, modal_size, 'alert');
								
								$( "#content-"+form ).html( data.html );
								$( "#form-"+form ).append( msg_alert );
								$('#modal-'+form).modal('show');
							}
							else //PRIMERO MOSTRAR EL MODAL Y LUEGO RECARGA EL CONTENIDO AL CERRAR EL MODAL
							{
								if(data.withFunction)
								{
								   functions = [data.function];
								}
								else
								{
								   functions = ["reload_content('"+form+"', '"+ servidor + data.section +"')"]; 
								}
								
								msg_alert = alert_bootstrap( form, 'Confirmaci&oacute;n', data.msg, modal_size, 'alert', functions);

								$( "#form-"+form ).append( msg_alert );
								$('#modal-'+form).modal('show');
							}
							
							$( "#loading-bar-spinner" ).remove();
						},
						error: function()
						{
							$( "#loading-bar-spinner" ).remove();
							$( "#modal-"+form ).remove();
							
							var title = 'Error';
							var content = 'Nos encontramos en mantenimiento de nuestros servidores, por favor intenta nuevamente m&aacute;s tarde.';
							var type = 'alert';
							var size = 'sm';

							createModalOverBody(title, content, size, type);
						}
					});
				}
            }
        });

     
        
        return false;
    }
    
 
   function send_form2(form, removeModalBody)
    {  
           var arr_checked = [];
           var arr_unchecked = [];
		var i=0;
     
					var modal_size = '';
					var form_user = $( "#form-"+form );
					var form_data = form_user.serialize();
                                        
                                         
                $("input:checkbox:checked").each(function(){
	              arr_checked[i]=$(this).val();
                           i++;   }); 
                    i=0;
                $("input:checkbox:not(:checked)").each(function(){
	                 arr_unchecked[i]=$(this).val();
                           i++;   });
                       
                    var arr_datos1=JSON.stringify(arr_checked);
                    var arr_datos2=JSON.stringify(arr_unchecked);
                    //var arr_datos=arr_checked.toString();
                    if(form==='ingresoAsis' ){
                        
                       
                        var form_data = {'arr_checked':arr_datos1,'arr_unchecked':arr_datos2,'materia':$('#hdmateria').val()};
                    }
                                        
                                        
					var form_action = form_user.attr('action');
					var deleteModalBody = removeModalBody || 'S';

					var loading_bar = '<div id="loading-bar-spinner">'
										+ '<div class="spinner-icon"></div>'
									+ '</div>';
					
					var backdrop_modal = '<div id="modal-'+form+'" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">'
											+ '<div class="modal-dialog '+modal_size+'">'
											+ '</div>'
										+ '</div>';
									
					var msg_alert = '';
					
					$.ajax
					({
						type: 'post',
						url: form_action,
						data: form_data,
						dataType: "json",
						beforeSend: function( )
						{
							if(deleteModalBody == 'S')
							{
								deleteModalOverBody();
							}
							
							$( "#modal-"+form ).remove();
							
							
								$( "#content-"+form ).append( backdrop_modal );
								$( "#content-"+form ).append( loading_bar );
							
							$('#modal-'+form).modal('show');
						},
						success: function(data) 
						{
							modal_size = data.btnSize || 'sm';
							
							$( "#modal-"+form ).remove();

							if(data.error) //SI EXISTE ALGÚN ERROR
							{ 
								$( "#content-"+form ).css('display','');
                                                               // close_modal(form);
								msg_alert = alert_bootstrap( form, 'Atenci&oacute;n', data.msg, modal_size, 'alert');
								if(form == 'ingresonotas'){
                                                               $( "#content-ingresonotas" ).append( msg_alert );}
                                                             else{   $( "#form-"+form ).append( msg_alert );}
                                                                
								$('#modal-'+form).modal('show');
                                                                
							}
							else if(data.anotherDivError) //SI EXISTE ALGÚN ERROR
							{
								$( "#content-"+form ).css('display','');

								msg_alert = alert_bootstrap( form, 'Atenci&oacute;n', data.msg, modal_size, 'alert');
								$( "#content-main-"+form ).append( msg_alert );
								$('#modal-'+form).modal('show');
							}
							else if(data.redirect) //SI SE REQUIERE UNA REDIRECCIÓN HA ALGUNA PÁGINA ESPECÍFICA
							{	
								location.href = data.msg;
							}
							else if(data.withoutModal) //CARGAR EL CONTENIDO SIN MOSTRAR MODALS
							{	
								$( "#content-"+form ).html( data.html );
							}
							else if(data.modalOverBody) //MODAL SOBRE EL CUERPO DE LA PAGINA
							{
								var title = data.title;
								var content = data.html;
								var type = data.typeModalOverBody || 'alert';
								var size = data.sizeModalOverBody || 'md';

								createModalOverBody(title, content, size, type);
							}
							else if(data.isHtml) //CARGAR EL CONTENIDO ANTES DE MOSTRAR EL MODAL
							{	
								msg_alert = alert_bootstrap( form, 'Confirmaci&oacute;n', data.msg, modal_size, 'alert');
								
								$( "#content-"+form ).html( data.html );
								$( "#form-"+form ).append( msg_alert );
								$('#modal-'+form).modal('show');
							}
							else //PRIMERO MOSTRAR EL MODAL Y LUEGO RECARGA EL CONTENIDO AL CERRAR EL MODAL
							{
								if(data.withFunction)
								{
								   functions = [data.function];
								}
								else
								{
								   functions = ["reload_content('"+form+"', '"+ servidor + data.section +"')"]; 
								}
								
								msg_alert = alert_bootstrap( form, 'Confirmaci&oacute;n', data.msg, modal_size, 'alert', functions);

								$( "#form-"+form ).append( msg_alert );
								$('#modal-'+form).modal('show');
							}
							
							$( "#loading-bar-spinner" ).remove();
						},
						error: function()
						{
							$( "#loading-bar-spinner" ).remove();
							$( "#modal-"+form ).remove();
							
							var title = 'Error';
							var content = 'Nos encontramos en mantenimiento de nuestros servidores, por favor intenta nuevamente m&aacute;s tarde.';
							var type = 'alert';
							var size = 'sm';

							createModalOverBody(title, content, size, type);
						}
					});
            

        
        return false;
    }
    
    function createModalOverBody(title, content, size, type, functions)
    {
        deleteModalOverBody();
         //close_modal(id);
        
        var btn_size = size || 'md';
        var type_modal = type || 'advertises';
        var functions_buttons = functions || false;
        
        if(title == 'Activación de Cuenta')
        {
            functions_buttons = ["window.location.href = '"+servidor+"home'"];
        }
        
        var a = document.createElement("div");
            a.className="modal-scrollbar-measure",
            $( "body" ).append(a);

        var paddingRight = a.offsetWidth - a.clientWidth;
        $( "body" ).css("padding-right", paddingRight );
        $( "body" ).css("overflow-y", "hidden" );

        var msg_alert = alert_bootstrap( 'body', title, content, btn_size, type_modal, functions_buttons);
        $( "body" ).prepend( msg_alert );
        $( "#menu" ).css('z-index','999');
        $('#modal-body').modal('show');
    }
    
    
       function deleteModalOverBody()
    {
        $( "#menu" ).css('z-index','9999');
        $( "#modal-body" ).remove();
    }
    