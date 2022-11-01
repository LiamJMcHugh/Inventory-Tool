<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>Create Order</title>
	</head>
	<body>
		<form method = "post" action= "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
			<span>UPC: </span><input type="number" name="upc" placeholder="UPC" required/>
			<br/>
			<span>Amount: </span><input type="number" name="amount" placeholder="Amount" required/>
			<br/>
			<input type="submit"/>
		</form>
		<?php
			require("misc.php");

			if (isset($_POST["upc"]) && !empty($_POST["upc"]) && isset($_POST["amount"]) && !empty($_POST["amount"])) {
				$upc = $_POST["upc"];
				$amount = $_POST["amount"];

				$query = mysqli_prepare($conn, <<<END
					INSERT INTO DOrder VALUES (?, ?, ?, 0, NULL);
				END);

				// Get today date
				$today = date("Y-m-d");

				// Generate random string of 25 numbers (since DeliveryID type is varchar(25))
				// $deliveryId = "910823"; //(string)rand(1000000000000000000000000, 9999999999999999999999999);

				// https://www.php.net/manual/en/mysqli-stmt.bind-param.php
				mysqli_stmt_bind_param($query, "ssi", $upc, $today, $amount); // Replace "?" with value

				if (mysqli_stmt_execute($query)) {
					echo "Successfully added " . $amount . " of UPC " . $upc . " to the order.";
				} else {
					echo "Failed to add " . $amount . " of UPC " . $upc . " to the order.";
				}
			}

			exit(200);
		?>
	</body>
</html>