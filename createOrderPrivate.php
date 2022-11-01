<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<title>Create Order Private 1.0</title>

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
					<label for="employee">Employee ID:</label>
					<select class="form-input" id="employee" name="employee" placeholder="00000000-0000-0000-0000-000000000000">
						<?php
							listInputs($project_db, <<<END
								SELECT `Employee`.`ID`
								FROM `Employee`;
							END, "ID");
						?>
					</select>
				</div>
				<div>
					<label for="upc">Item UPC:</label>
					<!-- <input class="form-input" type="number" name="upc" id="upc" min="100000000000" max="999999999999" step="1" placeholder="000000000000" required/> -->
					<select class="form-input" id="upc" name="upc" placeholder="000000000000">
						<?php
							listInputs($project_db, <<<END
								SELECT `Item`.`UPC`
								FROM `Item`
								ORDER BY `Item`.`UPC` ASC;
							END, "UPC");
						?>
					</select>
				</div>
				<div>
					<label for="amount">Amount:</label>
					<input class="form-input" type="number" name="amount" id="amount" min="1" step="1" placeholder="1" value="1" required/>
				</div>
				<div>
					<input class="form-input" type="submit"/>
				</div>
			</form>
		</div>

		<?php
			try {
				if (
					$_SERVER["REQUEST_METHOD"] == "POST" &&
					isset($_POST["employee"]) && isset($_POST["upc"]) && isset($_POST["amount"]) &&
					!empty($_POST["employee"]) && !empty($_POST["upc"]) && !empty($_POST["amount"])
				) {
					$employee_query = $pdo_db->prepare(<<<END
						SELECT `Employee`.`Permission`
						FROM `Employee`
						WHERE `Employee`.`ID` = :employee;
					END);
	
					$employee_query->bindValue(":employee", $_POST["employee"], PDO::PARAM_STR);

					$employee_query->execute();

					if ($employee_query->fetch(PDO::FETCH_ASSOC)["Permission"] == "0") {
						echo "<p>Not have permission.</p>";
						goto order_exit;
					}

					$make_order_query = $pdo_db->prepare(<<<END
						INSERT INTO `Order` (`Date`, `Amount`, `Item_UPC`, `Delivery_ID`)
						VALUES (STR_TO_DATE(:date, '%Y-%m-%d'), :amount, :upc, null);
					END);

					$curr_date = date("Y-m-d");
					$make_order_query->bindValue(":date", $curr_date, PDO::PARAM_STR);
					$make_order_query->bindValue(":amount", $_POST["amount"], PDO::PARAM_INT);
					$make_order_query->bindValue(":upc", $_POST["upc"], PDO::PARAM_STR);

					$make_order_query->execute();
				}

				order_exit:
				
				$order_query = $pdo_db->prepare(<<<END
					SELECT `Order`.*
					FROM `Order`;
				END);

				$order_query->execute();

				listTableRowsPDO($order_query);

			} catch (PDOException $e) {
				echo "<p>Error: " . $e->getMessage() . "</p>";
			}
		?>
		
		<a id="back" href="index.php">Back</a>
	</body>
</html>

<?php
	exit(200);
?>