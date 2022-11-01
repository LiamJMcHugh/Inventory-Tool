<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<title>Query Transaction 1.1</title>

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
					<label for="customer">Customer ID:</label>
					<select class="form-input" id="customer" name="customer">
						<?php
							listInputs($project_db, <<<END
								SELECT `Customer`.`ID`
								FROM `Customer`;
							END, "ID");
						?>
					</select>
				</div>
				<div>
					<label for="transaction">Transaction ID:</label>
					<select class="form-input" id="transaction" name="transaction">
						<?php
							listInputs($project_db, <<<END
								SELECT `Transaction`.`ID`
								FROM `Transaction`
								GROUP BY `Transaction`.`ID`;
							END, "ID");
						?>
					</select>
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
					isset($_POST["customer"]) && isset($_POST["transaction"]) &&
					!empty($_POST["customer"]) && !empty($_POST["transaction"])
				) {
					$trans_query = $pdo_db->prepare(<<<END
						SELECT `Purchase`.*
						FROM `Purchase`
						WHERE `Purchase`.`Transaction_ID` = :trans_id AND `Purchase`.`Customer_ID` = :cus_id;
					END);

					$trans_query->bindValue(":cus_id", $_POST["customer"], PDO::PARAM_STR);
					$trans_query->bindValue(":trans_id", $_POST["transaction"], PDO::PARAM_STR);

					$trans_query->execute();

					echo "<p>Total transactions: " . $trans_query->rowCount() . "</p>";

					if ($trans_query->rowCount() > 0) {
						$price = 0;

						$trans_result = $trans_query->fetchAll(PDO::FETCH_ASSOC);

						foreach ($trans_result as $row)
							$price += floatval($row["Price"]) * intval($row["Amount"]);
	
						echo "<p> Total price: " . $price . "</p>";

						echo listTableRowsPDOStrRaw($trans_query, $trans_result);
					}
				}
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