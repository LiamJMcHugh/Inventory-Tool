<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<title>Query Dates 1.5</title>

		<!-- Include index.css -->
		<link rel="stylesheet" type="text/css" href="index.css"/>
	</head>
	<body>
		<?php
			require("misc.php");
		?>

		<div id="form-wrap">
			<form method = "post" action= "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<!-- Dropdown list of available Department -->
				<div>
					<label for="department">Department:</label>
					<select class="form-input" id="department" name="department">
						<?php
							listDepartments($project_db);
						?>
					</select>
				</div>
				<div>
					<label for="datepicker">Date:</label>
					<!-- <input class="form-input" id="datepicker" type="text" name="date" placeholder="Date"/> -->
					<input class="form-input" id="datepicker" type="date" name="date" min="1970-01-01" placeholder="1970-01-01"/>
				</div>
				<div>
					<input class="form-input" type="submit"/>
				</div>
			</form>
		</div>

		<?php
			$str_query = <<<END
				SELECT `Item`.`UPC`, `Expiration`.`Date`
				FROM `Item`, `Expiration`
				WHERE
					`Item`.`UPC` = `Expiration`.`Item_UPC`
					AND `Expiration`.`Item_UPC` IN (
						SELECT `Item`.`UPC`
						FROM `Item` WHERE
						`Item`.`Department_Name` = ?
					)
			END;

			if(isset($_POST["department"])) {
				if (isset($_POST["date"]) && $_POST["date"] != "") {
					$str_query .= <<<END
						AND (
							TIMESTAMPDIFF(DAY, STR_TO_DATE(?, '%Y-%m-%d'), `Expiration`.`Date`) BETWEEN 0 AND 2
						)
					END;
				}
				$str_query .= " GROUP BY `Expiration`.`Date`;";

				$query = mysqli_prepare($project_db, $str_query);

				if (isset($_POST["date"]) && $_POST["date"] != "")
					mysqli_stmt_bind_param($query, "ss", $_POST['department'], $_POST['date']);
				else
					mysqli_stmt_bind_param($query, "s", $_POST['department']);
				
				try {
					mysqli_stmt_execute($query);
					listTableRowsSecure($query);
				} catch (Throwable $e) {
					echo "<p>Error (" . mysqli_stmt_error($query) . ")</p>";
				}
			}
		?>
		
		<a id="back" href="index.php">Back</a>
	</body>
</html>

<?php
	exit(200);
?>

<!-- Old stuff: https://pastebin.com/raw/BVm6T6bt -->