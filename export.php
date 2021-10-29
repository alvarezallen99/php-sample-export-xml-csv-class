<?php
/**
 * put your code here
 * It is recommended to show your coding skills in OOP
 */

error_reporting(0);
require __DIR__ . '/vendor/autoload.php';

$app = new App();
$app->init_mysql("localhost", "nba2019", "password", "nba2019");

if (isset($_GET['type'])) {
    switch ($_GET['type']) {
        case "playerstats":

            if (isset($_GET['team'])) {
                $result = $app->query("SELECT * FROM player_totals
                    INNER JOIN `roster` ON `roster`.`id`=`player_totals`.`player_id`
                    INNER JOIN `team` ON `roster`.`team_code`=`team`.`code`
                    WHERE `team`.`code`='" . $_GET['team'] . "'");
                $app->encoding = "json";
                return $app->encode($result);
            }
            if (isset($_GET['position'])) {
                $result = $app->query("SELECT * FROM player_totals
                    INNER JOIN `roster` ON `roster`.`id`=`player_totals`.`player_id`
                    WHERE `roster`.`pos`='" . $_GET['position'] . "'");
                $app->encoding = "json";
                return $app->encode($result);
            }

            if (isset($_GET['player'])) {
                $result = $app->query("SELECT * FROM player_totals
                    INNER JOIN `roster` ON `roster`.`id`=`player_totals`.`player_id`
                    WHERE `roster`.`name`='" . $_GET['player'] . "'");

                $app->encoding = "json";
                if (isset($_GET['format'])) {
                    if (in_array($_GET['format'], array('json', 'xml'))) {
                        $app->encoding = $_GET['format'];
                    }
                }
                return $app->encode($result);
            }

            break;

        case "players":

            if (isset($_GET['player'])) {
                $result = $app->query("SELECT * FROM `roster`
                    WHERE `roster`.`name`='" . $_GET['player'] . "'");

                $app->encoding = "json";
                if (isset($_GET['format'])) {
                    if (in_array($_GET['format'], array('json', 'xml'))) {
                        $app->encoding = $_GET['format'];
                    }
                }
                return $app->encode($result);
            }

            if (isset($_GET['team'])) {
                $result = $app->query("SELECT * FROM `roster`
                        INNER JOIN `team` ON `team`.`code`=`roster`.`team_code`
                        WHERE `team`.`code`='" . $_GET['team'] . "'");

                $app->encoding = "json";
                if (isset($_GET['format'])) {
                    if (in_array($_GET['format'], array('json', 'xml'))) {
                        $app->encoding = $_GET['format'];
                    }
                }
                return $app->encode($result);
            }
            break;
    }
}

class App
{
    public $mysqli;
    public $encoding;

    public function __construct()
    {
        //
    }

    public function init_mysql($host, $user, $pass, $database)
    {
        $this->mysqli = new mysqli($host, $user, $pass, $database);
        if ($this->mysqli->connect_error) {
            echo "<b>Failed to connect to MySQL: </b>" . $this->mysqli->connect_error;
        }
        // echo "Connected successfully";
    }

    public function query($query)
    {
        if ($result = $this->mysqli->query($query)) {
            /* fetch associative array */
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $this->mysqli->close();
            return $rows;
        }
    }

    public function encode($data)
    {
        switch ($this->encoding) {
            default:
            case "json":
                header('Content-Type: application/json');
                echo json_encode($data, true);
                break;
            case "xml":
                header('Content-type: application/xml');
                $xml = '<?xml version="1.0" encoding="UTF-8"?>';
                $xml .= '<data>';
                $arr = $data;
                foreach ($arr as $student) {
                    $xml .= '<player>';
                    foreach ($student as $tag => $data) {
                        $xml .= '<_' . $tag . '>' . htmlspecialchars($data) . '</_' . $tag . '>';
                    }
                    $xml .= '</player>';
                }
                $xml .= '</data>';
                echo $xml;
                // try {
                //     $array = [
                //         'title' => 'XML Data',
                //         'body' => [
                //             '@xml' => $xml,
                //         ],
                //     ];
                //     $xml = Array2XML::createXML('player', $array);
                //     echo $xml->saveXML();
                // } catch (Exception $e) {
                //     throw new Exception('Could not encode XML: ' . print_r($data));
                // }
                break;
        }
        return $data;
    }
}