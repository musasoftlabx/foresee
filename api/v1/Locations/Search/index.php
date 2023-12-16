<?php
require '../../functions.php';
//RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$code = 'L0001';//$_JSON['code'];

$location = ChainPDO("SELECT 
	#*,
	id,
	IF(physicalCount > 0, 1, 0) AS edited,
	DATE_FORMAT(modifiedOn, '%D-%b-%Y') AS modifiedOn,
	DATEDIFF(now(), modifiedOn) AS daysElapsed,
	ABS(physicalCount - systemCount) AS discrepancy
	FROM locations
	WHERE code = ?
	ORDER BY id DESC",
	[$code])->fetch();

//echo json_encode($location);
print_r($location);

/* if () {
	
} else {

} */

