<form method="post" action=<?php echo esc_attr($dotpay_url) ?>>
    <h3><?php __('Transaction Details', 'dotpay-payment-gateway') ?></h3>

    <p><?php __('You chose payment by Dotpay. Click Continue do proceed', 'dotpay-payment-gateway') ?></p>
	
    <p class="form-submit">
        <input type="hidden" value="<?php echo $dotpay_id; ?>" name="id">
        <input type="hidden" value="<?php echo $amount; ?>" name="amount">
        <input type="hidden" value="<?php echo $firstname; ?>" name="firstname">
        <input type="hidden" value="<?php echo $lastname; ?>" name="lastname">
        <input type="hidden" value="<?php echo $street; ?>" name="street">
        <input type="hidden" value="<?php echo $street_n1; ?>" name="street_n1">
        <input type="hidden" value="<?php echo $city; ?>" name="city">
        <input type="hidden" value="<?php echo $postcode; ?>" name="postcode">
        <input type="hidden" value="<?php echo $country; ?>" name="country">
        <input type="hidden" value="<?php echo $email; ?>" name="email">
        <input type="hidden" value="<?php echo $payment_currency; ?>" name="currency">
        <input type="hidden" value="<?php echo $return_url; ?>" name="URL">
        <input type="hidden" value="<?php echo $notify_url; ?>" name="URLC">
        <input type="hidden" value="<?php echo $payment_type; ?>" name="type">
        <input type="hidden" value="<?php echo $description; ?>" name="description">
        <input type="hidden" value="<?php echo $control; ?>" name="control">
        <input type="hidden" value="<?php echo $api_version; ?>" name="api_version">
        <input type="hidden" value="<?php echo $signature; ?>" name="chk">
        <input class="button" type="submit" value="<?php __('Continue', 'dotpay-payment-gateway') ?>" id="submit_dotpay_payment_form">
    </p>
</form>