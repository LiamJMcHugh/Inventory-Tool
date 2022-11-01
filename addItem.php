<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<title>Add Item 1.2</title>

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
					<label for="upc">UPC:</label>
					<input class="form-input" type="number" name="upc" id="upc" min="100000000000" max="999999999999" step="1" placeholder="000000000000" required/>
				</div>
				<div>
					<label for="price">Price:</label>
					<input class="form-input" type="number" name="price" id="price" min="0" step="0.01" required/>
				</div>
				<div>
					<label for="interim_price">Interim Price:</label>
					<input class="form-input" type="number" name="interim_price" id="interim_price" min="0" step="0.01" required/>
				</div>
				<div>
					<label for="wholesale_price">Wholesale Price:</label>
					<input class="form-input" type="number" name="wholesale_price" id="wholesale_price" min="0" step="0.01" required/>
				</div>
				<div>
					<label for="restock_amount">Restock Amount:</label>
					<input class="form-input" type="number" name="restock_amount" id="restock_amount" min="0" step="1" required/>
				</div>
				<div>
					<label for="stock_amount">Stock Amount:</label>
					<input class="form-input" type="number" name="stock_amount" id="stock_amount" min="0" step="1" required/>
				</div>
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
			$print_query = mysqli_prepare($project_db, <<<END
				SELECT `Item`.*
				FROM `Item`;
			END);

			if (
				isset($_POST["upc"]) &&
				isset($_POST["price"]) &&
				isset($_POST["interim_price"]) &&
				isset($_POST["wholesale_price"]) &&
				isset($_POST["restock_amount"]) &&
				isset($_POST["stock_amount"]) &&
				isset($_POST["department"]) &&
				$_POST["upc"] != "" &&
				$_POST["price"] != "" &&
				$_POST["interim_price"] != "" &&
				$_POST["wholesale_price"] != "" &&
				$_POST["restock_amount"] != "" &&
				$_POST["stock_amount"] != "" &&
				$_POST["department"] != ""
			) {
				$query = $pdo_db->prepare(<<<END
					INSERT INTO `Item`
						(`UPC`, `Price`, `Interim_Price`, `Wholesale_Price`, `Stock_Amount`, `Restock_Amount`, `Department_Name`)
					VALUES
						(:upc, CAST(:price AS FLOAT), CAST(:i_price AS FLOAT), CAST(:w_price AS FLOAT), :stock, :r_stock, :dept)
					ON DUPLICATE KEY
						UPDATE
							`Price` = CAST(:price AS FLOAT),
							`Interim_Price` = CAST(:i_price AS FLOAT),
							`Wholesale_Price` = CAST(:w_price AS FLOAT),
							`Stock_Amount` = :stock,
							`Restock_Amount` = :r_stock,
							`Department_Name` = :dept;
				END);

				$query->bindValue(":upc", $_POST["upc"], PDO::PARAM_STR);
				$query->bindValue(":price", $_POST["price"], PDO::PARAM_STR);
				$query->bindValue(":i_price", $_POST["interim_price"], PDO::PARAM_STR);
				$query->bindValue(":w_price", $_POST["wholesale_price"], PDO::PARAM_STR);
				$query->bindValue(":stock", $_POST["stock_amount"], PDO::PARAM_INT);
				$query->bindValue(":r_stock", $_POST["restock_amount"], PDO::PARAM_INT);
				$query->bindValue(":dept", $_POST["department"], PDO::PARAM_STR);

				try {
					$query->execute();
					echo "<p>Item added successfully</p>";
				} catch (PDOException $e) {
					echo "<p>Item not added successfully (" . $e->getMessage() . ")</p>";
					goto item_exit;
				}
			}
			item_exit:
			mysqli_stmt_execute($print_query);
			listTableRowsSecure($print_query);
		?>
		
		<a id="back" href="index.php">Back</a>
	</body>
</html>

<?php
	exit(200);
?>