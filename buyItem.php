<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<title>Buy Item 1.6</title>

		<!-- Include index.css -->
		<link rel="stylesheet" type="text/css" href="index.css"/>

		<!-- Include misc.js, buyItem.js -->
		<script src="misc.js"></script>
		<script src="buyItem.js"></script>
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
						<option value="00000000-0000-0000-0000-000000000000" selected>New Transaction</option>
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
					<label for="coupon">Coupon:</label>
					<input class="form-input" type="text" name="coupon" id="coupon" placeholder="00000000-0000-0000-0000-000000000000"/>
				</div>
				<div>
					<input class="form-input" type="submit" onclick="submitCheck()"/>
				</div>
			</form>
		</div>

		<p id="total"></p>

		<?php
			/*
				Should take an item id, customer id, and transaction id and add the item to the transaction.
				If no transaction id is given then a new transaction is started for the customer. 
				After a transaction is started the transaction id should be printed out so that the transaction can be continued
			//*/

			// AND `Item`.`Stock_Amount` > 0;

			// Use the query from queryOrder.php
			$item_query = $pdo_db->prepare(<<<END
				SELECT `Item`.`UPC`, `Item`.`Price`, `Item`.`Interim_Price`, `Item`.`Wholesale_Price`, `Item`.`Stock_Amount`, `Item`.`Restock_Amount`
				FROM `Item`
				WHERE `Item`.`Stock_Amount` <= `Item`.`Restock_Amount`;
			END);

			// For each item buy, check if the item is in stock, if it is then add it to the transaction,
			// and decrease the stock amount by one. [TODO]

			// Transaction and Order is separate, as Order is for internal use to resolve Delivery
			// and Transaction is for external use, exposed to the customer.
			// => So when a customer purchases an item, a transaction is created and an order is created. [IGNORE]

			// If Purchase is exist, then increase the amount of the item in the purchase. [IGNORE]

			// Also add Bought for each item bought by the customer [IGNORE]

			// Same UPC can have multiple Delivery_ID
			// And a Delivery_ID can have multiple customer_id

			// Do not modify Order as it associate with Purchase such that an Order can extract an amount of Purchase.Amount
			// and create an order from there with the UPC

			// As we can also split a purchase to multiple orders (or multiple purchase to multiple orders)
			// => Use Bought to associate an order with a customer

			// We split buy behavior track (Transaction, Purchase) and furfillment track (Order, Delivery)
			// and use Transaction.UPC and Bought to bridge between those twos

			// => Overall we don't have to touch (Bought, Order, Delivery) yet
				// => Do these in createOrderPrivate.php instead

			$trans_id = guidv4();

			// Set cookies client side using js
			echo "<script>";
			echo "window.transaction_id = '" . $trans_id . "';";
			echo "</script>";

			try {
				if (
					isset($_COOKIE["transaction_id"]) && !empty($_COOKIE["transaction_id"]) &&
					isset($_POST["customer"]) && isset($_POST["transaction"]) && isset($_POST["upc"]) && isset($_POST["amount"]) &&
					!empty($_POST["customer"]) && !empty($_POST["transaction"]) && !empty($_POST["upc"]) && !empty($_POST["amount"])
				) {
					if ($_POST["transaction"] == "00000000-0000-0000-0000-000000000000") {
						$trans_id = $_COOKIE["transaction_id"];
					} else
						$trans_id = $_POST["transaction"];

					$post_amount = intval($_POST["amount"]);

					// Make transaction
					$make_trans_query = $pdo_db->prepare(<<<END
						INSERT INTO `Transaction`
							(`ID`, `Customer_ID`, `Date`, `Time`)
						VALUES
							(:trans_id, :cus_id, :date, :time)
						ON DUPLICATE KEY
							UPDATE `Date` = STR_TO_DATE(:date, '%Y-%m-%d'), `Time` = :time;
					END);

					$make_trans_query->bindParam(":trans_id", $trans_id, PDO::PARAM_STR);
					$make_trans_query->bindParam(":cus_id", $_POST["customer"], PDO::PARAM_STR);
					
					$curr_date = date("Y-m-d");
					$curr_time = date("H:i:s");

					$make_trans_query->bindParam(":date", $curr_date, PDO::PARAM_STR);
					$make_trans_query->bindParam(":time", $curr_time, PDO::PARAM_STR);

					// Get price and coupon to apply for the thing
					$price_query = $pdo_db->prepare(<<<END
						SELECT `Item`.`Price`
						FROM `Item`
						WHERE `Item`.`UPC` = :upc;
					END);

					$price_query->bindParam(":upc", $_POST["upc"], PDO::PARAM_STR);
					
					$price_query->execute();
					
					// Set price to float type
					$actual_price = floatval($price_query->fetch(PDO::FETCH_ASSOC)["Price"]);
					
					// Get coupon and apply discount
					if (isset($_POST["coupon"]) && !empty($_POST["coupon"])) {
						$coupon_query = $pdo_db->prepare(<<<END
							SELECT `Coupon`.`Discount`, `Coupon`.`Required_Amount`
							FROM `Coupon`
							WHERE `Coupon`.`ID` = :cou_id AND `Coupon`.`Item_UPC` = :upc;
						END);

						$coupon_query->bindParam(":cou_id", $_POST["coupon"], PDO::PARAM_STR);
						$coupon_query->bindParam(":upc", $_POST["upc"], PDO::PARAM_STR);

						$coupon_query->execute();

						if ($coupon_query->rowCount() == 1) {
							$coupon_result = $coupon_query->fetch(PDO::FETCH_ASSOC);

							if (intval($coupon_result["Required_Amount"]) <= $post_amount)
								$actual_price = $actual_price * (1 - floatval($coupon_result["Discount"]));
							else {
								echo "<p>Coupon not applicable (required amount is " . $coupon_result["Required_Amount"] . ", got " . $post_amount . ")</p>";
								goto item_exit;
							}
						} else {
							echo "<p>Coupon not applicable (invalid coupon " . $_POST["coupon"] . ")</p>";
							goto item_exit;
						}
					}

					// https://stackoverflow.com/questions/6876666/convert-float-to-string-in-php
					$actual_price_str = number_format($actual_price, 2, ".", "");
					
					// Do the purchase
					$make_purchase_query = $pdo_db->prepare(<<<END
						INSERT INTO `Purchase`
							(`Customer_ID`, `Transaction_ID`, `Item_UPC`, `Amount`, `Price`)
						VALUES
							(:cus_id, :trans_id, :upc, :amount, CAST(:price AS FLOAT));
					END);

					$make_purchase_query->bindParam(":cus_id", $_POST["customer"], PDO::PARAM_STR);
					$make_purchase_query->bindParam(":trans_id", $trans_id, PDO::PARAM_STR);

					$make_purchase_query->bindParam(":upc", $_POST["upc"], PDO::PARAM_STR);
					$make_purchase_query->bindParam(":amount", $post_amount, PDO::PARAM_INT);
					// https://stackoverflow.com/questions/2718628/pdoparam-for-type-decimal
					$make_purchase_query->bindParam(":price", $actual_price_str, PDO::PARAM_STR);

					// Get Stock_Amount and decrease it, and prevent to less than 0

					$stock_query = $pdo_db->prepare(<<<END_STOCK
						SELECT `Item`.`Stock_Amount`
						FROM `Item`
						WHERE `Item`.`UPC` = :upc;
					END_STOCK);

					$stock_query->bindParam(":upc", $_POST["upc"], PDO::PARAM_STR);

					$stock_query->execute();

					$stock_amount = intval($stock_query->fetch(PDO::FETCH_ASSOC)["Stock_Amount"]);
					if ($stock_amount < $post_amount) {
						echo "<p>Not enough stock (got " . $post_amount . ")</p>";
						goto item_exit;
					}

					$update_stock_query = $pdo_db->prepare(<<<END_STOCK
						UPDATE `Item`
						SET `Stock_Amount` = CASE
							WHEN `Stock_Amount` >= 0
							THEN `Stock_Amount` - CAST(:amount AS UNSIGNED)
							ELSE 0
						END
						WHERE `Item`.`UPC` = :upc;
					END_STOCK);

					$update_stock_query->bindParam(":upc", $_POST["upc"], PDO::PARAM_STR);
					$update_stock_query->bindParam(":amount", $post_amount, PDO::PARAM_INT);
					
					// Execute queries
					$make_trans_query->execute();
					$make_purchase_query->execute();
					$update_stock_query->execute();
						
					// [TODO] Fetch purchase by current trans_id and customer
					$session_query = $pdo_db->prepare(<<<END
						SELECT `Purchase`.`Item_UPC`, `Purchase`.`Amount`, `Purchase`.`Price`
						FROM `Purchase`
						WHERE `Purchase`.`Transaction_ID` = :trans_id AND `Purchase`.`Customer_ID`  = :cus_id;
					END);

					$session_query->bindValue(":cus_id", $_POST["customer"], PDO::PARAM_STR);
					$session_query->bindValue(":trans_id", $trans_id, PDO::PARAM_STR);
				
					// Put this before session_query for consistency
					$item_query->execute();
					listTableRowsPDO($item_query);

					echo "<br/>";

					$session_query->execute();
					listTableRowsPDO($session_query);
				} else {
					item_exit:
					$item_query->execute();
					listTableRowsPDO($item_query);
				}

				echo "<script>";

				// Inject the transaction id into the form
				echo "document.getElementById('transaction').innerHTML += \"" . listInputsStr($project_db, <<<END
						SELECT `Transaction`.`ID`
						FROM `Transaction`
						GROUP BY `Transaction`.`ID`;
					END, "ID") . "\";";

				echo "</script>";
			} catch (Throwable $e) {
				// echo "<p>" . $item_query->errorInfo()[2] . "</p>";
				echo "<p>" . $e->getMessage() . "</p>";
			}

		?>

		<a id="back" href="index.php">Back</a>
	</body>
</html>

<?php
	exit(200);
?>

<!-- Old: https://pastebin.com/raw/69NAY91Q -->