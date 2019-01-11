// 弹出输入框
$('#to_pay_form').click(function(){
	alert('to pay form');
	if($('#yjfpay_form').is(':visible')){
		$('#yjfpay_form').hide();
	} else {
		$('#yjfpay_form').show();
	}
});

// $('#to_pay_form').click(function(){
// 	// consol.log('to_pay_form');
// 	alert('to_pay_form');
// 	$.post("./modules/ccpay/payment.php",{'data':'yjfpay'},function(ret){
// 		console.log(ret);
// 		 $('#yjfpay_form').html(ret);
// 		// $("span").html(result);
// 	},'html');
// });


//检查卡号是否正确
// 	function luhuCheck(sCardNum) {
// 		//if (sCardNum.charAt(0)==6) {return false;};
// 		var iOddSum = 0;
// 		var iEvenSum = 0;
// 		var bIsOdd = true;
// 		for(var i = sCardNum.length-1; i >= 0; i --){
// 			var iNum = parseInt(sCardNum.charAt(i));
// 			if(bIsOdd){
// 				iOddSum += iNum;
// 			}else{
// 				iNum = iNum * 2;
// 				if(iNum > 9){
// 					iNum = eval(iNum.toString().split('').join('+'));
// 				}
// 				iEvenSum += iNum;
// 			}
// 			bIsOdd = !bIsOdd;
// 		}
// 		return (iEvenSum + iOddSum) % 10 == 0;
// 	};
// function getnum(sourcestr){
// 	sourcestr = sourcestr.replace(/\s+/g,"");
// 	var str1 = sourcestr.slice(0,4);
// 	var str2 = sourcestr.slice(4,8);
// 	var str3 = sourcestr.slice(8,12);
// 	var str4 = sourcestr.slice(12,16);
// 	var str=str1 + ' '+ str2 + ' ' + str3 + ' ' + str4;
// 	$('input[name=ccpay_cc_number]').val(str);
// }
// $('input[name=ccpay_cc_number]').blur(function(){
// 		checkcardNo($(this));


// 	})
// $('input[name=ccpay_cc_number]').focus(function(){
// 	var val = $(this).val();
// 	val = val.replace(/\s/ig,'');
// 	$(this).val(val);
// 	$(this).removeClass('successinput');
// 	});

//Card number验证方法
// function checkcardNo(target){

// 	var value = target.val().replace(/\s/gi,'');;
// 	target.next('em').remove();
// 	var result = {
// 		res : false,
// 		type:'error-tips',
// 		message:'The card number is invalid!'
// 	};
// 	//4062540301082212
// 	var num4 = value.charAt(0);
// 	if(value.length==0){
// 		result.message = 'This field is required!';
// 	}else if(value.length!=16){
// 		result.message = 'Please enter at 16 characters!';
// 	}else if(!/^\d+$/.test(value)){
// 		result.message = 'Please enter valid number!';
// 	}else if(num4==1||num4==6||num4==7||num4==8||num4==9 ){
// 		result.message = 'Please enter Visa/JCB/MasterCard！'
// 	}else if(luhuCheck(value)){
// 		var num1=parseInt(value.slice(0,1));
// 		var num2=parseInt(value.slice(0,2));
// 		var num3=parseInt(value.slice(0,4));
// 		result.res = true;
// 		result.type = 'success-tips';
// 		if(num1==4){
// 			result.message = 'Visa Card';
// 			$('#ccpay_cc_credit').val("Visa");
// 			$('input[name=ccpay_cc_number]').addClass('successinput');
// 		}
// 		else if(num3>=2221 && num3<2721){
// 			result.message = 'MasterCard';
// 			$('#ccpay_cc_credit').val("MasterCard");
// 			$('input[name=ccpay_cc_number]').addClass('successinput');
// 		}
// 		else if(num2 >= 51 && num2 <= 55){
// 			result.message = 'MasterCard';
// 			$('#ccpay_cc_credit').val("MasterCard");
// 			$('input[name=ccpay_cc_number]').addClass('successinput');
// 		}
// 		else if(num2==35){
// 			result.message = 'JCB Card';
// 			$('#ccpay_cc_credit').val("JCB");
// 			$('input[name=ccpay_cc_number]').addClass('successinput');
// 		}else{
// 			result.res = false;
// 			result.type = 'error-tips';
// 			result.message = 'Please enter Visa/JCB/MasterCard！';
// 		}	
// 	}
// 	getnum(value);
// 	target.after($('<em>').addClass(result.type).text(result.message));
// 	return result.res;
// };