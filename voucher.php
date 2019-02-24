<html>
	<head>
		<!-- CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="main.css">
		<!-- script -->
		<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
		<script type="text/javascript">
		    $( document ).ready(function() {
		    	$('.btnCheckout').click( function(){
		    		$('#s_subtotal').html('RM '+$(this).val());
		    		$('#s_voucher_ttl').html('RM '+$(this).val());
		    		$('#summary').show();
		    	});
		    	$('#voucher_form').submit( function(e){
		    		e.preventDefault();
		    		$.ajax({
		    		    type:'POST',
		    		    url:'voucher.php',
		    		    data:$(this).serialize(),
		    		    dataType:'json',
		    		    success: function(data){
		    		    	if(data['type'] == 'success'){
		    		    		$('#s_voucher_val').html(data['value']);
		    		    		$('#s_voucher_amt').html(data['amount']);
		    		    		$('#s_voucher_ttl').html(data['total']);
		    		    	}else{
		    		    		$('#error').html(data['msg']);
		    		    	}
		    		    },
		    		    error: function() {
		    		        alert('We are sorry! Service temporarily unavailable. Please contact support @ angsuijin@gmail.com.');
		    		    }
		    		});
		        });
		    });
		</script>
	</head>

	<body>
		<div class="row">
		    <div class="col-md-9">
		    	<div class="row">
		    		<div class="col-md-9">
			    			<p><label>Click on button below to simulate purchase checkout =)<br/>
			    			Checkout Summary will show on your left!</label></p>
			    			<button value="999.00" class="btnCheckout btn btn-info">Order RM 999</button>
			    			<button value="99.00" class="btnCheckout btn btn-danger">Order RM 99</button>
			    	</div>
		    	</div>
		    	<br/><br/><br/><br/>
		    	<p><label>Then... insert voucher code and click "USE" ! **psst.. try 20FORME</label></p>
		    	<form id="voucher_form" name="voucher_form" class="voucher_form" action="#" method="post">
		    		<div class="row">
		    			<div class="col-md-1">
		    				<label for="v_code">VOUCHER</label>
		    			</div>
		    			<div class="col-md-3">
		    				<input name="v_code" type="text" required size="25" maxlength="20" placeholder="Voucher/Promotional code" />
		    			</div>
		    			<div class="col-md-5">
		    				<input type="submit" value="USE" />
		    			</div>
		    		</div>
	    			<div class="row">
		    			<div class="col-md-1"></div>
		    			<div class="col-md-8">
		    				<div id="error" class="error"></div>
		    			</div>
	    			</div>
		    	</form>
		    </div>
		    <div class="col-md-3">
		    	<div id="summary" class="summary" style="display:none;">
		    		<label for="summary">SUMMARY</label>
		    		<div class="row">
			    		<div class="col-md-12">SUBTOTAL</div>
			    	</div>
		    		<hr/>
		    		<div class="row">
			    		<div class="col-md-6">FROM HERMO</div>
			    		<div class="col-md-6 text-right" id="s_subtotal"></div>
			    	</div>
		    		<div class="row">
			    		<div class="col-md-6" id="s_voucher_val"></div>
			    		<div class="col-md-6 text-right" id="s_voucher_amt"></div>
			    	</div>
		    		<hr/>
		    		<div class="row">
			    		<div class="col-md-6">TOTAL</div>
			    		<div class="col-md-6 text-right" id="s_voucher_ttl"></div>
			    	</div>
		    		<hr/>
		    	</div>
		    </div>
		</div>
	</body>

</html>
