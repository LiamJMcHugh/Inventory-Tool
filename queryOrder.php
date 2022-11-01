<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<title>Query Order 1.0</title>

		<!-- Include index.css -->
		<link rel="stylesheet" type="text/css" href="index.css"/>
	</head>
	<body>
		<?php
			require("misc.php");
		?>

		<div id="form-wrap">
			<form method = "post" action= "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<div>
					<label for="department">Department:</label>
					<select class="form-input" id="department" name="department">
						<?php
							listDepartments($project_db);
						?>
					</select>
				</div>
				<div>
					<input class="form-input" type="submit"/>
				</div>
			</form>
		</div>

		<?php

			/*
				Should take a department number
				Should print a list of items that are associated with that department, that the stock is less than or equal to the restock amount
				Should also include any orders that were placed for each item in returned information
			//*/

			if (isset($_POST["department"]) && !empty($_POST["department"])) {
				$item_str_query = <<<END
					FROM `Item`
					WHERE `Item`.`Department_Name` = ?
					AND `Item`.`Stock_Amount` <= `Item`.`Restock_Amount`
				END;

				$item_query = mysqli_prepare($project_db, <<<END
					SELECT `Item`.`UPC`, `Item`.`Price`, `Item`.`Interim_Price`, `Item`.`Wholesale_Price`, `Item`.`Stock_Amount`, `Item`.`Restock_Amount`
				END . $item_str_query . ";");

				$order_query = mysqli_prepare($project_db, <<<END
					SELECT `Order`.*
					FROM `Order`
					WHERE `Order`.`Item_UPC` IN (SELECT `Item`.`UPC` {$item_str_query});
				END);

				mysqli_stmt_bind_param($item_query, "s", $_POST["department"]);
				mysqli_stmt_bind_param($order_query, "s", $_POST["department"]);

				try {
					mysqli_stmt_execute($item_query);
					listTableRowsSecure($item_query);
					echo "<br/>";
					try {
						mysqli_stmt_execute($order_query);
						listTableRowsSecure($order_query);
					} catch (Throwable $t) {
						echo "<p>Error order_query: " . mysqli_stmt_error($order_query) . "</p>";
					}
				} catch (Throwable $t) {
					echo "<p>Error item_query: " . mysqli_stmt_error($item_query) . "</p>";
				}
			}
		?>
		
		<a id="back" href="index.php">Back</a>
	</body>
</html>

<?php
	exit(200);
?>

<!-- Old: https://pastebin.com/raw/d0mVJMxa -->