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
$parametro = isset($_GET['parametro']) ? $_GET['parametro']: '0';

$table = <<<EOT
 (SELECT R_REGISTRO_CONV.NU_NUME_REG_RECO AS cod_admi,  
 CONCAT(PACIENTES.NO_NOMB_PAC, ' ', PACIENTES.NO_SGNO_PAC, ' ', PACIENTES.DE_PRAP_PAC, ' ', PACIENTES.DE_SGAP_PAC)  AS nom_usua,
 registro.nu_hist_pac_reg AS id_pacien,
 EPS.NO_NOMB_EPS AS nom_contrato,
 FACTURAS_CONTADO.REGIMEN as regimen, 
 REGISTRO.FE_INGR_REG AS fecha_ingre, 
 'xxxx' AS servicio_ingre,
 CASE WHEN REGISTRO.NU_TIAT_REG = '0' 
 THEN 'URGENCIAS' 
 WHEN REGISTRO.NU_TIAT_REG = '1' 
 THEN 'HOSPITALIZACION' 
 WHEN REGISTRO.NU_TIAT_REG = '2' 
 THEN 'CONSULTA EXTERNA' END AS servicio_actual,
 'xxxxxxxxxxxxxxxxx' AS acc_transito,
 REGISTRO.CD_CODI_CAMA_REG AS cama,
 'xxxxxxxxxxxxxxxxx' AS primera_vez,
 'xxxxxxxxxxxxxxxxx' AS finalizado,
 REGISTRO.FE_SALI_REG AS fecha_salida
 FROM 
 HABITACIONES INNER JOIN
 CAMAS ON HABITACIONES.CD_CODI_HABI = CAMAS.CD_CODI_HABI_CAMA INNER JOIN
 PABELLONES ON HABITACIONES.CD_CODI_PABE_HABI = PABELLONES.CD_CODI_PABE INNER JOIN
 PISOS ON PABELLONES.CD_CODI_PISO_PABE = PISOS.CD_CODI_PISO INNER JOIN
 R_REGISTRO_CONV
 INNER JOIN REGISTRO ON R_REGISTRO_CONV.NU_NUME_REG_RECO = REGISTRO.NU_NUME_REG
 INNER JOIN CONVENIOS ON R_REGISTRO_CONV.NU_NUME_CONV_RECO = CONVENIOS.NU_NUME_CONV
 INNER JOIN PACIENTES ON REGISTRO.NU_HIST_PAC_REG = PACIENTES.NU_HIST_PAC
 INNER JOIN FACTURAS_CONTADO ON R_REGISTRO_CONV.NU_NUME_FACO_RECO = FACTURAS_CONTADO.NU_NUME_FACO
 INNER JOIN EPS ON CONVENIOS.CD_NIT_EPS_CONV = EPS.CD_NIT_EPS ON CAMAS.CD_CODI_CAMA = REGISTRO.CD_CODI_CAMA_REG
 WHERE R_REGISTRO_CONV.NU_NUME_REG_RECO = $parametro
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
        'db' => 'cod_admi',
        'dt' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            // Technically a DOM id cannot start with an integer, so we prefix
            // a string. This can also be useful if you have multiple tables
            // to ensure that the id is unique with a different prefix
            return 'row_'.$d;
        }
    ),
    array( 'db' => 'cod_admi',  'dt' => 'cod_admi' ),
    array( 'db' => 'nom_usua',  'dt' => 'nom_usua' ),      
    array( 'db' => 'id_pacien',   'dt' => 'id_pacien' ),
    array( 'db' => 'nom_contrato',     'dt' => 'nom_contrato' ),
    array( 'db' => 'regimen',     'dt' => 'regimen' ),
    array( 'db' => 'fecha_ingre',     'dt' => 'fecha_ingre' ),
    array( 'db' => 'servicio_ingre',     'dt' => 'servicio_ingre' ),
    array( 'db' => 'servicio_actual',     'dt' => 'servicio_actual' ),
    array( 'db' => 'acc_transito', 'dt' => 'acc_transito'),
    array( 'db' => 'cama', 'dt' => 'cama'),
    array( 'db' => 'primera_vez', 'dt' => 'primera_vez'),
    array( 'db' => 'finalizado', 'dt' => 'finalizado'),
    array( 'db' => 'fecha_salida', 'dt' => 'fecha_salida')
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
