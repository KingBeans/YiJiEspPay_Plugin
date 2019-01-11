
<form id="submitPayForm" action="<?php echo $gatewayURL; ?>" method="POST">
    <?php foreach ($allOptions as $name => $value) { ?>
        <input type="hidden" name="<?php echo $name; ?>" value='<?php echo $value; ?>'/>
    <?php } ?>
</form>
<script type="text/javascript">
var submitPayForm = document.getElementById('submitPayForm');
    submitPayForm.submit();
</script>
