<?php
   namespace Titulacion\SisAcademicoBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Titulacion\SisAcademicoBundle\Helper\UgServices;

   class DocentesController extends Controller
   {
      var $v_error =false;
      var $v_html ="";
      var $v_msg  ="";
      var $v_message="";
      var $idCarrera="";

      public function indexAction(Request $request) //(Request $request)
      {
         $session=$request->getSession();

         $perfilEst   = $this->container->getParameter('perfilEst');
         $perfilDoc   = $this->container->getParameter('perfilDoc');
         $perfilAdmin = $this->container->getParameter('perfilAdmin'); 
         $perfilEstDoc = $this->container->getParameter('perfilEstDoc'); 
         $perfilEstAdm = $this->container->getParameter('perfilEstAdm'); 
         $perfilDocAdm = $this->container->getParameter('perfilDocAdm');

         if($session->has("perfil")) {
            if($session->get('perfil') == $perfilDoc || $session->get('perfil') == $perfilEstDoc || $session->get('perfil') == $perfilDocAdm){
               $idDocente     = $session->get('id_user');
               //$idDocente     = 1;
             
               $UgServices    = new UgServices;
               $datosCarrerasXML  = $UgServices->Docentes_getCarreras($idDocente);
               
               if($datosCarrerasXML!="") {
                  $datosCarreras = $datosCarrerasXML;
               }
               else {
               # Docente sin Carreras
               }
            
               $datosDocente	= array( 'idDocente' => $idDocente );
             
               return $this->render('TitulacionSisAcademicoBundle:Docentes:listadoCarreras.html.twig',
    									array(
    											'data' => array('datosDocente' => $datosDocente,  'datosCarreras' => $datosCarreras)
    										 )
                              );
            }else{
               $this->get('session')->getFlashBag()->add(
                                'mensaje',
                                'Los datos ingresados no son válidos'
                            );
               return $this->render('TitulacionSisAcademicoBundle:Home:login.html.twig');
            }
         }else{
            $this->get('session')->getFlashBag()->add(
                                'mensaje',
                                'Los datos ingresados no son válidos'
                            );
            return $this->render('TitulacionSisAcademicoBundle:Home:login.html.twig');
        }

      }
		
		
    public function listadoMateriasAction(Request $request)
      {	 
	 $session=$request->getSession();
         $idDocente  = $session->get('id_user');
	 //$idDocente  = $request->request->get('idDocente');
         $idCarrera  = $request->request->get('idCarrera');
         
         $datosDocente	= array( 'idDocente' => $idDocente );
         $datosCarrera2	= array( 'idCarrera' => $idCarrera );
         $datosMaterias	= array();
         //$idDocente = "1";
         //$idCarrera = "2";
		 
         $UgServices    = new UgServices;
         $datosMaterias  = $UgServices->Docentes_getMaterias($idDocente, $idCarrera);
/*
         if($datosMateriasXML!="") {
               foreach($datosMateriasXML->registros->registro as $datosCarrera) {
                  array_push($datosMaterias, (array)$datosCarrera);
               }
         }*/
         //para el render realmente deberia estar mandando la informacion de las materias
       
         return $this->render('TitulacionSisAcademicoBundle:Docentes:listadoMaterias.html.twig',
                        array(
                              'data' => array(
                                             'datosDocente' => $datosDocente,
                                             'datosCarrera' => $datosCarrera2,
                                             'datosMaterias' => $datosMaterias
                                        )
                        )
                     );
      }
		
      
		
      public function listadoAlumnosMateriaAction(Request $request)
      {
         $session=$request->getSession();
         $idDocente  = $session->get('id_user');
         //$idDocente= 7;
         $idMateria     = $request->request->get('idMateria');
         $idParalelo    = $request->request->get('idParalelo');
         $idCarrera     = $request->request->get('idCarrera');
         $fechaInicio   = $request->request->get('fechaInicio');
         $fechaFin      = $request->request->get('fechaFin');
         
         if( !isset($fechaInicio) || !isset($fechaFin) ){
            date_default_timezone_set ( "America/Guayaquil" );
            $day           = date('w');
            $fechaFin      = date('d-m-Y');
            $day--;
            $fechaInicio   = date('d-m-Y', strtotime('-'.($day).' days'));
         }
         else {
            $fechaFin      = str_replace("/","-",$fechaFin);
            $fechaInicio   = str_replace("/","-",$fechaInicio);
         }
         $anioConsulta  = date('o');
         
         $datosConsulta	= array( 
                                 'fechaInicio' => $fechaInicio,
                                 'fechaFin' => $fechaFin,
                                 'idDocente' => $idDocente,
                                 'idMateria' => $idMateria,
                                 'idParalelo' => $idParalelo,
                                 'anio' => $anioConsulta,
                                 'idCarrera' => $idCarrera
                                 );
         $UgServices    = new UgServices;
         $datosAsistenciasXML  = $UgServices->Docentes_getAsistenciasMaterias($datosConsulta);
         
         $dataAsistencia   = array();
         $arregloFechas    = array();
         
         if($datosAsistenciasXML!=NULL) {
            //PARA OBTENER EL ARREGLO DE FECHAS
            foreach($datosAsistenciasXML["alumno"][0] as $keyFecha => $valueFecha){
               //var_dump($keyFecha);
               //$regExp = "/(f)([0-9]{2}\\-[0-9]{2}\\-[0-9]{4})/";
               $regExp = "/(f)([0-9]{4}\\-[0-9]{2}\\-[0-9]{2})/";
               $tempFecha['diaVal'] = '';
               $tempFecha['diaNom'] = '';
               if(preg_match($regExp, $keyFecha, $matchesFecha)){
                  $tempFecha['diaVal'] = substr($keyFecha, 1);
                  //$tempFecha['diaVal'] =  date("d/m/Y",strtotime($tempFecha['diaVal']));     //Cambio de formato
                  $tempFecha['diaNom'] = $this->nombresDias( date('l', strtotime($tempFecha['diaVal'])) );
                  $tempFecha['diaVal'] =  date("d/m/Y",strtotime($tempFecha['diaVal']));     //Cambio de formato
                  array_push($arregloFechas, $tempFecha);
               }
            }
            //PARA GRABAR LOS ESTADOS DE LAS ASISTENCIAS POR ALUMNO
            foreach($datosAsistenciasXML["alumno"] as $dataAlumno){
               $dataAsistenciaReg   = array();
               $dataAsistenciaReg['nombres']   = $dataAlumno['nombres'];
               $dataAsistenciaReg['apellidos'] = $dataAlumno['apellidos'];
               $dataAsistenciaReg['fechas']    = array();
               //Para procesar las fechas que me han llegado, son dinamicas
               foreach($dataAlumno as $keyFecha => $valueFecha){
                  //$regExp = "/(f)([0-9]{2}\\-[0-9]{2}\\-[0-9]{4})/";
                  $regExp = "/(f)([0-9]{4}\\-[0-9]{2}\\-[0-9]{2})/";
                  if(preg_match($regExp, $keyFecha, $matchesFecha)){
                     array_push($dataAsistenciaReg['fechas'], $valueFecha);
                  }
               }
               
               array_push($dataAsistencia, $dataAsistenciaReg);
            }
         }

         return $this->render('TitulacionSisAcademicoBundle:Docentes:listadoAlumnosMateria.html.twig',
                         array(
                               'dataMateria' => array('fechasAsistencia' => $arregloFechas,
                                                      'datosAsistencia' => $dataAsistencia,
                                                      'fechaInicio'  => date("d/m/Y",strtotime($fechaInicio)),
                                                      'fechaFin'  => date("d/m/Y",strtotime($fechaFin)),
                                                      'idMateria' => $idMateria
                                                     )
                             )
                      );
      }
		
		public function notasAlumnosMateriaAction(Request $request)
      {
               $UgServices    = new UgServices;
               $idDocente="";
               $idCarrera="";
                 $trama ="<PI_ID_CICLO_DETALLE>18567</PI_ID_CICLO_DETALLE>
                         <PI_ID_USUARIO_PROFESOR>5</PI_ID_USUARIO_PROFESOR>
                         <PI_ID_MATERIA>251</PI_ID_MATERIA>
                         <PARCIAL>1</PARCIAL>
                         <PI_ESTUDIANTE>2</PI_ESTUDIANTE>";
               $datosParciales  = $UgServices->Docentes_gettareaxparcial($trama);
               
               for ($i=1; $i<=$datosParciales->registro[0]->cantParciales; $i++)
            {
                     $arr_parcial[$i]['parcial']='parcial #'.$i;
                          
            }
               $tareas= $datosParciales->registro[0]->periodos->periodo[0]->componentePeriodo;
               $i=0;
               foreach ($tareas->idNota as $idnota) {
               $registros[$i]['idNota']= (string)$idnota;
               $i++;
               }
               $i=0;
               foreach ($tareas->componente as $componente) {
               $registros[$i]['componente']= (string)$componente;
               $i++;
               }
//               print_r($registros);
//               exit();
               
               $idMateria  = $request->request->get('idMateria');
              // print_r($datosParciales);
              // echo $datosParciales->registro[0]->cantParciales;
               // print_r($datosParciales[0]['periodos']);
               //echo $datosParciales[0]['cantparciales'];
               $session=$request->getSession();
               $session->set("idMateria",$idMateria);
               
               for ($i=1; $i<=$datosParciales->registro[0]->cantParciales; $i++)
            {
                     $arr_parcial[$i]['parcial']='parcial #'.$i;
                          
            }
       //Menu de Notas por Materia para Profesor
       return $this->render('TitulacionSisAcademicoBundle:Docentes:notasAlumnosMateria.html.twig',
                         array(
                               'condition' => '',
                               'arr_parcial' => $arr_parcial,
                               'idMateria' => $idMateria
                             )
                      );
      }
      
      
      
      public function listadoNotasAlumnosMateriaAction(Request $request)
      {
         $session=$request->getSession();
         $idDocente  = $session->get('id_user');
         $idMateria  = $request->request->get('idMateria');
         //$idParalelo  = $request->request->get('idParalelo');
         //$idParalelo  = 1;

         $datosConsulta	= array( 'idMateria' => $idMateria,
                                 'idDocente' => $idDocente);
         
         $UgServices       = new UgServices;
         $datosNotasArray  = $UgServices->Docentes_getNotasMaterias($datosConsulta);
         
         $dataProcesar = $datosNotasArray["registro"][0];
         
         $datosGeneralesListado["notaMinima"]	= $dataProcesar["notaMinima"];
         $datosGeneralesListado["idProfesor"]	= $dataProcesar["idProfesor"];
         $datosGeneralesListado["profesor"]		= $dataProcesar["profesor"];
         $datosGeneralesListado["idMateria"]		= $dataProcesar["idMateria"];
         $datosGeneralesListado["materia"]		= $dataProcesar["materia"];
         $datosGeneralesListado["idParalelo"]	= $dataProcesar["idParalelo"];
         $datosGeneralesListado["paralelo"]		= $dataProcesar["paralelo"];


         foreach($dataProcesar["periodos"]["periodo"] as $periodoCheck) {
            if(is_numeric($periodoCheck["parcial"])) {
               $nombreKey	= "Parcial_".strtolower(str_replace(" ","_",$periodoCheck["parcial"]));
            }
            else {
               $nombreKey	= strtolower(str_replace(" ","_",$periodoCheck["parcial"]));
            }

            $periodosMostrar[$nombreKey]           		= array();
            $periodosMostrar[$nombreKey]["componente"]		= array();

            $iComponente = 0;
            foreach($periodoCheck["componentePeriodo"] as $keyComp => $componente) {
               if($keyComp=="idNota") {
                  $periodosMostrar[$nombreKey]["idComponente"]	= $componente;
               }
               if($keyComp=="componente") {
                  $periodosMostrar[$nombreKey]["componente"]		= $componente;
               }
            }
            $periodosMostrar[$nombreKey]["cantComponentes"] = count($periodosMostrar[$nombreKey]["componente"]);
            $periodosMostrar[$nombreKey]["totalizar"]		= $periodoCheck["totalizar"];
            if($periodosMostrar[$nombreKey]["totalizar"]=="SI") {
               $periodosMostrar[$nombreKey]["cantComponentes"]++;
               array_push($periodosMostrar[$nombreKey]["idComponente"], "99999999");
               array_push($periodosMostrar[$nombreKey]["componente"], "total");
            }
         }

         //var_dump($periodosMostrar);
         //var_dump($dataProcesar["estudiantes"]["estudiante"]);
         //echo count($dataProcesar["estudiantes"]["estudiante"]);

         $datosEstudiantes	= array();
         foreach($dataProcesar["estudiantes"]["estudiante"] as $estudiante) {
            $tempArrayEst = NULL;
            $tempArrayEst["idEstudiante"]	= $estudiante["idEstudiante"];
            $tempArrayEst["estudiante"]		= $estudiante["estudiante"];
            $tempArrayEst["ciclo"]			= $estudiante["ciclo"];
            $tempArrayEst["estadoCiclo"]	= $estudiante["estadoCiclo"];
            $tempArrayEst["parciales"]		= array();
            //Creo el array para grabar las notas
            foreach($periodosMostrar as $keyPeriodo => $valuePeriodo) {
               $tempArrayEst["parciales"][$keyPeriodo]		= array();
               $tempComponente	= NULL;
               if(is_array($valuePeriodo["componente"])){
                  foreach($valuePeriodo["componente"] as $componente) {
                     $tempComponente	= strtolower($componente);
                     $tempComponente	= str_replace("á","a",$tempComponente);
                     $tempComponente	= str_replace("é","e",$tempComponente);
                     $tempComponente	= str_replace("í","i",$tempComponente);
                     $tempComponente	= str_replace("ó","o",$tempComponente);
                     $tempComponente	= str_replace("ú","u",$tempComponente);
                     $tempComponente	= str_replace("ñ","n",$tempComponente);

                     $tempArrayEst["parciales"][$keyPeriodo][$tempComponente] = "-";
                  }
               }
               elseif($valuePeriodo["componente"]!=NULL) {
                  $tempComponente	= strtolower($valuePeriodo["componente"]);
                  $tempComponente	= str_replace("á","a",$tempComponente);
                  $tempComponente	= str_replace("é","e",$tempComponente);
                  $tempComponente	= str_replace("í","i",$tempComponente);
                  $tempComponente	= str_replace("ó","o",$tempComponente);
                  $tempComponente	= str_replace("ú","u",$tempComponente);
                  $tempComponente	= str_replace("ñ","n",$tempComponente);

                  $tempArrayEst["parciales"][$keyPeriodo][$tempComponente] = "-";
               }
            }


            //Para grabar las notas
            if(isset($estudiante["parciales"]["Parcial"])) {
               //Si entra aqui quiere decir que tiene SOLO UN parcial
               $tempComponente = NULL;
               if(is_numeric($estudiante["parciales"]["Parcial"])) {
                  $keyParcial	= "Parcial_".strtolower(str_replace(" ","_",$estudiante["parciales"]["Parcial"]));
               }
               else {
                  $keyParcial	= strtolower(str_replace(" ","_",$estudiante["parciales"]["Parcial"]));
               }

               if(isset($estudiante["parciales"]["notas"]["nota"]["Nota"])) {
                  //Si entra aqui es porque solo trae una nota (ej.Mejoramiento)
                  $keyComponente	= strtolower($estudiante["parciales"]["notas"]["nota"]["tipoNota"]);
                  $notaComponente	= $estudiante["parciales"]["notas"]["nota"]["Nota"];

                  $tempComponente	= strtolower($keyComponente);
                  $tempComponente	= str_replace("á","a",$tempComponente);
                  $tempComponente	= str_replace("é","e",$tempComponente);
                  $tempComponente	= str_replace("í","i",$tempComponente);
                  $tempComponente	= str_replace("ó","o",$tempComponente);
                  $tempComponente	= str_replace("ú","u",$tempComponente);
                  $keyComponente	= str_replace("ñ","n",$tempComponente);
                  $tempArrayEst["parciales"][$keyParcial][$keyComponente] = $notaComponente;
               }
               else {
                  foreach($estudiante["parciales"]["notas"]["nota"] as $dataComponente){
                     //var_dump($dataComponente);
                     $keyComponente	= strtolower($dataComponente["tipoNota"]);
                     $notaComponente	= $dataComponente["Nota"];

                     $tempComponente	= $keyComponente;
                     $tempComponente	= str_replace("á","a",$tempComponente);
                     $tempComponente	= str_replace("é","e",$tempComponente);
                     $tempComponente	= str_replace("í","i",$tempComponente);
                     $tempComponente	= str_replace("ó","o",$tempComponente);
                     $tempComponente	= str_replace("ú","u",$tempComponente);
                     $keyComponente	= str_replace("ñ","n",$tempComponente);
                     $tempArrayEst["parciales"][$keyParcial][$keyComponente] = $notaComponente;
                  }
                  
               }


            }
            else {
               //Si entra aqui quiere decir que tiene mas de un parcial
               //var_dump($estudiante["parciales"]);
               foreach($estudiante["parciales"] as $keyParcial => $dataParcial) {
                  //var_dump($dataParcial);
                  if(is_numeric($dataParcial["Parcial"])) {
                     $keyParcial	= "Parcial_".strtolower(str_replace(" ","_",$dataParcial["Parcial"]));
                  }
                  else {
                     $keyParcial	= strtolower(str_replace(" ","_",$dataParcial["Parcial"]));
                  }

                  foreach($dataParcial["notas"] as $keyNotas => $dataNotas) {

                     if(isset($dataNotas["tipoNota"])){
                        //Si entra aqui es porque llega solo una nota
                        $keyComponente	= strtolower($dataNotas["tipoNota"]);
                        $notaComponente	= $dataNotas["Nota"];

                        $tempComponente	= $keyComponente;
                        $tempComponente	= str_replace("á","a",$tempComponente);
                        $tempComponente	= str_replace("é","e",$tempComponente);
                        $tempComponente	= str_replace("í","i",$tempComponente);
                        $tempComponente	= str_replace("ó","o",$tempComponente);
                        $tempComponente	= str_replace("ú","u",$tempComponente);
                        $keyComponente	= str_replace("ñ","n",$tempComponente);
                     }
                     else {
                        //var_dump($dataNotas);
                        foreach($dataNotas as $dataComponente){
                           //var_dump($dataComponente);
                           $keyComponente	= strtolower($dataComponente["tipoNota"]);
                           $notaComponente	= $dataComponente["Nota"];

                           $tempComponente	= $keyComponente;
                           $tempComponente	= str_replace("á","a",$tempComponente);
                           $tempComponente	= str_replace("é","e",$tempComponente);
                           $tempComponente	= str_replace("í","i",$tempComponente);
                           $tempComponente	= str_replace("ó","o",$tempComponente);
                           $tempComponente	= str_replace("ú","u",$tempComponente);
                           $keyComponente	= str_replace("ñ","n",$tempComponente);
                           $tempArrayEst["parciales"][$keyParcial][$keyComponente] = $notaComponente;
                        }
                     }

                     $tempArrayEst["parciales"][$keyParcial][$keyComponente] = $notaComponente;
                     if($periodosMostrar[$keyParcial]["totalizar"]=="SI"){
                        $tempArrayEst["parciales"][$keyParcial]["total"]		= $dataParcial["total"];
                     }
                  }				
               }

            }

            array_push($datosEstudiantes, $tempArrayEst);
         }
         
       //listadoMaterias
         return $this->render('TitulacionSisAcademicoBundle:Docentes:listadoNotasMateria.html.twig',
                         array(
                              'datosGenerales' => $datosGeneralesListado,
                              'periodosMostrar' => $periodosMostrar,
                              'datosEstudiantes' => $datosEstudiantes
                             )
                      );
      }
      
      public function visionGeneralMateriaAction(Request $request)
      {
         $idDocente  = $request->request->get('idDocente');
         $idMateria  = $request->request->get('idMateria');
         
         $datosDocente	= array( 'idDocente' => $idDocente );

         $datosMateria	= array( 'idMateria' => $idMateria );

       //listadoMaterias
       return $this->render('TitulacionSisAcademicoBundle:Docentes:visionGeneralMateria.html.twig',
                         array(
                               'dataDocente' => array('datosDocente' => $datosDocente ),
                               'dataMateria' => array('datosMateria' => $datosMateria )
                             )
                      );
      }
      
  
        
               public function mostraralumnos3Action(Request $request)
        { 
            
            $notas='';
            
         $response   		= new JsonResponse();
         $withoutModal       = true;
          
	$nombresalumnos =  array(
                              array( 'Nombrealm' => 'Carlos Quiñonez'),
                              array( 'Nombrealm' => 'Juan Romero'),
                              array( 'Nombrealm' => 'Daniel Verdesoto'),
                              array( 'Nombrealm' => 'Fernando Lopez'),
                              array( 'Nombrealm' => 'Alexandra Gutierrez'),
                              array( 'Nombrealm' => 'Roberto Carlos'),
                              array( 'Nombrealm' => 'Orlando Macias'),
                              array( 'Nombrealm' => 'Fernanda Montero'),
                              array( 'Nombrealm' => 'Ana Kam'),
                              array( 'Nombrealm' => 'Angel Fuentes'),
                           );   
        
			$this->v_html = $this->renderView('TitulacionSisAcademicoBundle:Docentes:ingresoexamen.html.twig',
						  array(
							   'arr_datos'	=> $nombresalumnos,
                                                           'cantidad'   => '',
                                                           'msg'   	=> $this->v_msg
						  ));
                        
                        $response->setData(
                                array(
					'error' 		=> $this->v_error,
					'msg'			=> $this->v_msg,
                                        'html' 			=> $this->v_html,
                                        'withoutModal' 	=> $withoutModal,
                                        'recargar'      => '0'
                                     )
                              );
                        return $response;
        }

     
        public function ingresoexamenAction(Request $request)
        { 
            
            $notas='';
            
            $total =$request->request->get('hdcount');
            for($i=1; $i<$total; $i++)
            {
                $notas['academico1'][] =$request->request->get('academicos1_'.$i);
                $notas['examen1'][] =$request->request->get('examen1_'.$i);
                $notas['academico2'][] =$request->request->get('academicos2_'.$i);
                $notas['examen2'][] =$request->request->get('examen2_'.$i);
                $notas['examen3'][] =$request->request->get('examen3_'.$i);
            }
            print_r($notas) ;
			$pagina = 1;
          
	$nombresalumnos =  array(
                              array( 'Nombrealm' => 'Carlos Quiñonez'),
                              array( 'Nombrealm' => 'Juan Romero'),
                              array( 'Nombrealm' => 'Daniel Verdesoto'),
                              array( 'Nombrealm' => 'Fernando Lopez'),
                              array( 'Nombrealm' => 'Alexandra Gutierrez'),
                              array( 'Nombrealm' => 'Roberto Carlos'),
                              array( 'Nombrealm' => 'Orlando Macias'),
                              array( 'Nombrealm' => 'Fernanda Montero'),
                              array( 'Nombrealm' => 'Ana Kam'),
                              array( 'Nombrealm' => 'Angel Fuentes'),
                           );   
			return $this->render('TitulacionSisAcademicoBundle:Docentes:ingresonotas.html.twig',
						  array(
							   'arr_datos'	=> $nombresalumnos,
                                                           'cantidad'   => '',
                                                           'msg'   	=> $this->v_msg
						  ));
        }
        
        public function consultaNotasAction(Request $request)
        { 
           $notas='';
            
         $response   		= new JsonResponse();
         $withoutModal       = true;
          
	$nombresalumnos =  array(
                              array( 'Nombrealm' => 'Carlos Quiñonez'),
                              array( 'Nombrealm' => 'Juan Romero'),
                              array( 'Nombrealm' => 'Daniel Verdesoto'),
                              array( 'Nombrealm' => 'Fernando Lopez'),
                              array( 'Nombrealm' => 'Alexandra Gutierrez'),
                              array( 'Nombrealm' => 'Roberto Carlos'),
                              array( 'Nombrealm' => 'Orlando Macias'),
                              array( 'Nombrealm' => 'Fernanda Montero'),
                              array( 'Nombrealm' => 'Ana Kam'),
                              array( 'Nombrealm' => 'Angel Fuentes'),
                           );   
        
			$this->v_html = $this->renderView('TitulacionSisAcademicoBundle:Docentes:consultanotas.html.twig',
						  array(
							   'arr_datos'	=> $nombresalumnos,
                                                           'cantidad'   => '',
                                                           'msg'   	=> $this->v_msg
						  ));
                        
                        $response->setData(
                                array(
					'error' 		=> $this->v_error,
					'msg'			=> $this->v_msg,
                                        'html' 			=> $this->v_html,
                                        'withoutModal' 	=> $withoutModal,
                                        'recargar'      => '0'
                                     )
                              );
                        return $response;
        }
        
      function nombresDias($nombreIngles) {
         $diasEspaniol  = array("lunes", "martes", "miércoles", "jueves", "viernes", "sábado", "domingo");
         $diasIngles    = array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday");
         
         $nombreIngles  = str_replace($diasIngles, $diasEspaniol, strtolower($nombreIngles));
         
         return $nombreIngles;
      }
      
        public function tabDocAsistenciasAction(Request $request)
      {
            date_default_timezone_set('America/Buenos_Aires');
         $idMateria     = $request->request->get('idMateria');
         $idCarrera     = $request->request->get('idCarrera');
         $UgServices    = new UgServices;
       //Menu de Notas por Materia para Profesor
         $Parcial='1';
               
               $trama = "<materia>2271</materia>";
                
          $arr_fechas  = $UgServices->Docentes_getfechasparcial($trama);
//          print_r($arr_fechas);
//          exit();
           $muestrafecha="";
           $fecha_act=date('Y-m-d');
           foreach($arr_fechas as $fecha) {
               if ($fecha_act==$fecha['fecha'] && $fecha['ingreso'] == '1' ){
                   $muestrafecha=$fecha_act;
               }
            }
           // echo $fecha_act;
           // echo $arr_fechas[0]['fecha']."--".$fecha_act;
            // print_r($arr_fechas);
         //  exit();
       return $this->render('TitulacionSisAcademicoBundle:Docentes:tabsDocAsistencias.html.twig',
                         array(
                               'condition' => '',
                               'idCarrera' => $idCarrera,
                               'idMateria' => $idMateria,
                               'fecha'=> $muestrafecha,
                             )
                      );
      }
      
          
        public function IngresoAsistenciaAction(Request $request)
        { 
           $notas='';
            date_default_timezone_set('America/Buenos_Aires');
         $response   		= new JsonResponse();
         $withoutModal       = true;
                     $profesor='Apolinario';
            $materia='Calculo';
            $paralelo='S2A';
          
            $notas='';
            $id_Materia =$request->request->get('codigo');
            
            $parcial =$request->request->get('alumno');
            $session=$request->getSession();
               $session->set("parcial",$parcial);
            
            $parametro1 =$request->request->get('parametro1');
            
            $response   		= new JsonResponse();
            $withoutModal       = true;
            
            $fecha=date('d/m/Y');
            $idDocente     = 1;
            $carrera  =1;
            $UgServices    = new UgServices;
            //$idDocente="";
               $idCarrera="";
             $materia="235";
           
               $Parcial='1';
               
               	$trama = "<materiaparalelo>".$materia."</materiaparalelo>";
                
            $arr_datos  = $UgServices->Docentes_getAlumnos($trama);
       
           
           $datosParciales  = $UgServices->Docentes_gettareaxparcial($idDocente,$idCarrera);
            
           /*print_r($datosParciales);
           exit();*/
            $profesor=$datosParciales->registro[0]->profesor;
            $materia=$datosParciales->registro[0]->materia;
            $paralelo=$datosParciales->registro[0]->paralelo;

        
			$this->v_html = $this->renderView('TitulacionSisAcademicoBundle:Docentes:AlumnosIngresoAsistencia.html.twig',
						  array(
							   'arr_datos'	=> $arr_datos,
                                                           'fecha'   => $fecha,
                                                           'profesor'   => $profesor,
                                                           'materia'    => $materia,
                                                           'paralelo'   => $paralelo,
                                                           'id_materia'    => $id_Materia,
                                                           'cantidad'   => '',
                                                           'msg'   	=> $this->v_msg
						  ));
                        
                        $response->setData(
                                array(
					'error' 		=> $this->v_error,
					'msg'			=> $this->v_msg,
                                        'html' 			=> $this->v_html,
                                        'withoutModal' 	=> $withoutModal,
                                        'recargar'      => '0'
                                     )
                              );
                        return $response;
        }
        
           
        public function ingresarAsistenciaAction(Request $request)
        { 
           $notas='';
           date_default_timezone_set('America/Buenos_Aires');
            $session=$request->getSession();
           $idDocente     = $session->get('id_user');
           $id_materia  = $request->request->get('materia');
           $id_materia  ='235';
            
           $arr_checked  = $request->request->get('arr_checked');
           $arr_unchecked  = $request->request->get('arr_unchecked');
           $alumnosa=json_decode($arr_checked);
           $alumnosi=json_decode($arr_unchecked);
           $UgServices    = new UgServices;
           $idDocente='31';
           $estudiante='2';
           //$materia_paralelo='235';
           $fecha='01/06/2015';
           $fecha=date('d/m/Y');
           $id_docente='2'; 
           
               $doc = new \DOMDocument('1.0');
                    
                  $doc->formatOutput = true;
                  $xml = $doc->createElement('PX_XML_CAB');
                  $xml = $doc->appendChild($xml);
                  $items = $doc->createElement('items');
                  $items = $xml->appendChild($items);
                  $item = $doc->createElement('item');
                  $item = $items->appendChild($item);
                  $id_profesor = $doc->createElement('id_profesor');
                  $id_profesor = $item->appendChild($id_profesor);
                  $text = $doc->createTextNode($idDocente);
                  $text = $id_profesor->appendChild($text);
                  $id_estudiante = $doc->createElement('fecha_asistencia');
                  $id_estudiante = $item->appendChild($id_estudiante);
                  $text = $doc->createTextNode($fecha);
                  $text = $id_estudiante->appendChild($text);
                  $id_materia_paralelo = $doc->createElement('id_materia_paralelo');
                  $id_materia_paralelo = $item->appendChild($id_materia_paralelo);
                  $text = $doc->createTextNode($id_materia);
                  $text = $id_materia_paralelo->appendChild($text);
                  $id_usuario = $doc->createElement('id_usuario');
                  $id_usuario = $item->appendChild($id_usuario);
                  $text = $doc->createTextNode($idDocente);
                  $text = $id_usuario->appendChild($text);
                  $xmldet = $doc->createElement('PX_XML_DET');
                  $xmldet = $doc->appendChild($xmldet);
                  $items = $doc->createElement('items');
                  $items = $xmldet->appendChild($items);
                  
           
             foreach($alumnosa as $alumno) {
                 // echo $alumno."-";
                  $item = $doc->createElement('item');
                  $item = $items->appendChild($item);
                  $estado_asistencia = $doc->createElement('estado_asistencia');
                  $estado_asistencia = $item->appendChild($estado_asistencia);
                  $text = $doc->createTextNode('1');
                  $text = $estado_asistencia->appendChild($text);
                  $id_estudiante = $doc->createElement('id_estudiante');
                  $id_estudiante = $item->appendChild($id_estudiante);
                  $text = $doc->createTextNode($alumno);
                  $text = $id_estudiante->appendChild($text);
            }
            foreach($alumnosi as $alumno) {
                 // echo $alumno."-";
                  $item = $doc->createElement('item');
                  $item = $items->appendChild($item);
                  $estado_asistencia = $doc->createElement('estado_asistencia');
                  $estado_asistencia = $item->appendChild($estado_asistencia);
                  $text = $doc->createTextNode('1');
                  $text = $estado_asistencia->appendChild($text);
                  $id_estudiante = $doc->createElement('id_estudiante');
                  $id_estudiante = $item->appendChild($id_estudiante);
                  $text = $doc->createTextNode($alumno);
                  $text = $id_estudiante->appendChild($text);
            }
                  $opcion = $doc->createElement('PC_OPCION');
                  $opcion = $doc->appendChild($opcion);
                  $text = $doc->createTextNode('I');
                  $text = $opcion->appendChild($text);
                  
                  $xmlfinal= $doc->saveXML() . "\n";
                 
                 $xmlfinal= str_replace ( '<?xml version="1.0"?>' , '' , $xmlfinal);
//                 echo $xmlfinal;
//                 exit();
         $response   		= new JsonResponse();
          $respuesta  = $UgServices->Docentes_ingresoAsistencia($xmlfinal);
              
//                 print_r($respuesta);
//                   exit();
            
                 $ar=$respuesta->soapBody->ns2ejecucionObjetoResponse->return;
                 
                 $result=$ar->resultadoObjeto->parametrosSalida->PV_MENSAJE;
                // echo $result;
                // exit();
                 
                      //print $result;
           $mensaje =(string)$result;
        
            $this->v_error	= true;

            $response->setData(
                                array(
                                        'error' => true,
                                        'msg' => $mensaje
                                     )
                              );
            
            return $response;
        }
        
             public function actualizarAsistenciaAction(Request $request)
        { 
           $notas='';
           date_default_timezone_set('America/Buenos_Aires');
            $session=$request->getSession();
           $idDocente     = $session->get('id_user');
           $id_materia  = $request->request->get('materia');
           $fecha=$session->get('combofecha');
           $id_materia  ='235';
            
           $arr_checked  = $request->request->get('arr_checked');
           $arr_unchecked  = $request->request->get('arr_unchecked');
           $alumnosa=json_decode($arr_checked);
           $alumnosi=json_decode($arr_unchecked);
           $UgServices    = new UgServices;
           $idDocente='31';
           $estudiante='2';
           //$materia_paralelo='235';
          // $fecha='01/06/2015';
           //$fecha=date('d/m/Y');
           $id_docente='2'; 
           
               $doc = new \DOMDocument('1.0');
                    
                  $doc->formatOutput = true;
                  $xml = $doc->createElement('PX_XML_CAB');
                  $xml = $doc->appendChild($xml);
                  $items = $doc->createElement('items');
                  $items = $xml->appendChild($items);
                  $item = $doc->createElement('item');
                  $item = $items->appendChild($item);
                  $id_profesor = $doc->createElement('id_profesor');
                  $id_profesor = $item->appendChild($id_profesor);
                  $text = $doc->createTextNode($idDocente);
                  $text = $id_profesor->appendChild($text);
                  $id_estudiante = $doc->createElement('fecha_asistencia');
                  $id_estudiante = $item->appendChild($id_estudiante);
                  $text = $doc->createTextNode($fecha);
                  $text = $id_estudiante->appendChild($text);
                  $id_materia_paralelo = $doc->createElement('id_materia_paralelo');
                  $id_materia_paralelo = $item->appendChild($id_materia_paralelo);
                  $text = $doc->createTextNode($id_materia);
                  $text = $id_materia_paralelo->appendChild($text);
                  $id_usuario = $doc->createElement('id_usuario');
                  $id_usuario = $item->appendChild($id_usuario);
                  $text = $doc->createTextNode($idDocente);
                  $text = $id_usuario->appendChild($text);
                  $xmldet = $doc->createElement('PX_XML_DET');
                  $xmldet = $doc->appendChild($xmldet);
                  $items = $doc->createElement('items');
                  $items = $xmldet->appendChild($items);
                  
           
             foreach($alumnosa as $alumno) {
                 // echo $alumno."-";
                  $item = $doc->createElement('item');
                  $item = $items->appendChild($item);
                  $estado_asistencia = $doc->createElement('estado_asistencia');
                  $estado_asistencia = $item->appendChild($estado_asistencia);
                  $text = $doc->createTextNode('1');
                  $text = $estado_asistencia->appendChild($text);
                  $id_estudiante = $doc->createElement('id_estudiante');
                  $id_estudiante = $item->appendChild($id_estudiante);
                  $text = $doc->createTextNode($alumno);
                  $text = $id_estudiante->appendChild($text);
            }
            foreach($alumnosi as $alumno) {
                 // echo $alumno."-";
                  $item = $doc->createElement('item');
                  $item = $items->appendChild($item);
                  $estado_asistencia = $doc->createElement('estado_asistencia');
                  $estado_asistencia = $item->appendChild($estado_asistencia);
                  $text = $doc->createTextNode('1');
                  $text = $estado_asistencia->appendChild($text);
                  $id_estudiante = $doc->createElement('id_estudiante');
                  $id_estudiante = $item->appendChild($id_estudiante);
                  $text = $doc->createTextNode($alumno);
                  $text = $id_estudiante->appendChild($text);
            }
                  $opcion = $doc->createElement('PC_OPCION');
                  $opcion = $doc->appendChild($opcion);
                  $text = $doc->createTextNode('A');
                  $text = $opcion->appendChild($text);
                  
                  $xmlfinal= $doc->saveXML() . "\n";
                 
                 $xmlfinal= str_replace ( '<?xml version="1.0"?>' , '' , $xmlfinal);
//                 echo $xmlfinal;
//                 exit();
         $response   		= new JsonResponse();
          $respuesta  = $UgServices->Docentes_ingresoAsistencia($xmlfinal);
              
//                 print_r($respuesta);
//                   exit();
            
                 $ar=$respuesta->soapBody->ns2ejecucionObjetoResponse->return;
                 
                 $result=$ar->resultadoObjeto->parametrosSalida->PV_MENSAJE;
                // echo $result;
                // exit();
                 
                      //print $result;
           $mensaje =(string)$result;
        
            $this->v_error	= true;

            $response->setData(
                                array(
                                        'error' => true,
                                        'msg' => $mensaje
                                     )
                              );
            
            return $response;
        }
             public function mostraralumnosAction(Request $request)
        { 
          
            $notas='';
            $parcial =$request->request->get('alumno');
            $materia =$request->request->get('codigo');
            //echo $materia."tt";
            //exit();
            $session=$request->getSession();
               $session->set("parcial",$parcial);
             $parcial ='1';
            
            $parametro1 =$request->request->get('parametro1');
            
            $response   		= new JsonResponse();
            $withoutModal       = true;
         
            $idDocente     = 1;
            $carrera  =1;
            $UgServices    = new UgServices;
            //$idDocente="";
               $idCarrera="";
             $materia="235";
           
               
               
               	$trama = "<materiaparalelo>".$materia."</materiaparalelo>";
                
            $arr_datos  = $UgServices->Docentes_getAlumnos($trama);
            
           
          // print_r($arr_datos);
          // exit();
            
           // echo $arr_datos[0]['nombres'];
           /*$ar=$arr_datos->soapBody->ns2ejecucionConsultaResponse->return;
           echo $ar->idHistorico;*/
           // exit();
          // echo $arr_datos->estado;
            
           $datosParciales  = $UgServices->Docentes_gettareaxparcial($idDocente,$idCarrera);
            
           /*print_r($datosParciales);
           exit();*/
            $profesor=$datosParciales->registro[0]->profesor;
            $materia=$datosParciales->registro[0]->materia;
            $paralelo=$datosParciales->registro[0]->paralelo;

        
			$this->v_html = $this->renderView('TitulacionSisAcademicoBundle:Docentes:AlumnosIngresoNota.html.twig',
						  array(
							   'arr_datos'	=> $arr_datos,
                                                           'cantidad'   => '',
                                                           'profesor'   => $profesor,
                                                           'materia'    => $materia,
                                                           'paralelo'   => $paralelo,
                                                           'pruebaexam'	=> $parametro1, 
                                                           'parcial'	=> $parcial,
                                                           'msg'   	=> $this->v_msg
						  ));
                        $this->v_html=utf8_encode($this->v_html);
                        
                        $response->setData(
                                array(
					'error' 		=> $this->v_error,
					'msg'			=> $this->v_msg,
                                        'html' 			=> utf8_decode($this->v_html),
                                        'withoutModal' 	=> $withoutModal,
                                        'recargar'      => '0'
                                     )
                              );
                        return $response;
        }
        
            public function mostraralumnos2Action(Request $request)
        { 
          
            $notas='';
            $parcial =$request->request->get('alumno');
            $materia =$request->request->get('codigo');
            //echo $materia."tt";
            //exit();
            $session=$request->getSession();
               $session->set("parcial",$parcial);
             $parcial ='2';
            
            $parametro1 =$request->request->get('parametro1');
            
            $response   		= new JsonResponse();
            $withoutModal       = true;
         
            $idDocente     = 1;
            $carrera  =1;
            $UgServices    = new UgServices;
            //$idDocente="";
               $idCarrera="";
             $materia="235";
           
               
               
               	$trama = "<materiaparalelo>".$materia."</materiaparalelo>";
                
            $arr_datos  = $UgServices->Docentes_getAlumnos($trama);
            
           
          // print_r($arr_datos);
          // exit();
            
           // echo $arr_datos[0]['nombres'];
           /*$ar=$arr_datos->soapBody->ns2ejecucionConsultaResponse->return;
           echo $ar->idHistorico;*/
           // exit();
          // echo $arr_datos->estado;
            
           $datosParciales  = $UgServices->Docentes_gettareaxparcial($idDocente,$idCarrera);
            
           /*print_r($datosParciales);
           exit();*/
            $profesor=$datosParciales->registro[0]->profesor;
            $materia=$datosParciales->registro[0]->materia;
            $paralelo=$datosParciales->registro[0]->paralelo;

        
			$this->v_html = $this->renderView('TitulacionSisAcademicoBundle:Docentes:AlumnosIngresoNota.html.twig',
						  array(
							   'arr_datos'	=> $arr_datos,
                                                           'cantidad'   => '',
                                                           'profesor'   => $profesor,
                                                           'materia'    => $materia,
                                                           'paralelo'   => $paralelo,
                                                           'pruebaexam'	=> $parametro1, 
                                                           'parcial'	=> $parcial,
                                                           'msg'   	=> $this->v_msg
						  ));
                        $this->v_html=utf8_encode($this->v_html);
                        
                        $response->setData(
                                array(
					'error' 		=> $this->v_error,
					'msg'			=> $this->v_msg,
                                        'html' 			=> utf8_decode($this->v_html),
                                        'withoutModal' 	=> $withoutModal,
                                        'recargar'      => '0'
                                     )
                              );
                        return $response;
        }
        
          
      public function ingresarNotaAction(Request $request)
        { 
             $response   		= new JsonResponse();
              $session=$request->getSession();
               $idCarrera=$session->get('idCarrera');
              // $idDocente=$session->get('idDocente');
               $idMateria=$session->get('idMateria');
               $parcial=$session->get('hdparcial');
              // $alumno=$session->get('codalumno');
               
               $idDocente     = $session->get('id_user');
          
                $alumno =$request->request->get('hdalumno');
                $parcial =$request->request->get('hdparcial');
            //echo $parcial."ppt";
            //exit();
                    
                 $total =$request->request->get('hdcount');
                 
       
                  $UgServices    = new UgServices;
                  
                    $doc = new \DOMDocument('1.0');
                    
                  $doc->formatOutput = true;
                  $xml = $doc->createElement('px_xml');
                  $xml = $doc->appendChild($xml);
                  $notas = $doc->createElement('Notas');
                  $notas = $xml->appendChild($notas);
                 for($i=1; $i<=$total; $i++)
            {     
                  $root = $doc->createElement('Nota');
                  $root = $notas->appendChild($root);
                  $carrera = $doc->createElement('id_estudiante_carrera_materia');
                  $carrera = $root->appendChild($carrera);
                  $text = $doc->createTextNode($alumno);
                  $text = $carrera->appendChild($text);
                  
                  /*$materia = $doc->createElement('idMateria');
                  $materia = $root->appendChild($materia);
                  $text = $doc->createTextNode($idMateria);
                  $text = $materia->appendChild($text);*/
                  $TipoNota = $doc->createElement('idTipoNota');
                  $TipoNota = $root->appendChild($TipoNota);
                  $text = $doc->createTextNode($request->request->get('hdtarea_'.$i));
                  $text = $TipoNota->appendChild($text);
                  $parciales = $doc->createElement('parcial');
                  $parciales = $root->appendChild($parciales);
                  $text = $doc->createTextNode($parcial);
                  $text = $parciales->appendChild($text);
                  $calificacion = $doc->createElement('calificacion');
                  $calificacion = $root->appendChild($calificacion);
                  $text = $doc->createTextNode($request->request->get('academicos_'.$i));
                  $text = $calificacion->appendChild($text);
                  $docente = $doc->createElement('id_sg_usuario');
                  $docente = $root->appendChild($docente);
                  $text = $doc->createTextNode($idDocente);
                  $text = $docente->appendChild($text);
            }
                  $opcion = $doc->createElement('pc_opcion');
                  $opcion = $doc->appendChild($opcion);
                  $text = $doc->createTextNode('A');
                  $text = $opcion->appendChild($text);
                  
                  $xmlfinal= $doc->saveXML() . "\n";
                 
                 $xmlfinal= str_replace ( '<?xml version="1.0"?>' , '' , $xmlfinal);
                  $respuesta  = $UgServices->Docentes_ingresoNotas($xmlfinal);
                //print ($notas);
               //    print_r($respuesta);
               //    exit();
            
                 $ar=$respuesta->soapBody->ns2ejecucionObjetoResponse->return;
                 
                 $result=$ar->resultadoObjeto->parametrosSalida->PV_MENSAJE;
                // echo $result;
                // exit();
                 
                      //print $result;
           $mensaje =(string)$result;
        
            $this->v_error	= true;

            $response->setData(
                                array(
                                        'error' => true,
                                        'msg' => $mensaje
                                     )
                              );
            
            return $response;
        }
        
     public function ingresonotasAction(Request $request)
        { 
             $response   		= new JsonResponse();
             
              $UgServices    = new UgServices;
               $idDocente="";
               $idCarrera="";
               $datosParciales  = $UgServices->Docentes_gettareaxparcial($idDocente,$idCarrera);
               //print_r($datosParciales);
               $trama ="<PI_ID_CICLO_DETALLE>18567</PI_ID_CICLO_DETALLE>
                         <PI_ID_USUARIO_PROFESOR>5</PI_ID_USUARIO_PROFESOR>
                         <PI_ID_MATERIA>251</PI_ID_MATERIA>
                         <PARCIAL>1</PARCIAL>
                         <PI_ESTUDIANTE>2</PI_ESTUDIANTE>";
               $datosParciales  = $UgServices->Docentes_gettareaxparcial($trama);
            
               $tareas1= $datosParciales->registro[0]->periodos->periodo[0]->componentePeriodo;
               $i=0;
               foreach ($tareas1->idNota as $idnota) {
               $tareas[$i]['idNota']= (string)$idnota;
               $i++;
               }
               $i=0;
               foreach ($tareas1->componente as $componente) {
               $tareas[$i]['componente']= (string)$componente;
               $i++;
               }
//               print_r($registros);
//               exit();
          //echo $i;
               // print_r($datosParciales->registro[0]->periodos[0]->periodo[0]->componentePeriodo->componente);
           // print_r($tareas);
                $alumno =$request->request->get('alumno');
                $codigo =$request->request->get('codigo');
                $parcial =$request->request->get('parcial');
                $session=$request->getSession();
                $session->set("codalumno",$codigo);
            /*
                    $tareas =  array(
                              array( 'tarealm' => 'leccion1'),
                              array( 'tarealm' => 'leccion2'),
                              array( 'tarealm' => 'taller1'),
                              array( 'tarealm' => 'taller2'),
                           );*/
           
			$this->v_html = $this->renderView('TitulacionSisAcademicoBundle:Docentes:ingresoNotas.html.twig',
						  array(
							   'codigo'	=> $codigo,
                                                           'alumno'	=> $alumno,
                                                           'arr_tareas'   => $tareas,
                                                           'parcial'   =>$parcial,
                                                           'msg'   	=> $this->v_msg
						  ));
                    
                        
                       /* $response->setData(
                                array('html' 			=> $this->v_html,
                                    'error'=>true,
                                    'msg'=>'por fin salio'
                                     )
                              );
                        return $response;*/
                           
           
            $title 		= 'Ingreso de Nota';
            $typeModalOverBody 	= 'advertises';
            $sizeModalOverBody 	= 'lg';
            $modalOverBody	= true;
                  $this->v_html=utf8_encode($this->v_html);
            $response->setData(
                                array(
                                        'anotherDivError' => $this->v_error,
                                        'msg' => trim($this->v_message),
                                        'modalOverBody' => $modalOverBody,
                                        'html' => utf8_decode($this->v_html),
                                        'title' => $title,
                                        'typeModalOverBody' => $typeModalOverBody,
                                        'sizeModalOverBody' => $sizeModalOverBody
                                     )
                              );
            
            return $response;
        }
             public function actualizaAsisAction(Request $request)
        { 
           $notas='';
            date_default_timezone_set('America/Buenos_Aires');
         $response   		= new JsonResponse();
         $withoutModal       = true;
                     $profesor='Apolinario';
            $materia='Calculo';
            $paralelo='S2A';
          
            $notas='';
            $parcial =$request->request->get('alumno');
            $session=$request->getSession();
               $session->set("parcial",$parcial);
            
            $parametro1 =$request->request->get('parametro1');
            
            $response   		= new JsonResponse();
            $withoutModal       = true;
         
            $idDocente     = 1;
            $carrera  =1;
            $UgServices    = new UgServices;
            //$idDocente="";
               $idCarrera="";
             $materia="235";
       //Menu de Notas por Materia para Profesor
         $Parcial='1';
               
                    $trama = "<materia>2271</materia>";
                
          $arr_fechas  = $UgServices->Docentes_getfechasparcial($trama);
//          print_r($arr_fechas);
//          exit();
           $muestrafecha="";
           $fecha_act=date('Y-m-d');
 
           foreach($arr_fechas as $fecha) {
              
                  $muestrafecha .= '<option value="'.$fecha['fecha'].'">'.$fecha['fecha'].'</option>';
               
            }
           
               $Parcial='1';
               
               	$trama = "<materiaparalelo>".$materia."</materiaparalelo>";
                
            $arr_datos  = $UgServices->Docentes_getAlumnos($trama);
       
           
           $datosParciales  = $UgServices->Docentes_gettareaxparcial($idDocente,$idCarrera);
           /*print_r($datosParciales);
           exit();*/
            $profesor=$datosParciales->registro[0]->profesor;
            $materia=$datosParciales->registro[0]->materia;
            $paralelo=$datosParciales->registro[0]->paralelo;

        
			$this->v_html = $this->renderView('TitulacionSisAcademicoBundle:Docentes:AlumnosActualizaAsistencia.html.twig',
						  array(
							   'arr_datos'	=> $arr_datos,
                                                           'fecha'   => $muestrafecha ,
                                                           'profesor'   => $profesor,
                                                           'materia'    => $materia,
                                                           'paralelo'   => $paralelo,
                                                           'cantidad'   => '',
                                                           'msg'   	=> $this->v_msg
						  ));
                        
                        $response->setData(
                                array(
					'error' 		=> $this->v_error,
					'msg'			=> $this->v_msg,
                                        'html' 			=> $this->v_html,
                                        'withoutModal' 	=> $withoutModal,
                                        'recargar'      => '0'
                                     )
                              );
                        return $response;
        }
        
   }