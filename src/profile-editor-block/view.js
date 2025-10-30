/**
 * Frontend JavaScript for Profile Editor Block
 */

// Import Vietnam address data from the generated file
// This file contains complete 63 provinces/cities with all districts and wards
// Auto-generated from official Vietnam administrative division data
import vietnamAddressData from './vietnam-address-data.js';

document.addEventListener('DOMContentLoaded', function () {
	// Auto-hide success message after 8 seconds
	const successMessage = document.getElementById('nali-success-message');
	if (successMessage) {
		setTimeout(function () {
			successMessage.remove();
		}, 8000); // 8 seconds - animation handles fade out at 7.5s
	}

	// Initialize address dropdowns for both billing and shipping
	initializeAddressDropdowns('billing');
	initializeAddressDropdowns('shipping');

	const checkbox = document.getElementById('same_as_billing');
	const shippingFields = document.querySelector('.nali-shipping-fields');

	if (checkbox && shippingFields) {
		// Toggle shipping fields visibility - REVERSED LOGIC
		// If checked (same as billing) -> HIDE shipping fields
		// If unchecked (different) -> SHOW shipping fields
		checkbox.addEventListener('change', function () {
			if (this.checked) {
				// Checked = Same as billing -> Hide fields and copy
				shippingFields.classList.add('hidden');
				copyBillingToShipping();
			} else {
				// Unchecked = Different address -> Show fields
				shippingFields.classList.remove('hidden');
			}
		});

		// Check if shipping address is different from billing
		// Compare ALL fields to determine if addresses are different
		const compareFields = [
			// Address fields
			{ billing: 'billing_state', shipping: 'shipping_state' },
			{ billing: 'billing_city', shipping: 'shipping_city' },
			{ billing: 'billing_district', shipping: 'shipping_district' },
			// Name fields
			{ billing: 'billing_first_name', shipping: 'shipping_first_name' },
			{ billing: 'billing_last_name', shipping: 'shipping_last_name' },
			// Contact fields
			{ billing: 'billing_phone', shipping: 'shipping_phone' },
			{ billing: 'billing_company', shipping: 'shipping_company' },
			// Address detail
			{ billing: 'billing_address_1', shipping: 'shipping_address_1' }
		];

		let isDifferentAddress = false;
		let hasAnyShippingData = false;

		// Check each field pair
		for (const fieldPair of compareFields) {
			const billingEl = document.getElementById(fieldPair.billing);
			const shippingEl = document.getElementById(fieldPair.shipping);
			
			if (!billingEl || !shippingEl) continue;

			const billingValue = billingEl.tagName === 'SELECT' 
				? billingEl.getAttribute('data-selected') || billingEl.value 
				: billingEl.value;
			const shippingValue = shippingEl.tagName === 'SELECT' 
				? shippingEl.getAttribute('data-selected') || shippingEl.value 
				: shippingEl.value;

			// Check if shipping has any data
			if (shippingValue && shippingValue !== '') {
				hasAnyShippingData = true;
			}

			// Check if values are different
			if (shippingValue !== billingValue) {
				isDifferentAddress = true;
			}
		}

		// REVERSED LOGIC: 
		// If different address -> UNCHECK (show fields)
		// If same address -> CHECK (hide fields)
		if (hasAnyShippingData && isDifferentAddress) {
			// Different address -> Uncheck "same as billing"
			checkbox.checked = false;
			shippingFields.classList.remove('hidden');
		} else {
			// Same address or no data -> Check "same as billing"
			checkbox.checked = true;
			shippingFields.classList.add('hidden');
		}
	}

	/**
	 * Initialize address dropdowns for a given prefix (billing or shipping)
	 */
	function initializeAddressDropdowns(prefix) {
		const stateSelect = document.getElementById(prefix + '_state');
		const citySelect = document.getElementById(prefix + '_city');
		const districtSelect = document.getElementById(prefix + '_district');
		const postcodeInput = document.getElementById(prefix + '_postcode');

		if (!stateSelect) return;

		// Get saved values from data attributes
		const savedState = stateSelect.getAttribute('data-selected');
		const savedCity = citySelect.getAttribute('data-selected');
		const savedDistrict = districtSelect.getAttribute('data-selected');

		// Populate provinces
		Object.keys(vietnamAddressData).forEach(function (key) {
			const option = document.createElement('option');
			option.value = key;
			option.textContent = vietnamAddressData[key].name;
			stateSelect.appendChild(option);
		});

		// Restore saved province value
		if (savedState) {
			stateSelect.value = savedState;
			// Trigger change to populate districts
			populateDistricts(prefix, savedState, savedCity, savedDistrict);
		}

		// Handle province change
		stateSelect.addEventListener('change', function () {
			const selectedProvince = this.value;
			citySelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
			districtSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
			citySelect.disabled = true;
			districtSelect.disabled = true;
			postcodeInput.value = '';

			if (selectedProvince && vietnamAddressData[selectedProvince]) {
				const districts = vietnamAddressData[selectedProvince].districts;
				Object.keys(districts).forEach(function (key) {
					const option = document.createElement('option');
					option.value = key;
					option.textContent = districts[key].name;
					citySelect.appendChild(option);
				});
				citySelect.disabled = false;
			}
		});

		// Handle district change
		citySelect.addEventListener('change', function () {
			const selectedProvince = stateSelect.value;
			const selectedDistrict = this.value;
			districtSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
			districtSelect.disabled = true;
			postcodeInput.value = '';

			if (selectedProvince && selectedDistrict && vietnamAddressData[selectedProvince].districts[selectedDistrict]) {
				const wards = vietnamAddressData[selectedProvince].districts[selectedDistrict].wards;
				const postcode = vietnamAddressData[selectedProvince].districts[selectedDistrict].postcode;
				
				Object.keys(wards).forEach(function (key) {
					const option = document.createElement('option');
					option.value = key;
					option.textContent = wards[key];
					districtSelect.appendChild(option);
				});
				districtSelect.disabled = false;
				postcodeInput.value = postcode;
			}
		});

		// Handle ward change - update postcode
		districtSelect.addEventListener('change', function () {
			const selectedProvince = stateSelect.value;
			const selectedDistrict = citySelect.value;
			
			if (selectedProvince && selectedDistrict && vietnamAddressData[selectedProvince].districts[selectedDistrict]) {
				const postcode = vietnamAddressData[selectedProvince].districts[selectedDistrict].postcode;
				postcodeInput.value = postcode;
			}
		});
	}

	/**
	 * Helper function to populate districts and wards with saved values
	 */
	function populateDistricts(prefix, provinceCode, savedCity, savedDistrict) {
		const citySelect = document.getElementById(prefix + '_city');
		const districtSelect = document.getElementById(prefix + '_district');
		const postcodeInput = document.getElementById(prefix + '_postcode');

		if (!provinceCode || !vietnamAddressData[provinceCode]) return;

		// Populate districts
		citySelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
		const districts = vietnamAddressData[provinceCode].districts;
		
		Object.keys(districts).forEach(function (key) {
			const option = document.createElement('option');
			option.value = key;
			option.textContent = districts[key].name;
			citySelect.appendChild(option);
		});
		
		citySelect.disabled = false;

		// Restore saved city value
		if (savedCity) {
			citySelect.value = savedCity;
			populateWards(prefix, provinceCode, savedCity, savedDistrict);
		}
	}

	/**
	 * Helper function to populate wards with saved values
	 */
	function populateWards(prefix, provinceCode, districtCode, savedWard) {
		const districtSelect = document.getElementById(prefix + '_district');
		const postcodeInput = document.getElementById(prefix + '_postcode');

		if (!districtCode || !vietnamAddressData[provinceCode].districts[districtCode]) return;

		// Populate wards
		districtSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
		const wards = vietnamAddressData[provinceCode].districts[districtCode].wards;
		const postcode = vietnamAddressData[provinceCode].districts[districtCode].postcode;
		
		Object.keys(wards).forEach(function (key) {
			const option = document.createElement('option');
			option.value = key;
			option.textContent = wards[key];
			districtSelect.appendChild(option);
		});
		
		districtSelect.disabled = false;
		postcodeInput.value = postcode;

		// Restore saved ward value
		if (savedWard) {
			districtSelect.value = savedWard;
		}
	}

	/**
	 * Copy billing address fields to shipping address fields
	 */
	function copyBillingToShipping() {
		const billingInputs = document.querySelectorAll('[name^="billing_"]');
		
		billingInputs.forEach(function (billingInput) {
			const fieldName = billingInput.name.replace('billing_', '');
			const shippingInput = document.querySelector('[name="shipping_' + fieldName + '"]');
			
			if (shippingInput) {
				if (billingInput.tagName === 'SELECT') {
					shippingInput.value = billingInput.value;
					// Trigger change event to update dependent dropdowns
					const event = new Event('change', { bubbles: true });
					shippingInput.dispatchEvent(event);
				} else {
					shippingInput.value = billingInput.value;
				}
			}
		});
	}
});
