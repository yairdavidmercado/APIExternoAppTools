<?php
 
 include 'conexion.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 //thabitacion.tipo_hab AS tipo_habitacion,
// DB table to use

error_reporting(0);

$perfil = isset($_GET['perfil']) ? $_GET['perfil']: 3;

$limit = 10;
$page = isset($_GET['page']) ? $_GET['page']: 1;
$start = ($page -1) * $limit;
$end = $limit+$start;
$admision = isset($_GET['admision']) ? $_GET['admision']: '';

$table = <<<EOT
 (
    SELECT  *
FROM    (
    SELECT
    R_REGISTRO_CONV.NU_NUME_REG_RECO AS admision,
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
    REGISTRO.CD_CODI_CAMA_REG AS cama, 
    PISOS.DE_DESC_PISO AS piso, 
    HABITACIONES.DE_DESC_HABI AS habitacion,
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
    THEN 'NACIDO EN LA INSTITUCION' END AS tipo_ingreso,
    (SELECT
    COUNT(R_REGISTRO_CONV.NU_NUME_REG_RECO) AS admision
    FROM
    HABITACIONES INNER JOIN
    CAMAS ON HABITACIONES.CD_CODI_HABI = CAMAS.CD_CODI_HABI_CAMA INNER JOIN
    PABELLONES ON HABITACIONES.CD_CODI_PABE_HABI = PABELLONES.CD_CODI_PABE INNER JOIN
    PISOS ON PABELLONES.CD_CODI_PISO_PABE = PISOS.CD_CODI_PISO INNER JOIN
    R_REGISTRO_CONV INNER JOIN
            CONVENIOS ON R_REGISTRO_CONV.NU_NUME_CONV_RECO = CONVENIOS.NU_NUME_CONV INNER JOIN
            REGISTRO ON R_REGISTRO_CONV.NU_NUME_REG_RECO = REGISTRO.NU_NUME_REG INNER JOIN
            PACIENTES ON REGISTRO.NU_HIST_PAC_REG = PACIENTES.NU_HIST_PAC INNER JOIN
            FACTURAS_CONTADO ON R_REGISTRO_CONV.NU_NUME_FACO_RECO = FACTURAS_CONTADO.NU_NUME_FACO INNER JOIN
            FACTURAS ON R_REGISTRO_CONV.NU_NUME_FAC_RECO = FACTURAS.NU_NUME_FAC INNER JOIN
            EPS ON CONVENIOS.CD_NIT_EPS_CONV = EPS.CD_NIT_EPS ON CAMAS.CD_CODI_CAMA = REGISTRO.CD_CODI_CAMA_REG
    WHERE        (CONVENIOS.CD_CODI_CONV = 'EVENTOUCI') AND R_REGISTRO_CONV.NU_NUME_REG_RECO LIKE '%$admision%') AS totalrows,
    ROW_NUMBER() OVER ( ORDER BY FE_INGR_REG DESC ) AS RowNum
    FROM
    HABITACIONES INNER JOIN
    CAMAS ON HABITACIONES.CD_CODI_HABI = CAMAS.CD_CODI_HABI_CAMA INNER JOIN
    PABELLONES ON HABITACIONES.CD_CODI_PABE_HABI = PABELLONES.CD_CODI_PABE INNER JOIN
    PISOS ON PABELLONES.CD_CODI_PISO_PABE = PISOS.CD_CODI_PISO INNER JOIN
    R_REGISTRO_CONV INNER JOIN
            CONVENIOS ON R_REGISTRO_CONV.NU_NUME_CONV_RECO = CONVENIOS.NU_NUME_CONV INNER JOIN
            REGISTRO ON R_REGISTRO_CONV.NU_NUME_REG_RECO = REGISTRO.NU_NUME_REG INNER JOIN
            PACIENTES ON REGISTRO.NU_HIST_PAC_REG = PACIENTES.NU_HIST_PAC INNER JOIN
            FACTURAS_CONTADO ON R_REGISTRO_CONV.NU_NUME_FACO_RECO = FACTURAS_CONTADO.NU_NUME_FACO INNER JOIN
            FACTURAS ON R_REGISTRO_CONV.NU_NUME_FAC_RECO = FACTURAS.NU_NUME_FAC INNER JOIN
            EPS ON CONVENIOS.CD_NIT_EPS_CONV = EPS.CD_NIT_EPS ON CAMAS.CD_CODI_CAMA = REGISTRO.CD_CODI_CAMA_REG
    WHERE        (CONVENIOS.CD_CODI_CONV = 'EVENTOUCI') AND R_REGISTRO_CONV.NU_NUME_REG_RECO LIKE '%$admision%'
    ) AS RowConstrainedResult
WHERE   RowNum >= $start
    AND RowNum < $end
 ) temp
EOT;
 
// Table's primary key
$primaryKey = '1';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
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
    array( 'db' => 'RowNum',  'dt' => 'RowNum' ),
    array( 'db' => 'totalrows',  'dt' => 'totalrows' ),
    array( 'db' => 'admision',  'dt' => 'admision' ),
    array( 'db' => 'tipo_documento',  'dt' => 'tipo_documento' ),
    array( 'db' => 'historia',  'dt' => 'historia' ),
    array( 'db' => 'paciente',  'dt' => 'paciente' ),
    array( 'db' => 'aseguradora',  'dt' => 'aseguradora' ),
    array( 'db' => 'cama',  'dt' => 'cama' ),
    array( 'db' => 'piso',  'dt' => 'piso' ),
    array( 'db' => 'habitacion',  'dt' => 'habitacion' ),
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

// $sql_details = array(
//     'user' => 'sa',
//     'pass' => 'admin',
//     'db'   => 'eValuamoS',
//     'host' => '192.168.1.178',
//     'charset' => 'utf8'
// );
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);

