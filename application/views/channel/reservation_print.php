<link rel="stylesheet" href="<?php echo base_url();?>user_assets/css/style_dash.css">

<link rel="stylesheet" href="<?php echo base_url();?>user_assets/css/bootstrap.min.css">

<link rel="stylesheet" href="<?php echo base_url();?>user_assets/css/demo.css">

<link rel="stylesheet" href="<?php echo base_url();?>user_assets/css/font-awesome.min.css">

<div class="container cls_comm_ashbg cls_printmain">
  
  <div class="row">

  <?php
if($curr_cha_id==0)
{ 
      $room_name = $this->reservation_model->get_room_name_id($room_id);
      $room = $room_name->property_name;
      $meal_plan = $room_name->meal_plan;
      if($meal_plan!=0)
      {
            $meal = $this->reservation_model->get_meal_plan_id($room_name->meal_plan);
            $meal_name = $meal->meal_name;
      }
      else
      {
            $meal_name = 'Not Available';
      }
}
if($curr_cha_id==11)
{
      $char = array('0'=>'None', '1'=>'Continental Breakfast', '2'=>'Buffet Breakfast', '3'=>'Half Board', '4'=>'Full Board');
      foreach($char as $letter => $number) 
      {
            if($mealsinc==$letter)
            {
                  $meal_name = $number;
                  break;
            }
      }
}
?>

  <h2 class="cls_comhead"><?php echo ucfirst(get_data(HOTEL,array('hotel_id'=>hotel_id()))->row()->property_name); ?></h2>
      


      <h3><span>Reservation <?php 
        if($curr_cha_id != 2){
        echo '#'.$reservation_code ; 
      }else
      {
        echo '#'.$reser_id;
      }
        ?></span></h3>
      <div class="clearfix">
      <div class="col-md-10 col-sm-9 col-xs-12 fn center-block">      
      
      <div class="cls_prin50">
      <ul class="list-unstyled">
      <li><strong>Channel Name :</strong> 
      <?php if($curr_cha_id==0){echo 'Manual Booking';}
        else{
        $channel_name = $this->reservation_model->get_channel_name($curr_cha_id);
        echo $channel_name->channel_name;} ?></li>
      <li><strong>Confirmation number :  </strong>#<?php echo $reservation_code ; ?></li>
      <li><strong>Check-in date :  </strong> <?php echo date('M d,Y',strtotime(str_replace('/','-',$start_date))); ?></li>
      <li><strong>Check-out date : </strong><?php echo date('M d,Y',strtotime(str_replace('/','-',$end_date))); ?></li>
      <?php if(isset($adults)){ ?>
      <li><strong>Adults : </strong><?php echo $adults; ?></li>
      <?php }?>
      <?php if(isset($children)){ ?>
      <li><strong>Children :</strong><?php echo $children; ?></li>         
      <?php }?>
      <?php $nt = $num_nights; ?>
      </ul>
      </div>
      
      <div class="cls_prin50">
      <ul class="list-unstyled">
      <?php 
      if($curr_cha_id==0)
      {
      $user = get_data(TBL_USERS,array('user_id'=>user_id()))->row(); 
      $currency = $this->reservation_model->get_curreny_name($user->currency); 
            $currency = $currency->currency_code; 
      }
    ?>
      <li><strong>Subtotal : </strong>
      <?php echo $currency; ?> <?php if($curr_cha_id != 1 && $curr_cha_id != 8&& $curr_cha_id != 5) { echo $price*$nt; }else{ echo $price; } ?></li>
      <li><strong>Grand total :  </strong>
      <?php echo $currency; ?> <?php if($curr_cha_id != 1 && $curr_cha_id != 8 && $curr_cha_id != 5) { echo $price*$nt; }else{ echo $price; } ?></li>
      <li><strong>Status : </strong><?php echo $status; ?></li>
      <li><strong>Booked date : </strong><?php echo date('M d,Y',strtotime($booking_date)); ?><?php if(isset($booking_time)){ echo ":".$booking_time; } ?></li>
      </ul>
      </div>
    
      </div>
                  
      </div>
      
      <h3><span>Guest details</span></h3>
      
      <div class="clearfix">
      
      <div class="col-md-10 col-sm-9 col-xs-12 fn center-block">
            
      <div class="cls_prin50">
      <ul class="list-unstyled">
      <li><strong>Name :</strong><?php echo $guest_name; ?></li>
      <li><strong>Phone : </strong><?php echo $mobile; ?></li>
      <li><strong>E-mail :  </strong><?php echo $email; ?></li>    
      </ul>
      </div>
      
      <div class="cls_prin50">
      <ul class="list-unstyled">
      <li><strong>Street address : </strong><?php echo $street_name; ?></li>
      <?php
            if($curr_cha_id==0)
            {
        if($country!='')
        {
            $country = $this->reservation_model->get_country_name_id($country);
            $country=$country->country_name;
        }
        else
        {
          $country='----';
        }
            }
        ?>
      <li><strong>Country : </strong><?php echo $country; ?></li>
      </ul>
      </div>
      
      </div>
            
      </div>
      <?php if($curr_cha_id!=0){ ?>
      <h3><span>Additional Channel Details</span></h3>
      
      <div class="clearfix">
      
      <div class="col-md-10 col-sm-9 col-xs-12 fn center-block">    
      
      <div class="cls_prin50">
      <ul class="list-unstyled">
      <li><strong>Commission :</strong><?php echo $commission;?></li>
       <?php if(isset($channel_room_name)){ ?>
      <li><strong>Channel Room Name : </strong>  <?php echo $channel_room_name;?>  </li>
      <?php } ?>
      <?php if(isset($promocode)){ ?>
      <li><strong>Promo Code :</strong> <?php echo $promocode; ?></li>   
      <?php } ?>
      </ul>
      </div>      
      
      </div>
      
      </div>
      <?php }else{ ?>


      <h3><span>Additional Channel Details</span></h3>

      <div class="clearfix">
      ------
      </div>

      <?php } ?>
      
      <h3><span>Billing infomation</span></h3>
      
      <div class="clearfix">
      <div class="col-md-10 col-sm-9 col-xs-12 fn center-block">      
      
      <div class="cls_prin50">
      <ul class="list-unstyled">
      <li><strong>Payment method :</strong>
       <?php echo $payment_method; ?>
      </li>
      <?php if(isset($cvv)){?>
       <li><strong>CVV :</strong>
       <?php echo $cvv; ?>
      </li>
      <?php } ?>
      </ul>
      </div>
      
      </div>
      
      </div>
      
      <h3><span>Room Details</span></h3>
      
      <div class="clearfix">
      
      <div class="col-md-10 col-sm-9 col-xs-12 fn center-block">
      
      <div class="clearfix">
      
      <div class="cls_prin50">
      <ul class="list-unstyled">
      <li><strong>Guest count :</strong><?php echo $members_count; ?></li>

       <li><strong>Child count :</strong><?php if (isset($children)) {echo $children;}  else { echo "0" ;}?></li>

      <li><strong>Meal plan :</strong><?php echo $meal_name; ?></li>

      </ul>
      </div>
      
      <div class="cls_prin50">
      <ul class="list-unstyled">
      <li><strong>Check-in date : </strong><?php echo date('M d,Y',strtotime(str_replace('/','-',$start_date))); ?></li>
      <li><strong>Check-out date : </strong><?php echo date('M d,Y',strtotime(str_replace('/','-',$end_date))); ?></li>
      <li><strong>Total :  </strong><?php echo $currency; ?> <?php if($curr_cha_id != 1 && $curr_cha_id != 8 && $curr_cha_id != 5){ echo $price*$num_nights; }else{ echo $price; } ?></li>
      </ul>
      </div>
      
      </div>
      
      <div class="clearfix">
      
      <h4>Daily Price</h4>
      <div class="cls_prtable">
      <div class="table-responsive">
      <table class="res_table table table-striped">
      <thead>
      <tr>
      <th>Date</th>
      <th>Price</th>
      </tr>
      </thead>
      <tbody>
       <?php if($curr_cha_id != 1 && $curr_cha_id != 8 && $curr_cha_id != 2 && $curr_cha_id != 17  && $curr_cha_id != 15 && $curr_cha_id != 5 ) { ?>
      <?php $originalstartDate = date('M d,Y',strtotime(str_replace('/','-',$start_date)));
         $newstartDate = date("Y/m/d", strtotime($originalstartDate));
         $originalendDate = date('M d,Y',strtotime(str_replace('/','-',$end_date)));
         $newendDate = date("Y/m/d", strtotime($originalendDate));
         $begin = new DateTime($newstartDate);
         $ends = new DateTime($newendDate);
             $daterange = new DatePeriod($begin, new DateInterval('P1D'), $ends);
         foreach($daterange as $date){
          
    ?>

      <tr>
      <td><?php echo $date->format("M d, Y") . "<br>"; ?> </td>
      <td> Adult rate <?php echo $currency; ?> <?php echo $curr_cha_id=='5' ? number_format((float)$price/$num_nights, 2, '.', '') : $price; ?> </td>
      </tr>  
       <?php } ?>
          <?php }else { 
              if(is_numeric($price))
              {
            if(isset($perdayprice)){ 
            ?>
          <?php foreach($perdayprice as $key => $value){
          foreach ($value as $date => $val) { ?>

      <tr>
      <td>  <?php echo date('M d, Y' , strtotime($date)). "<br>"; ?></td>
      <td>   <?php echo $curr_cha_id=='5' ? number_format((float)$val, 2, '.', '') : $val; //echo $val; ?> <?php echo $currency; ?> </td>
      </tr>  
       <?php } } } }else{ ?> 

       <td><?php echo date('M d, Y' , strtotime($start_date)). " - ".date('M d, Y' , strtotime($end_date)); ?></td>

       <td> <?php echo $price; ?> </td> 
       <?php } } ?>
     
      </tbody>
      </table>
      </div>
      </div>
      </div>
      </div>
    </div>
    <?php if(isset($keylength) != 0){ ?>
    <h3><span>Extras</span></h3>
    
    <div class="clearfix">
      <div class="col-md-10 col-sm-9 col-xs-12 fn center-block">      
      
      <div class="cls_prin50">
      <ul class="list-unstyled">

       <?php 
    
        for($i=0;$i<$keylength;$i++){
          ?>
           <section style="float: left;height: 100%; padding: 0; position: relative;  width: 46%;">
          <?php
          foreach ($extradetails as $key => $extras) { ?>
          <?php if($key == "name" || $key == "totalprice" || $key =="persons"){?>

      <li><strong>Name :</strong><?php echo ucfirst($key); ?></li>

      <!-- <li><strong>Persons : </strong>1</li> -->

      <li><strong>Totalprice : </strong>  <?php if(is_array($extras)){ ?>
             <?php echo $extras[$i]; ?>
             <?php }else{
              echo $extras;
             } ?>
             <?php if($key == "totalprice"){ echo $currency; }?></li>
             <?php } } ?>
      </ul> 
      <?php } ?>    
      </div>
    
      </div>
                  
      </div>

      <?php } ?>

 <?php if(isset($ruid) != ""){ ?>
        <tr>
      <td> </td>
      <td><?php if($ruid!='') { echo $ruid; } ?> </td>
      </tr> 

      <?php } ?> 
      
      <h3><span>Notes</span></h3>
      
      <div class="cls_prinblk clearfix">      
      
      <div class="pull-left">
      <ul class="list-unstyled">
      <li><?php if($description!='') { echo $description; } else { echo 'No notes provide...';} ?></li>             
      </ul>
      </div>
      
      </div>
      
      <h3><span>Policies</span></h3>
	<div class="">
	
	 <div class="cls_prinblk clearfix">  
	 <ul class="list-unstyled">
      <?php if($curr_cha_id != 2){ ?>  
      
     

      <?php 
if($curr_cha_id==0)
{
      $cancel_details = get_data(PCANCEL,array('user_id'=>user_id()))->row(); 
      $other_details = get_data(POTHERS,array('user_id'=>user_id()))->row();
      $smoke = $other_details->smoking;
      $pets = $other_details->pets;
      $cancel_description = $cancel_details->description;
      $policy_checin = $other_details->check_in_time;
      $policy_checout   =$other_details->check_out_time;
}
elseif($curr_cha_id==11)
{
      $cancel_description ="Cancelations or changes made after";
}
?>    
      
      
      <li><strong>Cancellation :  </strong><?php echo $cancel_description; ?></li> 
      <li><strong>Check-in time :  </strong>After <?php echo date('M d,Y',strtotime(str_replace('/','-',$policy_checin))); ?> day of arrival.</li> 
      <li><strong>Check-out time :  </strong><?php  echo date('M d,Y',strtotime(str_replace('/','-',$policy_checout))); ?> upon day of departure.</li> 

      <?php } ?>
  <?php
  if($curr_cha_id==0 || $curr_cha_id == 2)
  { ?>

      <li><strong>Smoking :  </strong>
      <?php if($smoke == '1' && $curr_cha_id == 0){echo 'Smoking is Allowed';}else if($smoke == 0 && $curr_cha_id == 0){echo 'Smoking is not Allowed';}else if($smoke == "" && $curr_cha_id == 0){echo "No Preferences";} ?><?php if($smoke == '1' && $curr_cha_id == 2){echo 'Yes';}else if($smoke == 0 && $curr_cha_id == 2){echo 'No';}else if($smoke == "" && $curr_cha_id == 2){echo "No Preferences"; } ?>
      </li> 
      <?php if(isset($pets)){ ?>

       <li><strong>Pets :  </strong>
       <?php if($pets=='1'){echo 'Pets are Allowed';}else{echo 'No Pets Allowed';} ?>
       </li>
       <?php } ?>
       <?php if(isset($info)){ ?>
      <li><strong>Info : </strong><?php if($info !=''){echo $info; } ?></li>
      <?php } ?>

     
      <?php  }  ?>
	   </ul>
      </div>
      
      </div>


      <div class="clearfix">
      <div class="col-md-10 col-sm-9 col-xs-12 fn center-block signature">      
      
      <div class="cls_prin50 col-md-6">      
      <strong>Guest 1 :</strong>  ------------------------------  
      </div>

       <div class="cls_prin50 col-md-6">      
          <strong style="margin-left: 220px">Guest 2 :</strong>  ------------------------------
      </div>
      
      </div>
      
      </div>
    
  </div>
  </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="<?php echo base_url();?>user_assets/js/bootstrap.min.js"></script>