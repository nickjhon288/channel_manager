<?php	

class Stripe_payment extends CI_Controller{



	public function index(){

	}

	public function checkout()
	{

		$property_name = ucfirst(get_data(HOTEL,array('hotel_id'=>hotel_id()))->row()->property_name);
		//Busco skApikey para el Hotel
		$skApikeyinfo = $this->db->query("select user_password from user_connect_channel where channel_id = 39 and hotel_id=".hotel_id())->row();
		 
		 if($skApikeyinfo)
		 {
				$skApikey = $skApikeyinfo->user_password;
				$token = $this->CrearToken($skApikey);
				if (strlen($token["error"])==0)
				{
					$data="amount=".number_format($_POST['Amount'], 2, '', '')."&currency=".$_POST['currency']."&source=".$token['id']."&description=Reservation to Hotel ".$property_name;

					$Charge=$this->Charge($data,$skApikey);
						if (strlen($Charge["error"])==0)
						{
							
							$lon=(strlen($Charge['amount'])-2);
							
							print '<script language="JavaScript">'; 
							print 'alert("Successfully Applied Charge \n Amount: '.number_format(substr($Charge['amount'],0,$lon), 0, '', ',').'.'.substr($Charge['amount'],-2).'\n Currency: '.$_POST['currency'].' \n Description: '.$Charge['description'].' \n Id Transaction: '.$Charge['id'].'");'; 
							print '</script>'; 

					
						}
						else
						{
							print '<script language="JavaScript">'; 
							print 'alert("'.$Charge["error"].'");'; 
							print '</script>'; 
							
						}
			
				}
				else
				{
					print '<script language="JavaScript">'; 
					print 'alert("'.$token["error"].'");'; 
					print '</script>'; 
					
				}
				
		}
		else
		{
			print '<script language="JavaScript">'; 
			print 'alert("Stripe is not connected, configure stripe and try again");'; 
			print '</script>'; 
			
		}	

		$URL = $_POST['URL'];
		echo "<SCRIPT>window.location='$URL';</SCRIPT>"; 
		
	}

	public function CrearToken($skApikey)
	{
					$channelid= unsecure($_POST['channelid']);

               
               if ($channelid ==2)
               {
               		$URL = 'https://api.pci-proxy.com/v1/pull';
               }
               else
               {
               		$URL = 'https://api.stripe.com/v1/tokens';
               }
                   
                 	$XML = "card[number]=".$_POST['CC_NUMBER']."&card[exp_month]=".$_POST['CC_DATE']."&card[exp_year]=".$_POST['CC_YEAR'];
                 	//$mail_data = '<strong> Request </strong> <br>';
					//$mail_data .= $XML;
                    $ch = curl_init($URL);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER,  array("Authorization: Bearer ".$skApikey,"POST",'X-CC-MERCHANT-ID: 3000011638' , 'X-CC-SIGN: 170426055204280709' ,'X-CC-URL: https://api.stripe.com/v1/tokens'));
                    curl_setopt($ch, CURLOPT_POSTFIELDS,$XML );
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    $output = curl_exec($ch);
                    $mail_data = '<strong> Response </strong> <br>';
					$mail_data .= $output;	
					mail("datahernandez@gmail.com"," Stripe.Com Request and Response Token ",$mail_data);
					mail("xml@hoeratus.com"," Stripe.Com Request and Response Token",$mail_data);
                    curl_close($ch); 

                    $result = (json_decode($output,true));

                    $data['error']='';
                    //print_r($result);
                    foreach ($result as $value) {
                  
                    	if(isset($value['message'])){
                    	$data['error']=	$value['message'];
                    	}
                    	else
                    	{
                    		$data =array_merge($data,$result);
                    	}


                    }
                    	
                
                	return($data);              
                	   
	}
 	
 	public function Charge($datacharge,$skApikey)
 	{

 					$mail_data = '<strong> Request </strong> <br>';
					$mail_data .= $datacharge;
 		 			$ch = curl_init('https://api.stripe.com/v1/charges');
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER,  array("Authorization: Bearer ".$skApikey,"POST",'X-CC-MERCHANT-ID: 3000011638' , 'X-CC-SIGN: 170426055204280709' ,'X-CC-URL: https://api.stripe.com/v1/tokens'));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $datacharge);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    $output = curl_exec($ch);
                    $mail_data .= '<strong> Response </strong> <br>';
					$mail_data .= $output;	
					mail("datahernandez@gmail.com"," Stripe.Com Request and Response Charge ",$mail_data);
					mail("xml@hoeratus.com"," Stripe.Com Request and Response Charge",$mail_data);
                    curl_close($ch); 

                    $result = (json_decode($output,true));


                    $data['error']='';
                    //print_r($result);
                    foreach ($result as $value) {
                  
                    	if(isset($value['message'])){
                    	$data['error']=	$value['message'];
                    	}
                    	else
                    	{
                    		$data =array_merge($data,$result);
                    	}


                    }

                	return($data);   

 	}


}


?>