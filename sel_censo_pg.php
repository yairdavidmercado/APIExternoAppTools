<?php 
 include '../AppToolsAPI/conexion.php';
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);
error_reporting(0); 

        $perfil = isset($_GET['perfil']) ? $_GET['perfil']: 3;

        $limit = 10;
        $page = isset($_GET['page']) ? $_GET['page']: 1;
        $start = ($page -1) * $limit;
        $admision = isset($_GET['admision']) ? $_GET['admision']: '';

        $conn = pg_connect("user=".DB_USER." password=".DB_PASS." port=".DB_PORT." dbname=".DB_NAME." host=".DB_HOST);
        $arr = null;
        $table_censo = file_get_contents('http://localhost/APIExterno/table_censo.php?page='.$page.'&admision='.$admision.'&perfil='.$perfil);
        $table_censo = json_decode($table_censo, TRUE);
        //echo json_encode($table_censo);
        if ($conn) {
                $valor;
                $data = $table_censo["data"];
                for ($x = 0; $x < count($data); $x++) {
                    $valor .= $data[$x]['admision'].',';
                }
                $valor = trim($valor, ',');
                $estadoAuditoria = json_decode(estadoAuditoria($conn, $valor, $perfil ), TRUE);


                $valor2;
                $data2 = $table_censo["data"];
                for ($x = 0; $x < count($data2); $x++) {
                    $cambio = 0;
                    for ($xi = 0; $xi < count($estadoAuditoria); $xi++) {
                        if ($estadoAuditoria[$xi]['cod_admi'] == $data2[$x]['admision'] ) {
                            array_push($table_censo["data"][$x], ['estados' => $estadoAuditoria[$xi]]);
                        }
                    }
                }
                $none = json_decode('{"0":"","final":"","1":"","cod_admi":""}', TRUE);
                for ($x = 0; $x < count($data2); $x++) {
                    array_push($table_censo["data"][$x], ['estados' => $none]);
                }
                
                $manage["success"] = true;
                $manage["message"] = "Inicio de sesión éxitoso.";
                echo json_encode($table_censo);
        }
        else{
        $response["success"] = false;
        $response["message"] = "No se pudo establecer conexion con el servidor";
        // echo no users JSON
        echo json_encode($response);
        }      
    pg_close($conn);

    function estadoAuditoria($conn, $cods_admi, $perfil ){
        $result = pg_query($conn, 	"SELECT 
        (SELECT 
        CASE id_bloqueo::text 
        WHEN NULL 
        THEN 'NO' 
        ELSE 'SI' END 
        FROM 
        wfinalizacion_auditoria 
        WHERE id_bloqueo = cod_admi 
        AND perfil::integer = $perfil ) as final,
        (SELECT terminado 
        FROM wauditorias 
        WHERE cod_admi = a.cod_admi 
        AND anulado <> true 
        AND perfil::integer = $perfil
        order by cod_audi ASC limit 1 ) AS estado,
        cod_admi 
        FROM wauditorias as a WHERE cod_admi IN($cods_admi) 
        AND perfil::integer = $perfil
        AND anulado <> true GROUP BY 1,2,3");
        if(pg_num_rows($result) > 0)
        {	
            $response["resultado"] = array();
            while ($row = pg_fetch_array($result)) {
            $datos = array();

                $datos["final"] 			= $row["final"];
                $datos["cod_admi"]			= $row["cod_admi"];
                $datos["estado"]			= $row["estado"];
                
                // push single product into final response array
                array_push($response["resultado"], $row);
            }
            // $response["success"] = true;
            // $response["message"] = "Inicio de sesión éxitoso.";
            return json_encode($response['resultado']);

        }else{
        // $response["success"] = false;
        // $response["message"] = "El usuario o contraseña no coincide.";
        // echo no users JSON
        //echo json_encode($response);
        }
    }
?>