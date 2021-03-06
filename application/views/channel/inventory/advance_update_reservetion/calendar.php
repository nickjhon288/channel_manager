<style>

.booking_label label {
    color: #222222;
    display: inline-block;
    font-size: 14px;
    font-weight: 500;
    line-height: 22px;
    padding-left: 12px;
    position: relative;
    text-transform: initial;
    vertical-align: middle;
}

.booking_label label::before {  
    content: "";
    display: inline-block;
    height: 16px;
    left: 0;
    margin-left: -20px;
    position: absolute;
    top: 3px;
    transition: border 0.15s ease-in-out 0s, color 0.15s ease-in-out 0s;
    width: 16px;
    margin-right:3px;
}

.booking_label label::after {
    color: #51cbfb;
    display: inline-block;
    font-size: 10px;
    height: 9px;
    left: 0;
    line-height: 9px;
    margin-left: -19px;
    padding-left: 3px;
    padding-top: 0;
    position: absolute;
    top: 6px;
    vertical-align: middle;
    width: 9px;
    margin-right:3px;
}

.booking_reserved label::before{
	 background:<?php echo $this->theme_customize['b_reserved']!='' ? $this->theme_customize['b_reserved']:'#180cff' ?>;
}

.booking_confirmed label::before{
	 background:<?php echo $this->theme_customize['b_confirmed']!='' ? $this->theme_customize['b_confirmed']:'#180cff' ?>;
}

.booking_checkin label::before{
	 background:<?php echo $this->theme_customize['b_canceled']!='' ? $this->theme_customize['b_canceled']:'#180cff' ?>;
}

.booking_checkout label::before{
	 background:<?php echo $this->theme_customize['b_pending']!='' ? $this->theme_customize['b_pending']:'#180cff' ?>;
}



.booked3{
  background:<?php echo $this->theme_customize['b_reserved']!='' ? $this->theme_customize['b_reserved']:'#180cff' ?>;
  
}

.booked1{
  background:<?php echo $this->theme_customize['b_confirmed']!='' ? $this->theme_customize['b_confirmed']:'#ff135a' ?>;
}

.booked2{
background: <?php echo $this->theme_customize['b_canceled']!='' ? $this->theme_customize['b_canceled']:'#fff' ?>; 
}

.booked4{
  background:<?php echo $this->theme_customize['b_pending']!='' ? $this->theme_customize['b_pending']:'#180cff' ?>;
  
}
.booked{
background: #366092; 
}
</style>


