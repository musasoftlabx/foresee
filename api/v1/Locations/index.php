<?php
require '../functions.php';
$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$_JSON = json_decode(file_get_contents('php://input'), true);

$username = TokenDeconstruct($token, 'username');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['products'])) {
        //echo json_encode(ChainPDO("SELECT *, PK AS id, DATE_FORMAT(LastScannedOn, '%D-%m-%y at %r') AS LastScannedOn FROM products WHERE location = ?", [$_GET['location']])->fetchAll());
        echo json_encode(ChainPDO("SELECT SUBSTR(code, 1, 5) AS location, section, SpecialCode, ColorCode, length, color, size, name, scans.barcode, LastScannedBy, DATE_FORMAT(lastScannedOn, '%D-%m-%y at %r') AS LastScannedOn
            FROM scans
            LEFT JOIN products
            ON scans.barcode = products.barcode
            WHERE code = ?", [$_GET['location']])->fetchAll());
    } else {
        $order = '';
        $sort = '';
        $filter = '';
        $limit = '';

        $countQuery = "SELECT COUNT(id) FROM locations";

        if (isset($_GET['order'])) {
            $order = $_GET['order'] == 1 ? ' DESC' : ' ASC';
        }

        if (isset($_GET['sort'])) {
            $sort = 'ORDER BY ' . $_GET['sort'];
        }

        if (isset($_GET['filter'])) {
            $filter = ' WHERE ';
            $_filter = urldecode($_GET['filter']);
            $_filter = json_decode($_filter, true);

            foreach ($_filter as $key => $value) {
                $key++;
                $operator = $value['operator'];
                $property = $value['property'];
                $value    = $value['value'];

                switch ($operator) {
                    case 'lt':
                        return '<';
                        break;
                    case 'gt':
                        return '>';
                        break;
                    case '<=':
                        return 'lteq';
                        break;
                    case '>=':
                        return 'gteq';
                        break;
                    case 'eq':
                    case 'stricteq':
                        $filter .= "`".$property."` = '".$value."'";
                        break;
                    case 'noteq':
                    case 'notstricteq':
                        return '!=';
                        break;
                    case 'like':
                        $filter .= "`".$property."` LIKE '%".$value."%'";
                        break;
                    case 'rx':
                        $filter .= "SUBSTR(code, 1, 5) REGEXP '".$value."'";
                        break;
                    case 'custom':
                        if ($property === 'counted') {
                            $filter .= "ABS(physicalCount - systemCount) = 0 AND isVerified = 1";
                        }

                        if ($property === 'discrepancy') {
                            $filter .= "ABS(physicalCount - systemCount) > 0 AND systemCount > 0";
                        }
                        break;
                }

                if ($key !== count($_filter)) {
                    $filter .= ' AND ';
                }
            }
            
            $count = $countQuery . $filter;

            $filterChunk = explode(' AND ', $filter)[0];
        } else {
            $count = $countQuery;
        }

        if (isset($_GET['limit'])) {
            $page  = isset($_GET['page']) ? $_GET['page'] : 0;
            $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
            $start  = $page * $limit;
            $limit = ' LIMIT ' . $start . ', ' . $limit;
        }
        
        $cumulative = [
            'Total' => ChainPDO("SELECT COUNT(id) FROM locations WHERE storeId = ?", [$_filter[0]['value']])->fetch(PDO::FETCH_COLUMN),
            'Counted' => ChainPDO("SELECT COUNT(id) FROM locations WHERE ABS(physicalCount - systemCount) = 0 AND isVerified = 1 AND storeId = ?", [$_filter[0]['value']])->fetch(PDO::FETCH_COLUMN),
            'Not Counted' => ChainPDO("SELECT COUNT(id) FROM locations WHERE systemCount = 0 AND storeId = ?", [$_filter[0]['value']])->fetch(PDO::FETCH_COLUMN),
            'Discrepancies' => ChainPDO("SELECT COUNT(id) FROM locations WHERE ABS(physicalCount - systemCount) > 0 AND systemCount > 0 AND storeId = ?", [$_filter[0]['value']])->fetch(PDO::FETCH_COLUMN)
        ];

        echo json_encode([
            'data'       => ChainPDO("SELECT
                            *,
                            IF(physicalCount > 0, 1, 0) AS edited, 
                            DATE_FORMAT(modifiedOn, '%D-%b-%Y') AS modifiedOn, 
                            DATE_FORMAT(createdOn, '%D-%b-%Y') AS createdOn, 
                            DATE_FORMAT(lastScannedOn, '%r') AS lastScannedOn, 
                            DATEDIFF(now(), modifiedOn) AS daysElapsed, 
                            ABS(physicalCount - systemCount) AS discrepancy 
                            FROM locations $filter $sort $order $limit")->fetchAll(),
            'totalCount' => ChainPDO($count)->fetch(PDO::FETCH_COLUMN),
            'store' => ChainPDO("SELECT code, name, country, client FROM stores WHERE id = ?", [$_filter[0]['value']])->fetch(),
            /* 'cumulativeCount' => ChainPDO("SELECT 
                CAST(SUM(PhysicalCount) AS `Physical Count` AS UNSIGNED),
                CAST(SUM(SystemCount) AS `System Count` AS UNSIGNED),
                CAST(SUM(ABS(PhysicalCount - SystemCount)) AS Discrepancies AS UNSIGNED)
                FROM locations $filter")->fetch() */
            //'counted' => ChainPDO("SELECT COUNT(id) FROM locations WHERE isVerified = 1")->fetch(PDO::FETCH_COLUMN),
            //'uncounted' => ChainPDO("SELECT COUNT(id) FROM locations WHERE isVerified = 0")->fetch(PDO::FETCH_COLUMN),
            //'discrepancies' => ChainPDO("SELECT COUNT(id) FROM locations WHERE ABS(physicalCount - systemCount) > 0")->fetch(PDO::FETCH_COLUMN),
            /* 'cumulativeCount' => ChainPDO("SELECT 
                IFNULL(SUM(IF(isVerified = 1, 1, 0)), 0) AS `Counted`,
                IFNULL(SUM(IF(isVerified = 0, 1, 0)), 0) AS `Not Counted`,
                IFNULL(SUM(ABS(physicalCount - systemCount)), 0) AS `Discrepancies`
                FROM locations $filterChunk")->fetch() */
            'cumulativeCount' => $cumulative
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['Scan'])) {
        if ($_GET['Scan'] === 'location') {
            $workingStoreDate = ChainPDO("SELECT workingStoreDate FROM defaults WHERE id = 1")->fetch(PDO::FETCH_COLUMN);

            $code = $_JSON['code'] . '-' . $workingStoreDate;
            
            $location = ChainPDO("SELECT id, storeId, code, physicalCount, systemCount, isVerified FROM locations WHERE code = ?", [$code])->fetch();
            
            if ($location && $location['isVerified'] === 0) {
                echo json_encode($location);
            } else if ($location && $location['isVerified'] === 1) {
                http_response_code(302);
                echo json_encode([
                    'error' => 'Already scanned',
                    'message' => 'Location already scanned and verified. System Count: ' . $location['systemCount'] . ' Physical Count: ' . $location['physicalCount']
                ]);
            } else {
                http_response_code(409);
                echo json_encode([
                    'error' => 'Not found',
                    'message' => 'Location not found.'
                ]);
            }
        } else if ($_GET['Scan'] === 'product') {
            $barcode  = $_JSON['barcode'];

            //if (strlen($barcode) === 13) {
            if (strlen($barcode) >= 8 && strlen($barcode) <= 15) {
                $id       = $_JSON['id'];
                $batteryLevel   = $_JSON['batteryLevel'];
                $code     = $_JSON['code'];
                $serialNumber   = $_JSON['serialNumber'];
                $storeId   = $_JSON['storeId'];

                $doesExist = ChainPDO("SELECT COUNT(barcode) FROM products WHERE barcode = ?", [$barcode])->fetchColumn();

                if ($doesExist > 0) {
                    echo json_encode(['success' => 'Product was found and updated']);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'error' => 'Product not found.',
                        'message' => 'Product doesn\'t exist but was added to scans.'
                    ]);

                    ChainPDO("INSERT INTO scans_failed VALUES (NULL, ?, ?, ?, now(), ?, ?, ?)", [
                        $storeId,
                        $code,
                        $barcode,
                        $username,
                        $serialNumber,
                        $batteryLevel,
                    ]);
                }

                ChainPDO("UPDATE products SET location = ?, quantity = quantity + 1, lastScannedBy = ?, lastScannedOn = now() WHERE barcode = ?", [$code, $username, $barcode]);
                ChainPDO("UPDATE locations SET systemCount = systemCount + 1, lastScannedOn = now(), lastScannedBy = ? WHERE code = ?", [$username, $code]);
                ChainPDO("INSERT INTO scans VALUES (NULL, ?, ?, ?, now(), ?, ?, ?)", [
                    $storeId,
                    $code,
                    $barcode,
                    $username,
                    $serialNumber,
                    $batteryLevel,
                ]);
            } else {
                http_response_code(403);
                echo json_encode([
                    'error' => 'Invalid barcode!',
                    'message' => 'The barcode you scanned is invalid. Please try again.'
                ]);
            }
        }
        die();
    }

    if (isset($_GET['ScanProduct'])) {
        $code = $_JSON['code'];

        $location = ChainPDO("SELECT 
            *,
            id,
            IF(physicalCount > 0, 1, 0) AS edited,
            DATE_FORMAT(modifiedOn, '%D-%b-%Y') AS modifiedOn,
            DATEDIFF(now(), modifiedOn) AS daysElapsed,
            ABS(physicalCount - systemCount) AS discrepancy
            FROM locations
            WHERE code = ?
            ORDER BY id DESC",
            [$code])->fetch();
            
            if ($location) {
                echo json_encode($location);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Location not found']);
            }
        die();
    }

    if (isset($_GET['Create'])) {
        $storeId   = $_JSON['storeId'];
        $storeCode   = $_JSON['storeCode'];
        $quantity    = $_JSON['quantity'];
        $scanDate    = $_JSON['scanDate']['formatted'];

        if ($quantity < 9999) {
            $inserts = [];
            $errors = [];

            for ($i = 0; $i < $quantity; $i++) {
                $maxCode = ChainPDO("SELECT MAX(code) FROM locations WHERE storeId = ?", [$storeId])->fetchColumn();

                if ($maxCode) {
                    $count = preg_replace('/[^0-9]/', '', explode('-', $maxCode)[0]);
                    $count++;
                } else {
                    $count = 1;
                }

                $location = 'L' . str_pad($count, 4, '0', STR_PAD_LEFT);

                $code  = $location . '-' . $storeCode . '-' . $scanDate;

                try {
                    $DB->beginTransaction();

                    ChainPDO("INSERT INTO locations VALUES (NULL, ?, ?, 0, 0, 1, 0, now(), ?, NULL, NULL, NULL, NULL)", [
                        $storeId,
                        $code,
                        $username,
                    ]);

                    $LastID = $DB->lastInsertId();

                    $DB->commit();

                    array_push($inserts, 
                        ChainPDO("SELECT *,
                            IF(physicalCount > 0, 1, 0) AS edited,
                            DATE_FORMAT(modifiedOn, '%D-%b-%Y') AS modifiedOn,
                            DATEDIFF(now(), modifiedOn) AS daysElapsed,
                            ABS(physicalCount - systemCount) AS discrepancy
                            FROM locations
                            WHERE id = ?", [$LastID])->fetch());
                } catch (PDOException $e) {
                    $DB->rollBack();
                    array_push($errors, $e->getMessage());
                }
            }

            if (empty($errors)) {
                ChainPDO("INSERT INTO scan_dates VALUES (NULL, ?, now(), ?)", [$_JSON['scanDate']['date'], $username]);

                http_response_code(201);
                echo json_encode($inserts);
            } else {
                http_response_code(500);
                echo json_encode([
                    'title' => 'Server Error',
                    'content' => 'There was an error creating the locations. Please try again.'
                ]);

                LogToDB(json_encode(
                    [
                        'logtype'   => 'ERROR',
                        'operation' => 'DATABASE',
                        'service'   => basename(dirname(__FILE__)),
                        'request'   => file_get_contents('php://input'),
                        'response'  => json_encode($errors),
                    ]
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode([
                'title' => 'Exceeded Limit',
                'content' => 'Quantity must be less than 100.'
            ]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    if (isset($_GET['SubmitScan'])) {
        ChainPDO("UPDATE locations SET isVerified = 1 WHERE id = ?", [$_JSON['id']]);

        // Get discrepancies
        $location = ChainPDO("SELECT storeId, code, physicalCount, systemCount FROM locations WHERE id = ?", [$_JSON['id']])->fetch();
        ChainPDO("INSERT INTO discrepancies VALUES (NULL, ?, ?, ?, ?)", [
            $location['storeId'],
            $location['code'],
            $location['physicalCount'],
            $location['systemCount'],
        ]);

        echo json_encode(['success' => 'Updated']);
        die();
    }

    if (isset($_GET['ResetPhysicalCount'])) {
        ChainPDO("UPDATE locations
            SET physicalCount = ?, modifiedBy = ?, modifiedOn = now()
            WHERE id = ?",
            [$_JSON['physicalCount'], $username, $_JSON['id']]);
        echo json_encode(['success' => 'Updated']);

        LogToDB(json_encode(
            [
                'logtype'   => 'INFO',
                'operation' => 'UPDATE',
                'service'   => basename(dirname(__FILE__)),
                'request'   => file_get_contents('php://input'),
                'response'  => 'Physical count updated to '. $_JSON['physicalCount'] .' by ' . $username,
            ]
        ));

        die();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $code = $_JSON['code'];

    ChainPDO("UPDATE locations SET systemCount = 0, isVerified = 0, lastScannedBy = NULL, lastScannedOn = NULL WHERE id = ?", [$_JSON['id']]);
    ChainPDO("DELETE FROM scans WHERE code = ?", [$code]);
    ChainPDO("DELETE FROM scans_failed WHERE code = ?", [$code]);

    LogToDB(json_encode(
        [
            'logtype'   => 'INFO',
            'operation' => 'DELETE',
            'service'   => basename(dirname(__FILE__)),
            'request'   => file_get_contents('php://input'),
            'response'  => $code.' scans deleted by ' . $username,
        ]
    ));
}
