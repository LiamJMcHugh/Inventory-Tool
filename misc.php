<?php
	function listTableRows ($conn, $query, $showing = true) {
		$queryResult = mysqli_query($conn, $query);
		//First, print the names of the columns
		if (!$showing)
			return;
		echo "<table>";
		$result_arr = mysqli_fetch_fields($queryResult);
		echo "<tr>";
		for ($i = 0; $i < count($result_arr); ++ $i) {
				echo "<th>";
				echo $result_arr[$i]->name;
				echo "</th>";
		}
		echo "</tr>";
		while($row = mysqli_fetch_row($queryResult)) {
				echo "<tr>";
				foreach ($row as $rowVal) {
						printf("<td> %s </td>",$rowVal);
				}
				echo "</tr>";
		}
		echo "</table>";
	}

	/*
		$query = mysqli_prepare($db, "SELECT * FROM ?");
		mysqli_stmt_bind_param($query, "s", $_GET['Name']); // Replace "?" with value
		mysqli_stmt_execute($query);
		$db_result = mysqli_stmt_get_result($query);
	//*/
	function listTableRowsSecure ($query, $showing = true) {
		$result = mysqli_stmt_get_result($query);
		
		if (!$showing || mysqli_num_rows($result) == 0)
			return;
		$result_fields = mysqli_fetch_fields($result);
		echo "<table><thead><tr>";
		foreach ($result_fields as $field) {
			echo "<th>" . $field->name . "</th>";
		}
		echo "</tr></thead><tbody>";
		while ($row = mysqli_fetch_row($result)) {
			echo "<tr>";
			foreach ($row as $field) {
				echo "<td>" . $field . "</td>";
			}
			echo "</tr>";
		}
		echo "</tbody></table>";
	}

	function listTableRowsPDOStrRaw ($query, $data, $showing = true) {
		$res = "";

		$res .= "<table><thead><tr>";
		for ($i = 0; $i < $query->columnCount(); ++ $i)
			$res .= "<th>" . $query->getColumnMeta($i)["name"] . "</th>";

		$res .= "</tr></thead><tbody>";

		foreach ($data as $row) {
			$res .= "<tr>";
			foreach ($row as $field) {
				$res .= "<td>" . $field . "</td>";
			}
			$res .= "</tr>";
		}

		$res .= "</tbody></table>";

		return $res;
	}

	function listTableRowsPDOStr ($query, $showing = true) {
		return listTableRowsPDOStrRaw($query, $query->fetchAll(PDO::FETCH_ASSOC), $showing);
	}

	function listTableRowsPDO ($query, $showing = true) {
		echo listTableRowsPDOStr($query, $showing);
	}

	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	$project_db = mysqli_connect("mariadb", "cs332u14", "8mmsPWn4", "cs332u14"); // Legacy connection
	$pdo_db = new PDO("mysql:host=mariadb;dbname=cs332u14", "cs332u14", "8mmsPWn4"); // Newer connection

	function listInputsStr ($project_db, $str_query, $row_name) {
		$res = "";

		$query_result = mysqli_query($project_db, $str_query);

		while ($row = mysqli_fetch_array($query_result))
			$res .= "<option value='" . $row[$row_name] . "'>" . $row[$row_name] . "</option>";

		return $res;
	}

	function listInputs ($project_db, $str_query, $row_name) {
		echo listInputsStr($project_db, $str_query, $row_name);
	}

	function listDepartments ($project_db) {
		listInputs($project_db, <<<END
			SELECT `Department`.`Name`
			FROM `Department`
			ORDER BY `Department`.`Name`;
		END, "Name");
	}

	// https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
	function guidv4($data = null) {
		if ($data === null)
			$data = openssl_random_pseudo_bytes(16);

		assert(strlen($data) == 16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	// if($conn->connect_error) {
	// 	die("Connection failed: " . $conn->connect_error);
	// }
?>