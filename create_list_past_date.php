<?php
	header("Access-Control-Allow-Origin: http://ecs.fullerton.edu");
	header("Content-Type: application/json; charset=utf-8");

	// Get password (temp maybe)
	$pass_file = fopen("../../pass.txt", "r");
	$pass = fread($pass_file, filesize("../../pass.txt"));
	fclose($pass_file);

	// Connect to db
	// https://www.php.net/manual/en/mysqli.quickstart.dual-interface.php
	$db = mysqli_connect("mariadb", "cs332u32", $pass, "cs332u32");

	if ($_SERVER['REQUEST_METHOD'] == "GET") {
		if (isset($_GET['Name'])) {
			$str_query = <<<END
				SELECT *
				FROM `Expiration`
				WHERE `Expiration`.`Item_UPC` IN (
					SELECT `Item`.`UPC`
					FROM `Item` WHERE
					`Item`.`Department_Name` = ?
				)
			END;

			$query = null;
			
			if (isset($_GET['Date'])) {
				$str_query .= <<<END
					AND (
						TIMESTAMPDIFF(DAY, STR_TO_DATE(?, '%Y-%m-%d'), `Expiration`.`Date`) BETWEEN 0 AND 2
					);
				END;
				$query = mysqli_prepare($db, $str_query);
				mysqli_stmt_bind_param($query, "ss", $_GET['Name'], $_GET['Date']);
			} else {
				$str_query .= ";";
				$query = mysqli_prepare($db, $str_query);
				mysqli_stmt_bind_param($query, "s", $_GET['Name']);
			}
			mysqli_stmt_execute($query);
			$db_result = mysqli_stmt_get_result($query);

			$json = array();
			while ($row = mysqli_fetch_array($db_result)) {
				$json[] = array(
					'UPC' => $row['Item_UPC'],
					'Date' => $row['Date']
				);
			}
			echo json_encode($json);
		} else {
			$str_query = <<<END
				SELECT `Department`.`Name`
				FROM `Department`
				ORDER BY `Department`.`Name`;
			END;

			$db_result = mysqli_query($db, $str_query);

			$result_arr = array();

			while ($row = mysqli_fetch_array($db_result))
				$result_arr[] = $row['Name'];

			echo json_encode($result_arr);

			// echo phpversion(); // 7.4.3
		}
	}

	mysqli_close($db);
	exit(200); // Apparently http_response_code did not work so we use this
	
	// $department = $_GET['Name'];
	// mysqli_stmt_bind_param($query, "s", $department); // Basically pass by reference like in C++
?>