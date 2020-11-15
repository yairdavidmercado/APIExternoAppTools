<?php
 include 'conexion.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

//error_reporting(0);

$perfil = isset($_GET['perfil']) ? $_GET['perfil']: 3;

$limit = 10;
$page = isset($_GET['page']) ? $_GET['page']: 1;
$start = ($page -1) * $limit;
$nombre = isset($_GET['nombre']) ? $_GET['nombre']: '';
$table = <<<EOT
 (
    SELECT R_REGISTRO_CONV.NU_NUME_REG_RECO AS admision,
    CASE WHEN PACIENTES.NU_TIPD_PAC = 1 
    THEN 'TI' 
    WHEN PACIENTES.NU_TIPD_PAC = 2 
    THEN 'RC' WHEN PACIENTES.NU_TIPD_PAC = 0 
    THEN 'CC' WHEN PACIENTES.NU_TIPD_PAC = 3 
    THEN 'CE' WHEN PACIENTES.NU_TIPD_PAC = 4 
    THEN 'PA' WHEN PACIENTES.NU_TIPD_PAC = 5 
    THEN 'AS' WHEN PACIENTES.NU_TIPD_PAC = 6 
    THEN 'MS' WHEN PACIENTES.NU_TIPD_PAC = 7 
    THEN 'NV' WHEN PACIENTES.NU_TIPD_PAC = 11 
    THEN 'PET' WHEN PACIENTES.NU_TIPD_PAC = 12 
    THEN 'PE' END AS tipo_documento, 
    PACIENTES.NU_HIST_PAC AS historia,  
    CONCAT(PACIENTES.NO_NOMB_PAC, ' ', PACIENTES.NO_SGNO_PAC, ' ', PACIENTES.DE_PRAP_PAC, ' ', PACIENTES.DE_SGAP_PAC)  AS paciente, 
    EPS.NO_NOMB_EPS AS aseguradora, 
    REGISTRO.FE_INGR_REG AS fecha_ingreso, 
    REGISTRO.FE_SALI_REG AS fecha_salida,
    CASE WHEN REGISTRO.NU_TIAT_REG = '0' 
    THEN 'URGENCIAS' 
    WHEN REGISTRO.NU_TIAT_REG = '1' 
    THEN 'HOSPITALIZACION' 
    WHEN REGISTRO.NU_TIAT_REG = '2' 
    THEN 'CONSULTA EXTERNA' END AS tipo_habitacion,
    CASE WHEN REGISTRO.NU_VIIN_REG = 0 
    THEN 'URGENCIAS' 
    WHEN REGISTRO.NU_VIIN_REG = 1 
    THEN 'CONSULTA EXTERNA' 
    WHEN REGISTRO.NU_VIIN_REG = 2 
    THEN 'REMITIDO' 
    WHEN REGISTRO.NU_VIIN_REG = 3 
    THEN 'NACIDO EN LA INSTITUCION' END AS tipo_ingreso
    FROM R_REGISTRO_CONV INNER JOIN
            CONVENIOS ON R_REGISTRO_CONV.NU_NUME_CONV_RECO = CONVENIOS.NU_NUME_CONV INNER JOIN
            REGISTRO ON R_REGISTRO_CONV.NU_NUME_REG_RECO = REGISTRO.NU_NUME_REG INNER JOIN
            PACIENTES ON REGISTRO.NU_HIST_PAC_REG = PACIENTES.NU_HIST_PAC INNER JOIN
            FACTURAS_CONTADO ON R_REGISTRO_CONV.NU_NUME_FACO_RECO = FACTURAS_CONTADO.NU_NUME_FACO INNER JOIN
            FACTURAS ON R_REGISTRO_CONV.NU_NUME_FAC_RECO = FACTURAS.NU_NUME_FAC INNER JOIN
            EPS ON CONVENIOS.CD_NIT_EPS_CONV = EPS.CD_NIT_EPS
    WHERE        (CONVENIOS.CD_CODI_CONV = 'EVENTOUCI')
 ) temp
EOT;
 
// Table's primary key
$primaryKey = '1';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

// $columns = array(
//     array(
//         'db' => 'admision',
//         'dt' => 'DT_RowId',
//         'formatter' => function( $d, $row ) {
//             // Technically a DOM id cannot start with an integer, so we prefix
//             // a string. This can also be useful if you have multiple tables
//             // to ensure that the id is unique with a different prefix
//             return 'row_'.$d;
//         }
//     ),
//     array( 'db' => 'admision',  'dt' => 'admision' ),
//     array( 'db' => 'auditado',  'dt' => 'auditado' ),      
//     array( 'db' => 'paciente',   'dt' => 'paciente' ),
//     array( 'db' => 'cama',     'dt' => 'cama' ),
//     array( 'db' => 'habitacion',     'dt' => 'habitacion' ),
//     array( 'db' => 'tipo_habitacion',     'dt' => 'tipo_habitacion' ),
//     array( 'db' => 'piso',     'dt' => 'piso' ),
//     array( 'db' => 'aseguradora',     'dt' => 'aseguradora' ),
//     array( 'db' => 'fecha_ingreso', 'dt' => 'fecha_ingreso'),
//     array( 'db' => 'finalizacion', 'dt' => 'finalizacion'),
// );

$columns = array(
    array(
        'db' => 'admision',
        'dt' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            // Technically a DOM id cannot start with an integer, so we prefix
            // a string. This can also be useful if you have multiple tables
            // to ensure that the id is unique with a different prefix
            return 'row_'.$d;
        }
    ),
    array( 'db' => 'admision',  'dt' => 'admision' ),
    array( 'db' => 'tipo_documento',  'dt' => 'tipo_documento' ),
    array( 'db' => 'historia',  'dt' => 'historia' ),
    array( 'db' => 'paciente',  'dt' => 'paciente' ),
    array( 'db' => 'aseguradora',  'dt' => 'aseguradora' ),
    array( 'db' => 'fecha_ingreso',  'dt' => 'fecha_ingreso' ),
    array( 'db' => 'fecha_salida',  'dt' => 'fecha_salida' ),
    array( 'db' => 'tipo_ingreso',  'dt' => 'tipo_ingreso' ),
    array( 'db' => 'tipo_habitacion',  'dt' => 'tipo_habitacion' )
);
 
 //pgsql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME, DB_USER, DB_PASS);
// SQL server connection information
$sql_details = array(
    'user' => DB_USER,
    'pass' => DB_PASS,
    'db'   => DB_NAME,
    'host' => DB_HOST,
    'charset' => 'utf8'
);


require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);
