<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<title>Add Item 1.1</title>

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
					<label for="upc">Coupon ID:</label>
					<input class="form-input" type="number" name="CouponID" id="upc" min="100000000000" max="999999999999" step="1" placeholder="000000000000" required/>
				</div>
				<div>
					<label for="price">Transaction ID:</label>
					<input class="form-input" type="number" name="TransactionID" id="price" min="0" step="0.01" required/>
				</div>
				<div>
					<input class="form-input" type="submit"/>
				</div>
			</form>
		</div>

		<?php
			$print_query = mysqli_prepare($project_db, <<<END
				SELECT `ID`, 'ID'.*
				FROM `Coupon`, 'Transaction';
			END);

			if (
				isset($_POST["Coupon.ID"]) &&
				isset($_POST["Purchase.Transaction_ID"]) &&
				/*isset($_POST["interim_price"]) &&
				isset($_POST["wholesale_price"]) &&
				isset($_POST["restock_amount"]) &&
				isset($_POST["stock_amount"]) &&
				isset($_POST["department"]) && */
				$_POST["Coupon.ID"] != "" &&
				$_POST["Purchase.Transaction_ID"] != ""
				/*$_POST["interim_price"] != "" &&
				$_POST["wholesale_price"] != "" &&
				$_POST["restock_amount"] != "" &&
				$_POST["stock_amount"] != "" &&
				$_POST["department"] != "" */
			) {
				$str_query = <<<END
					UPDATE Purchase SET Price *= Discount WHERE Amount >= (Select Amount.Coupon FROM Coupon WHERE Coupon.UPC = (?)) HAVING Purchase.Transaction_ID = (?);
				END;

				$query = mysqli_prepare($project_db, $str_query);
				
				$Coupon_ID = (string)$_POST["Coupon.ID"];
				$Purchase_Transaction_ID = $_POST["Purchase.Transaction_ID"];
				/*$interim_price = $_POST["interim_price"];
				$wholesale_price = $_POST["wholesale_price"];
				$stock_amount = $_POST["stock_amount"];
				$restock_amount = $_POST["restock_amount"];
				$department = $_POST["department"];
                */
				mysqli_stmt_bind_param(
					$query, "sdddiis",
					$Coupon_ID,
					$Purchase_Transaction_ID
				);

				try {
					mysqli_stmt_execute($query);
					echo "<p>Price changed successfully</p>";

					mysqli_stmt_execute($print_query);
					listTableRowsSecure($print_query);
				} catch (Throwable $e) {
					echo "<p>Price not changed successfully (" . mysqli_stmt_error($query) . ")</p>";
					exit(500);
				}
			} else {
				mysqli_stmt_execute($print_query);
				listTableRowsSecure($print_query);
			}
			exit(200);
		?>
	</body>
</html>