<!DOCTYPE html>
<html>
	<head>
		<title>Index</title>
	</head>
	<style>
		body {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
		}
		#menu {
			display: flex;
			flex-direction: row;
			max-width: 1250px;
			column-gap: 32px;
			row-gap: 16px;
			flex-wrap: wrap;
			font-size: 
		}
		#menu > a {
			flex-grow: 1;
			flex-shrink: 1;
			flex-basis: 0;
			text-decoration: none;
			color: #000;
			padding: 10px;
			border: 2px solid black;
			border-radius: 8px;
			text-align: center;
		}
		#menu > a:link {
			margin: auto;
		}
		/* @media screen and (max-width: 800px) {
			#menu {
				max-width: 400px;
				h1 {
					font-size: 70px;
				}
				p, #menu > a {
					font-size: 40px;
				}
			}
		} */
	</style>
	<body>
		<h1>Welcome to the Database.</h1>
		<p>Please navigate to one of the following links to modify or access the database.</p>
		<br/>
		<div id="menu">
			<a href='./addItem.php'>Add new items to the database.</a> 
			<a href='./queryDates.php'>Query expiring items based on a given date.</a>
			<a href='./queryOrder.php'>Query an order from the database.</a>
			<a href='./buyItem.php'>Buy items from the database.</a> <!-- Apply coupon -->
			<!-- <a href='./queryCustomers.php'>Look up a customer information.</a> -->
			<a href='./queryDelivery.php'>Input delivery information.</a>
			<a href='./queryTrans.php'>Look up total transactions information.</a>
			<a href='./createOrderPrivate.php'>Create an order from the database.<br/>Only accessible by employee</a>
		</div>
	</body>
</html>