<div class="content">
<div class="">
<div class="row">
<div class="dash-b-n1 new-s">
	<div class="row-fluid clearfix">
		<div class="col-md-12 col-sm-12 new-k2">
			<div class="dash-b-n2 mar-top-30">
				<div class="row">
					<div class="col-md-2 col-sm-2">
					<label class="switch">
						<input class="switch-input reservationyes" type="checkbox" name="on-offswitch" id="myon-offswitch" checked>
						<span class="switch-label" data-on="Channel" data-off="Main"></span> 
						<span class="switch-handle"></span> 
					</label>
					<input type="hidden" name="cur_month" id="next_month" value="<?php echo $nextMonthDate->format('d/m/Y');?>" />
					<input type="hidden" name="cur_month" id="prev_month" value="<?php echo $prevMonthDate->format('d/m/Y');?>" />
					</div>
						<!--<div class="col-md-2 col-sm-2">
						<div class="">
							<div class="on-2">
								<div class="on-offswitch">
									<input type="checkbox" name="on-offswitch" class="on-offswitch-checkbox reservationyes" id="myon-offswitch" checked>
									<label class="on-offswitch-label" for="myon-offswitch">
										<span class="on-offswitch-inner reservationyes"></span>
										<span class="on-offswitch-switch"></span>
									</label>
								</div>
							</div>
							
						</div>
						</div>-->
					<div class="col-md-2 col-sm-2 bor-o">
						<a class="btn btn-default" href="javascipt:;" id="show_pop" role="button" data-toggle="modal" data-target="#myModal-p" data-backdrop="static" data-keyboard="false">Customize calendar</a>
					</div>
					<?php if(user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1') { ?>
						<div class="col-md-2 col-sm-2 org">
							<a class="btn btn-warning main_full_update_modal" href="javascript:;" role="button">Full Update</a><!--  myModal-p2-->
						</div>
					<?php } ?>
					<div class="col-md-3 col-sm-3 dr">
						<a href="javascript:;" class="pull-left mar-right prev_month <?php if( $prevMonthDate < $todayMonth ) { ?>display_none<?php } ?>"><img src="<?php echo base_url();?>user_assets/images/pre.png"></a>
						<div class="dropdown pull-left">
							<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu_item1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								<?php echo $startDate->format('F Y');?>	
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu" aria-labelledby="dropdownMenu1" id="ajax_cal">
								<?php foreach( $monthPeriod as $date ) { ?>
									<li class="<?php if($startDate->format('m')==$date->format('m')) { ?>active<?php } ?> change_month"custom="<?php echo $date->format('d/m/Y');?>">
										<a href="javascript:;" ><?php echo $date->format('F Y'); ?></a>
									</li>
								<?php } ?>
							</ul>
						</div>
						<div class="dropdown pull-left"></div>
						<a href="javascript:;" class="pull-left mar-left next_month"><img src="<?php echo base_url();?>user_assets/images/next.png"></a>
					</div>
					<?php if(user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1') {?>
						<div class="col-md-3 col-sm-3 bor-o">
							<a class="btn btn-default" href="<?php echo site_url('inventory/bulk_update/reservation_yes');?>" role="button">Bulk Update</a>
						</div>
					<?php } ?>
				</div>
			</div>
			<div id="customize_date">
				<input type="hidden" name="cal_start" id="cal_start" value="<?php echo $startDate->format('d/m/Y'); ?>" />
				<input type="hidden" name="cal_end" id="cal_end" value="<?php echo $endDate->format('d/m/Y'); ?>"/>
				<div id="resp_div" style="display: none"></div> 
				<form id="main_cal">
					<input type="hidden" name="alter_checkbox" id="alter_checkbox" />
					<input type="hidden" name="alter_checkbox_rate" id="alter_checkbox_rate" />
					<input type="hidden" id="show_ss" name="show_ss" value="0" />
					<div class="">
						<div class="table table-responsive">
							<table class="table table-bordered table_stricky " id="reservation_yes_tbl">
								<thead>
									<tr>
										<th width="400" align="left"></th>
										<?php foreach( $months as $month => $colspan ) { ?>
											<th colspan="<?php echo $colspan;?>" class="text-center tal_td_bor">
												<h3><strong><?php echo $month; ?></strong></h3>
											</th>
										<?php } ?>
									</tr>
									<tr>
										<td width="400" bgcolor="#3ac4fa"></td>
										<td width="7" bgcolor="#3ac4fa">&nbsp;</td>
										<?php foreach($period as $date) { ?>
											<td width="28" bgcolor="<?php echo ($date->format("D")=='Sat' || $date->format("D")=='Sun') ? "#366092" : "#3ac4fa";?>"><?php echo $date->format("d")?> <br> <?php echo $date->format("D")?></td>
										<?php } ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach($properties as $property) { ?>
									<tr class="p-b-o">
										<td rowspan="3" class="ha2 ss_main_row" width="400">
											<a
												href="javascript:;"
												class="show_e"
												onClick="toggle_visibility('contents_<?php echo $property->property_id;?>','show_plus_<?php echo $property->property_id;?>');"
											>
												<span class="pull-left text-info"><strong><?php echo ucfirst($property->property_name);?></strong></span>
												<?php if( $property->pricing_type == 2 || ( $property->pricing_type == 1 && $property->non_refund == 1 ) || count($property->rate_types) ) { ?>
													<i class="fa fa-plus show_plus_<?php echo $property->property_id;?>"></i>
												<?php } ?>
									   	</a>
								   	</td>
										<td bgcolor="#a6a6a6">P</td>
										<?php
										foreach($period as $date)
										{
											$separate_date = $date->format("d/m/Y");
											
											$update = isset($property->updates[$separate_date]) ? $property->updates[$separate_date] : null;
											
											if( $update )
											{
											?>
												<td>
													<?php if( $date >= $today && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1' ) { ?>
														<a
															href="javascript:;"
															id="inline_username"
															class="inline_username inline_price"
															data-type="number"
															data-name="price"
															data-pk="<?php echo $separate_date.'-'.$property->property_id;?>"
															data-url="<?php echo site_url('inventory/inline_edit_no')?>"
															data-title="Change Price"
														>
															<?php echo floatval($update->price); ?>
														</a>
													<?php	} else { ?>
														<?php echo floatval($update->price); ?>
													<?php	} ?>
												</td>
											<?php	
											}
											else
											{
												?>
												<td>
													<?php if( $date >= $today && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1' ) { ?>
														<a
															href="javascript:;"
															id="inline_username"
															class="inline_username inline_price"
															data-type="number"
															data-name="price"
															data-pk="<?php echo $separate_date.'-'.$property->property_id;?>"
															data-url="<?php echo site_url('inventory/inline_edit_no')?>"
															data-title="Change Price"
														>
															<?php echo 'N/A'; ?>
														</a>
													<?php	} else { ?>
														<?php echo 'N/A'; ?>
													<?php	} ?>
												</td>
												<?php
											}
										}
										?>
									</tr>
									<tr class="p-b-o">
										<td bgcolor="#a6a6a6">A</td>
										<?php 
										foreach($period as $date)
										{
											$separate_date = $date->format("d/m/Y");
											
											$update = isset($property->updates[$separate_date]) ? $property->updates[$separate_date] : null;
											
											$color = ($date->format("D")=='Sat' || $date->format("D")=='Sun') ? '#669933' : '#D9E4C3';
											$color_class = ($date->format("D")=='Sat' || $date->format("D")=='Sun') ? 'tabl_add' : 'tabl_even';
											
											if( $update )
											{
												/* if($update->availability==0)
												{
													$color = '#FF0000';
												}
												elseif($update->stop_sell==1)
												{
													$color = '#FF0000';
												}
												elseif($update->availability<0)
												{
													$color = '#F4C327';
												} */
												
												if($update->availability==0 || $update->stop_sell==1)
												{
													$color = '#FF0000';
												}
												if($update->availability==0)
												{
													$color = '#FF0000';
												}
												elseif($update->stop_sell==1)
												{
													$color = '#CC99FF';
												}
												elseif($update->availability < 0)
												{
													$color	= '#F4C327';
												}
												?>
												<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr <?php echo str_replace('/','_',$separate_date).'_'.$property->property_id.'_M';?>"> 
													<?php if( $date >= $today && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1' ) { ?>
														<a
															href="javascript:;"
															id="inline_username"
															class="inline_username inline_availability"
															data-type="number"
															data-name="availability"
															data-pk="<?php echo $separate_date.'-'.$property->property_id;?>"
															data-url="<?php echo site_url('inventory/inline_edit_no')?>"
															data-title="Change Availability"
														>
															<?php echo floatval($update->availability); ?>
														</a>
													<?php	} else { ?>
														<?php echo floatval($update->availability); ?>
													<?php	} ?>
												</td>
												<?php	
											}
											else
											{
												?>
												<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr <?php echo str_replace('/','_',$separate_date).'_'.$property->property_id.'_M';?>">
													<?php if( $date >= $today && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1' ) { ?>
														<a
															href="javascript:;"
															id="inline_username"
															class="inline_username inline_availability"
															data-type="number"
															data-name="availability"
															data-pk="<?php echo $separate_date.'-'.$property->property_id;?>"
															data-url="<?php echo site_url('inventory/inline_edit_no')?>"
															data-title="Change Availability"
														>
															<?php echo 'N/A'; ?>
														</a>
													<?php	} else { ?>
														<?php echo 'N/A'; ?>
													<?php	} ?>
												</td>
												<?php
											}
										}
										?>  
									</tr>
									
									<tr class="p-b-o">
										<td bgcolor="#a6a6a6">M</td>
										<?php 
										foreach($period as $date)
										{
											$separate_date = $date->format("d/m/Y");
											
											$update = isset($property->updates[$separate_date]) ? $property->updates[$separate_date] : null;
											
											$color = ($date->format("D")=='Sat' || $date->format("D")=='Sun') ? '#669933' : '#D9E4C3';
											$color_class = ($date->format("D")=='Sat' || $date->format("D")=='Sun') ? 'tabl_add' : 'tabl_even';
											
											if( $update )
											{
												/* if($update->availability==0)
												{
													$color = '#FF0000';
												}
												elseif($update->stop_sell==1)
												{
													$color = '#FF0000';
												}
												elseif($update->availability<0)
												{
													$color = '#F4C327';
												} */
												?>
												<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr <?php echo str_replace('/','_',$separate_date).'_'.$property->property_id.'_M';?>"> 
													<?php if( $date >= $today && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1' ) { ?>
														<a
															href="javascript:;"
															id="inline_username"
															class="inline_username inline_availability"
															data-type="number"
															data-name="minimum_stay"
															data-pk="<?php echo $separate_date.'-'.$property->property_id;?>"
															data-url="<?php echo site_url('inventory/inline_edit_no')?>"
															data-title="Change Minimum Stay"
														>
															<?php echo floatval($update->minimum_stay); ?>
														</a>
													<?php	} else { ?>
														<?php echo floatval($update->minimum_stay); ?>
													<?php	} ?>
												</td>
												<?php	
											}
											else
											{
												?>
												<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr <?php echo str_replace('/','_',$separate_date).'_'.$property->property_id.'_M';?>">
													<?php if( $date >= $today && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1' ) { ?>
														<a
															href="javascript:;"
															id="inline_username"
															class="inline_username inline_availability"
															data-type="number"
															data-name="minimum_stay"
															data-pk="<?php echo $separate_date.'-'.$property->property_id;?>"
															data-url="<?php echo site_url('inventory/inline_edit_no')?>"
															data-title="Change Minimum Stay"
														>
															<?php echo 'N/A'; ?>
														</a>
													<?php	} else { ?>
														<?php echo 'N/A'; ?>
													<?php	} ?>
												</td>
												<?php
											}
										}
										?>  
									</tr>
									

									
									<tr class="p-b-o ss_main show_content_<?php echo $property->property_id; ?>" id="stop_sale_<?php echo $property->property_id; ?>" style="display: none"> <td> show <td>	</tr>
									<tr class="p-b-o show_content_<?php echo $property->property_id; ?>" style="display: none"> <td> show <td>	</tr>
									<?php } ?>
									<tr>
										<td colspan="<?php echo $startDate->diff($endDate)->format('%a') + 3; ?>" bgcolor="#3ac4fa" style="text-align:left; color:#fff; padding-left:20px;">
											<div class="col-md-10">
												<div class="col-md-3 pull-left">
												<div class="cls_bulk_checkbox">
												  <input id="show_reservation" class="styled" type="checkbox" onclick="changevalue()">
												  <label for="show_reservation"> Show Reservations</label>
												</div>
												<body onload="funcion_inicial()"> </body>
													<!--<div class="checkbox mar-top7">
														<label><input type="checkbox" id="show_reservation"> Show Reservations</label>
													</div>-->	
												</div>
									
												<div class="col-md-2 pull-left">
												<div class="cls_bulk_checkbox">
												  <input id="stop_sell_main" class="styled" type="checkbox">
												  <label for="stop_sell_main"> Stop Sell</label>
												</div>
													<!--<div class="checkbox mar-top7">
														<label><input type="checkbox" id="stop_sell_main">  Stop Sell</label>
													</div>-->
												</div>


												<div class="col-md-2 pull-left">
												<div class="">
												<div class="booking_label booking_reserved">
												  <label for="">Booking</label>
												  </div>
												</div>
												</div>												

												<div class="col-md-2 pull-left">
												<div class="">
												<div class="booking_label booking_confirmed">
												  <label for="">Confirmed</label>
												  </div>
												</div>
												</div>


												<div class="col-md-2 pull-left">
												<div class="">
												<div class="booking_label booking_checkin">
												  <label for=""> Check - In</label>
												  </div>
												</div>
												</div>	

												<div class="col-md-2 pull-left">
												<div class="">
												<div class="booking_label booking_checkout">
												  <label for=""> Check - Out</label>
												  </div>
												</div>
												</div>	

											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</div>
<?php 
$data['calendar'] = 'Advance Update';

                $user_details = get_data(TBL_USERS,array('user_id'=>user_id()))->row_array();
                $data= array_merge($data,$user_details);
$this->load->view('channel/dash_sidebar',$data); ?>

<?php  $available1= get_data('ConfigUsers',array('userid'=>current_user_type(),'Hotelid'=>hotel_id()))->row_array();

if(count($available1)!=0)
{
	if($available1['CalenderShowR']==1)
	{	?>
		<script>
		document.getElementById("show_reservation").click();
		function funcion_inicial(){

		var ch =document.getElementById("show_reservation");
			if(ch.checked){
				ch.checked=false;
				}
				
			ch.click();
			ch=null;

		}
		</script>

	<?php }
}
?>

<script>

  function changevalue()
  {
  	var base_url = document.getElementById('base_url').value;
  		
  	if(document.getElementById("show_reservation").checked)
  	{
  		var valor =1;
  	}
  	else
 	{
 		var valor =0;
  	}	
  	
  		$.ajax({
				type: "POST",
				url: base_url+'inventory/saveuserconfig',
				data: "valor="+valor,
				success: function(result)
				{
					
				}
			});
  	
  }

</script>
