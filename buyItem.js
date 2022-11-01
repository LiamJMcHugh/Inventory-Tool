function submitCheck () {
	setCookie(
		'transaction_id',
		document.getElementById('transaction').value === '00000000-0000-0000-0000-000000000000' ?
			window.transaction_id :
			getCookie('transaction_id') !== document.getElementById("transaction").value ?
				document.getElementById("transaction").value :
				getCookie('transaction_id') === '' ?
					window.transaction_id : getCookie('transaction_id')
	);
}

async function queryCoupon (customer, upc) {
	return await (
		await window.fetch(`http://ecs.fullerton.edu/~cs332u14/api/queryCoupon.php?customer=${customer}&upc=${upc}`)
	).json();
}

async function queryCouponRaw (coupon) {
	return await (
		await window.fetch(`http://ecs.fullerton.edu/~cs332u14/api/queryCoupon.php?coupon=${coupon}`)
	).json();
}

function amountCheck (coupon) {
	return parseInt(document.getElementById("amount").value) >= parseInt(coupon[0].Required_Amount);
}

let coupon_flag = false;
async function fillCoupon () {
	let coupon = await queryCoupon(
			document.getElementById("customer").value,
			document.getElementById("upc").value
		),
		coupon_elem = document.getElementById("coupon");

	// [NOTE] Do not disable the coupon field since it is used to send data
	// https://stackoverflow.com/questions/7357256/disabled-form-inputs-do-not-appear-in-the-request
	if (coupon.length > 0 && amountCheck(coupon)) {
		coupon_elem.value = coupon[0].ID;
		coupon_flag = true;
	} else if (
		coupon_elem.value !== "" &&
		(coupon = await queryCouponRaw(coupon_elem.value)).length > 0 &&
		amountCheck(coupon)
	) {
		coupon_flag = true;
	} else {
		// Auto-delete coupon if it not valid
		coupon_elem.value = "";
		coupon_flag = false;
	}

	return coupon;
}

async function totalPrice (coupon) {
	// This should be good enough for now since we don't have time to implementing caching
	const coupon_elem = document.getElementById("coupon");

	if (!coupon || coupon instanceof InputEvent)
		coupon = await queryCouponRaw(coupon_elem.value);

	let amount = parseInt(document.getElementById("amount").value),
		price = await (
			await window.fetch(`http://ecs.fullerton.edu/~cs332u14/api/queryPrice.php?upc=${document.getElementById("upc").value}`)
		).json(),
		total_price = 0;
		
	const total_elem = document.getElementById("total");

	if (price.length > 0) {
		total_price = amount * Math.ceil(Number(price[0].Price) * 100) / 100;

		if (coupon.length > 0 && amount >= parseInt(coupon[0].Required_Amount)) {
			total_price *= coupon[0].Discount;
			coupon_flag = true;
		} else {
			// Same thing here
			coupon_elem.value = "";
			coupon_flag = false;
		}
	}
	
	total_elem.textContent = "Total Price: $" + total_price;
}

async function formUpdate () {
	await totalPrice(await fillCoupon());
}

// Auto-fill saved transaction id
window.addEventListener("DOMContentLoaded", function (event) {
	formUpdate();

	document.getElementById("transaction").value =
		getCookie("transaction_id") === "" ||
		document.querySelector(`#transaction > option[value="${getCookie("transaction_id")}"]`) === null ?
		"00000000-0000-0000-0000-000000000000" : getCookie("transaction_id");

	// Auto-fill coupon code
	document.getElementById("customer").addEventListener("input", debounce(formUpdate, 250));
	document.getElementById("upc").addEventListener("input", debounce(formUpdate, 250));
	document.getElementById("amount").addEventListener("input", debounce(formUpdate, 250));
	document.getElementById("coupon").addEventListener("input", debounce(totalPrice, 250));
});