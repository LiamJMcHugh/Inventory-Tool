<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<title>Delivery</title>

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
					<label for="Delivery_ID">Delivery ID:</label>
					<input class="form-input" type="text" name="Delivery_ID" id="Delivery_ID" placeholder="" required/>
				</div>
				<div>
					<input class="form-input" type="submit"/>
				</div>
			</form>
		</div>

		<?php
			

			/* Order deletion*/
			$print_query = mysqli_prepare($project_db, <<<END
				SELECT `Order`.*
				FROM `Order`;
			END);
			
			if (isset($_POST["Delivery_ID"]) &&
				$_POST["Delivery_ID"] != ""
            )  {
				
				$str_query = <<<END
					DELETE FROM `Order` WHERE Delivery_ID = (?);
				END;

				$query = mysqli_prepare($project_db, $str_query);
				
				$Delivery_ID = (string)$_POST["Delivery_ID"];

				mysqli_stmt_bind_param(
					$query, "s",
					$Delivery_ID
				);

				try {
					mysqli_stmt_execute($query);
					mysqli_stmt_execute($print_query);
					listTableRowsSecure($print_query);
				} catch (Throwable $e) {
					echo "<p>Order Not Confirmed (" . mysqli_stmt_error($query) . ")</p>";
					exit(500);
				}
			} else {
				mysqli_stmt_execute($print_query);
				listTableRowsSecure($print_query);
			}

			$print_query1 = mysqli_prepare($project_db, <<<END
				SELECT `Delivery`.*
				FROM `Delivery`;
			END);
			
			if (isset($_POST["Delivery_ID"]) &&
				$_POST["Delivery_ID"] != ""
            )  {

				$D_ID = $_POST["Delivery_ID"];
				$str_query1 = <<<END
					DELETE FROM `Delivery` WHERE ID = (?);
					END;
			
				$query1 = mysqli_prepare($project_db, $str_query1);

				$ID = (string)$_POST["Delivery_ID"];

				mysqli_stmt_bind_param(
					$query1, "s",
					$ID
				);

				try {
					mysqli_stmt_execute($query1);
					

					mysqli_stmt_execute($print_query1);
					listTableRowsSecure($print_query1);

					echo "<p>Delivery Confirmed</p>";
				} catch (Throwable $e) {
					echo "<p>Delivery Not Confirmed (" . mysqli_stmt_error($query1) . ")</p>";
					exit(500);
				}
			} else {
				mysqli_stmt_execute($print_query1);
				listTableRowsSecure($print_query1);
			}
			
			exit(200);
		?>
		<a id="back" href="index.php">Back</a>
	</body>
</html>