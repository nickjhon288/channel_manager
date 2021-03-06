<?php
$rate_id="";

$rate_name="";

if(count($all_room)!=0) 
{
	if($all_channel)
	{
		foreach($all_channel as $con_chs)
		{
			extract($con_chs);
			$con_ch = $channel_id;
			if($con_ch==2)
			{
				$chk_allow = get_data(CONNECT,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'channel_id'=>$con_ch))->row()->xml_type;
			}
			else
			{	
				$chk_allow = '';
			}
			if(($con_ch==2 && ($chk_allow==2 || $chk_allow==3))||$con_ch!=2)
			{
				$pid="";
				$cha_logo = get_data(TBL_CHANNEL,array('channel_id'=>$con_ch))->row();
				$channelid=$cha_logo->channel_id;
?>
				<div class="change_month_replaces_<?php echo $con_ch;?>">
		
				<div class="dash-b-n1">
		
				<div class="row-fluid clearfix">
		
				<div class="col-md-12 col-sm-12 new-k2">
		
				<div class="dash-b-n2 mar-top-30">
		
				<div class="row">
		
				<div class="col-md-3 col-sm-3"><div class="">
		
				<div class="on-2">
				
				<label class="switch">
						<input class="switch-input reservationno" type="checkbox" name="on-offswitch" id="myon-offswitch" checked>
						<span class="switch-label" data-on="Main" data-off="Channel"></span> 
						<span class="switch-handle"></span> 
					</label>
				</div>
		  
				<input type="hidden" name="cur_month" id="next_months" value="<?php echo date('m')+1;?>" />
		
				<input type="hidden" name="cur_month" id="prev_months" value="<?php echo date('m')-1;?>" />
		
				</div>
				
				</div>
		
				<div class="col-md-3 col-sm-3 bor-o">
		
					<a class="btn btn-default change_cal_custom" href="javascript:;" role="button" data-toggle="modal" data-target="#myModal-ps" current-channel="<?php echo $con_ch;?>" current-rate="<?php echo $rate_id;?>">Customize calendar</a>
		
				</div>
				
				<div class="col-md-3 col-sm-3 bor-o">
		
					<a href="javascript:;" class="pull-left mar-right prev_months display_none" current-channel="<?php echo $con_ch;?>" current-rate="<?php echo $rate_id;?>"><img src="<?php echo base_url();?>user_assets/images/pre.png"></a>
		
					<div class="dropdown pull-left">
		
						<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu_item1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					
						<?php echo date('F Y');?>	
					
						<span class="caret"></span>
					
						</button>

						<ul class="dropdown-menu" aria-labelledby="dropdownMenu1" id="ajax_cal">
						<?php for ($m=date('m'); $m<=date('m')+14; $m++) {?>
						<li current-channel="<?php echo $con_ch;?>" class="<?php if(date('m')==$m) { ?>active<?php } ?> change_months" custom="<?php echo $m;?>"><a href="javascript:;" >
						<?php echo date('F Y', mktime(0,0,0,$m, 1, date('Y'))); //echo date('F Y', mktime(0,0,0,$m));?></a></li>
						<?php } ?>
						</ul>
		
					</div>
		
					<a href="javascript:;" class="pull-left mar-left next_months" current-channel="<?php echo $con_ch;?>" current-rate="<?php echo $rate_id;?>"><img src="<?php echo base_url();?>user_assets/images/next.png"></a>
		
				</div>
				<?php if(user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){ ?>
				<div class="col-md-3 col-sm-3 bor-o">
				<a class="btn btn-default" href="<?php echo lang_url();?>inventory/bulk_update/reservation_no" role="button">Bulk Update</a>
				</div>
				<?php } ?>
				</div>	
				</div>
		
				<div id="customize_dates_<?php echo $con_ch;?>">
				<?php
		
				$today = date('d/m/Y');
		
				$today_date = date('Y/m/d');
		
				if(date('d')=='01')
				{
					$tomorrow = date('t/m/Y');
				}		
				else
				{
					$tomorrow = date('d/m/Y',strtotime($today_date."+1 months"));
				}
		
				$startDate = DateTime::createFromFormat("d/m/Y",$today);
		
				$endDate = DateTime::createFromFormat("d/m/Y",$tomorrow);
		
				$periodInterval = new DateInterval("P1D");
		
				$endDate->add( $periodInterval );
		
				$period = new DatePeriod( $startDate, $periodInterval, $endDate );
		
				$dates = array();
		
				$months = array();
		
				$month   = array();
		
				$last   = array();
		
				$da=array();
		
				$fi = 1;
		
				$li = 1;
		
				$thead = '';
				
				foreach($period as $date)
				{
					$dates[] 	= $date->format("d");
			
					$months[] 	= date('F Y',strtotime($date->format("Y/m/d")));
			
					$month[] 	= $date->format("m"); 
			
					$last[]     = $date->format("t"); 
			
					$da[]		= $date->format("d/m/Y");
			
					if($date->format("m")==date('m'))
					{
						$month_s 	= date('F Y',strtotime($date->format("Y/m/d")));
						$fi++;
					}
					else
					{
						$month_l = date('F Y',strtotime($date->format("Y/m/d")));
						$li++;
					}
					
					if($date->format("D")=='Sat' || $date->format("D")=='Sun')
					{
						$bg_clr = "#366092";
					}
					else
					{
						$bg_clr = "#3ac4fa";
					}
					
					$thead.='<td width="28" bgcolor="'.$bg_clr.'">'.$date->format("d").'<br>'.$date->format("D").'</td>';
		
				}
		
				$show_month = (array_unique($month));
		
				$last_date   = (array_unique($last));
		?>
		
		<input type="hidden" name="cal_start_<?php echo $con_ch; ?>" id="cal_start_<?php echo $con_ch; ?>" value="<?php echo $today; ?>" />
				
		<input type="hidden" name="cal_end_<?php echo $con_ch; ?>" id="cal_end_<?php echo $con_ch; ?>" value="<?php echo $tomorrow; ?>"/>
		
		<div id="resp_div_<?php echo $con_ch;?>" style="display: none"></div>
			
		<form method="post" action="<?php echo lang_url();?>inventory/reservation_update_no" class="master_calendar_form form-inline">
		
		<input type="hidden" name="channe_id_update" id="channe_id_update" value="<?php echo $con_ch;?>"/>
		
		<input type="hidden" name="alter_checkbox" id="alter_checkbox_<?php echo $con_ch;?>" />
		
		<input type="hidden" name="alter_checkbox_refund" id="alter_checkbox_refund_<?php echo $con_ch;?>" />
		
		<input type="hidden" name="alter_checkbox_rate" id="alter_checkbox_rate_<?php echo $con_ch;?>" />
		
		<input type="hidden" name="alter_checkbox_rate_refund" id="alter_checkbox_rate_refund_<?php echo $con_ch;?>" />
		
		<div class="content_check">
		
		<div class="table table-responsive">
		
		<table class="table table-bordered reservation_no_channel table_stricky">
		
		<thead>
		
		<tr>
		
			<th width="400" align="left" valign="middle" bgcolor="#ffffff" style="background:#ffffff;"><img src="data:image/png;base64,<?php echo base64_encode(file_get_contents("uploads/channel/".$cha_logo->logo_channel));?>"></th>
		
		<?php 
		$t_col='0';
		foreach($show_month as $val)  
		{
			if($val==date('m')){ $c_head = $month_s; $col=$fi; $t_col=$t_col+$col;} else { $c_head = $month_l; $col=$li-1;$t_col=$t_col+$col;}
		 ?>
		
		<th colspan="<?php echo $col;?>" class="text-center tal_td_bor">
		
		<h3>
		
		<strong><?php echo $c_head; ?></strong>
		
		</h3>
		
		</th>
		
		<?php } ?>
		
		</tr>
		
		<tr>
		
			<td width="400" bgcolor="#3ac4fa">
				<div class="checkbox col-md-6 mp">
				
					<div class="cls_bulk_checkbox">
						<input id="cal_m_<?php echo $con_ch;?>" value="1" onclick="msccc('m_<?php echo $con_ch;?>','<?php echo $con_ch;?>')" class="styled" type="checkbox">
						<label for="cal_m_<?php echo $con_ch;?>"> Min</label>
					</div>
					
				</div>
				<div class="checkbox col-md-6 mp">

					<div class="cls_bulk_checkbox">
						<input id="cal_s_<?php echo $con_ch;?>" value="1" onclick="msccc('s_<?php echo $con_ch;?>','<?php echo $con_ch;?>')" class="styled" type="checkbox">
						<label for="cal_s_<?php echo $con_ch;?>"> Stop</label>
					</div>
					
				</div>
			<?php if($con_ch!=17) { ?>
				<div class="checkbox col-md-6 mp">
			
					<div class="cls_bulk_checkbox">
						<input id="cal_c1_<?php echo $con_ch;?>" value="1" onclick="msccc('c1_<?php echo $con_ch;?>','<?php echo $con_ch;?>')" class="styled" type="checkbox">
						<label for="cal_c1_<?php echo $con_ch;?>"> CTA</label>
					</div>
					
				</div>
				<div class="checkbox col-md-6 mp">

					<div class="cls_bulk_checkbox">
						<input id="cal_c2_<?php echo $con_ch;?>" value="1" onclick="msccc('c2_<?php echo $con_ch;?>','<?php echo $con_ch;?>')" class="styled" type="checkbox">
						<label for="cal_c2_<?php echo $con_ch;?>"> CTD</label>
					</div>
					
				</div>
			<?php } ?>
			</td>

			<td width="7" bgcolor="#8db4e2">&nbsp;</td>
			
			<?php 
			$current_day_this_month = date('d'); 

			$last_day_this_month  = date('t');
			
			echo $thead;
			
			?>
		
			</tr>
		
		</thead>
		
			<tbody>
			<?php
			$all_propertyid = $this->channel_model->getMappedRoom_Rate($channelid);
			if(count($all_propertyid!=0))
			{
				foreach($all_propertyid as $propertyid)
				{	
						
					$pid=$propertyid->property_id;
			
					$rate_id=$propertyid->rate_id;
			
					if($rate_id!="0")
					{
						$ratename= get_data('room_rate_types',array('rate_type_id'=>$rate_id),'rate_name,uniq_id')->row();
						
						if($ratename->rate_name!='')
						{
							$rate_name=$ratename->rate_name;
						}
						else
						{
							$rate_name='#'.$ratename->uniq_id;
						}
					}
					
					$all_room = get_data(TBL_PROPERTY,array('property_id'=>$pid,'status'=>'Active','owner_id'=>current_user_type(),'droc'=>'1'),'property_id,non_refund,member_count,property_name,pricing_type')->result_array();
	
					foreach($all_room as $room) 
					{
						extract($room);

						if($non_refund==1)
						{
							$members = $member_count+$member_count;
						}
						else
						{
							$members = $member_count;
						}
						if($rate_id=="0")
						{
							$main_available = get_data(MAP,array('property_id'=>$property_id,'rate_id'=>'0','guest_count'=>'0','refun_type'=>'0','channel_id'=>$con_ch))->row_array(); //echo count($main_available); 			
							
							if(count($main_available)!=0)
							{
								$Mprice_data='';
								$Mavail_data='';
								foreach($period as $date)
								{
									$ss_da = $date->format("d/m/Y");
							
									if($date->format("D")=='Sat' || $date->format("D")=='Sun')
									{
										$color='#669933';
									}	
									else
									{
										$color='#D9E4C3';
									}
									if(in_array($ss_da,$da))
									{
										$single = get_data(TBL_UPDATE,array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da),'availability,price,stop_sell')->row();
										
										if(count($single)!='0')
										{
											if($single->availability==0 || $single->stop_sell==1)
											{
												$pcolor = '#FF0000';
											}
											if($single->availability==0)
											{
												$color = '#FF0000';
											}
											elseif($single->stop_sell==1)
											{
												$color = '#CC99FF';
											}
											elseif($single->availability < 0)
											{
												$color	= '#F4C327';
											}
											
											$Mprice_data.='<td>';
											
											if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
											{
												$Mprice_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'" data-url="'.lang_url().'inventory/inline_edit_channel" data-title="Change Price">'.floatval($single->price).'</a>';
											}
											else
											{ 
												$Mprice_data.=floatval($single->price);
											}
											
											$Mprice_data.='</td>';
											
											
											$Mavail_data.='<td width="28" bgcolor="'.$color.'" class="w_bdr">';
											if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
											{
												$Mavail_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'" data-url="'.lang_url().'inventory/inline_edit_channel" data-title="Change Availability">'.$single->availability.'</a>';
											}
											else
											{
												$Mavail_data.=$single->availability;
											}
											
											$Mavail_data.='</td>';
										}
										else  
										{
											$Mprice_data.='<td>';
											
											if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
											{
												$Mprice_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'" data-url="'.lang_url().'inventory/inline_edit_channel" data-title="Change Price"> N/A </a>';
											} 
											else 
											{
												$Mprice_data.='N/A';
											}
											
											$Mprice_data.='</td>';
											
											$Mavail_data.='<td width="28" bgcolor="'.$color.'?>" class="w_bdr">';
											
											if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
											{
												$Mavail_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'" data-url="'.lang_url().'inventory/inline_edit_channel" data-title="Change Availability"> N/A </a>';
											}
											else
											{
												$Mavail_data.='N/A';
											} 
											
											$Mavail_data.='</td>';
										}
									}
									else 
									{
										$Mprice_data.='<td>';
										
										if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
										{
											$Mprice_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'" data-url="'.lang_url().'inventory/inline_edit_channel" data-title="Change Price"> N/A </a>';
										} 
										else 
										{
											$Mprice_data.='N/A';
										}
										
										$Mprice_data.='</td>';
										
										$Mavail_data.='<td width="28" bgcolor="'.$color.'" class="w_bdr">';
										
										if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
										{
											$Mavail_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'" data-url="'.lang_url().'inventory/inline_edit_channel" data-title="Change Availability"> N/A </a>';
										}
										else
										{
											$Mavail_data.='N/A';
										}
										
										$Mavail_data.='</td>';
									}
								}
								
							?>
							<tr class="p-b-o">
							   
								<td width="400" rowspan="2" align="left" class="ha2 msccrow_<?php echo $con_ch;?> text-info"><strong><?php echo ucfirst($property_name);?></strong></td>
						
								<td bgcolor="#a6a6a6">P</td>
						
							   <?php echo $Mprice_data; ?>
						
							</tr>
							
							<tr class="p-b-o">
						
								<td bgcolor="#a6a6a6">A</td>
						
								<?php echo $Mavail_data; ?> 
						
							</tr>
						  
							<tr class="p-b-o msccc m_<?php echo $con_ch;?>" style="display:none;" id="ms_main_room_<?php echo $property_id.'_'.$con_ch;?>"> <td> show MS </td></tr>
							<!-- Main Room MS -->
							<tr class="p-b-o msccc s_<?php echo $con_ch;?>" style="display:none;" id="ss_main_room_<?php echo $property_id.'_'.$con_ch;?>"> <td> show SS </td></tr>
							<!-- Main Room SS -->
							<tr class="p-b-o msccc c1_<?php echo $con_ch;?>" style="display:none;" id="cta_main_room_<?php echo $property_id.'_'.$con_ch;?>"> <td> show CTA </td></tr>
							<!-- Main Room CTA -->
							<tr class="p-b-o msccc c2_<?php echo $con_ch;?>" style="display:none;" id="ctd_main_room_<?php echo $property_id.'_'.$con_ch;?>"> <td> show CTD </td></tr>
							<!-- Main Room CTD -->
							<?php 
							}
							if($pricing_type==2) 
							{
								//echo 'pricing_type'.$pricing_type;
								if($non_refund==1)
								{
									for($k=1;$k<=$members;$k++)
									{
										if($member_count < $members)
										{
											if($k%2 == 0)
											{
												$name = 'Guest Non refundable';
						
												$v = ceil($k/2);
						
												$refun = '2';
											}
											else
											{
												$name = 'Guest';
						
												$v = ceil($k/2);
						
												$refun = '1';
											}
										}
										else
										{
											$name = 'Guest';
						
											$v = $k;
						
											$refun = '1';
										}
										$sub_available = get_data(MAP,array('property_id'=>$property_id,'rate_id'=>'0','guest_count'=>$v,'refun_type'=>$refun,'channel_id'=>$con_ch))->row_array();
										if(count($sub_available)!=0)
										{
										?>
						
							<tr class="p-b-o">
						
								<td class="ha2"><span class="pull-left"><?php echo ucfirst($property_name);?> - <?php echo $v.' '.$name;?></span></td>
						
								<td bgcolor="#a6a6a6" class="w_bdr">P</td>
						
							<?php 
						
							foreach($period as $date)
						
							{
						
								$j=$date->format("d");
						
								$ss_da = $date->format("d/m/Y");
						
								if($refun=='1')
						
								{

						
										$sub_rates = get_data(RESERV,array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();
				
									
						
									if(count($sub_rates)!=0)
						
									{
						
										$sub_rate = $sub_rates['refund_amount'];
						
									}
						
								}
						
								elseif($refun=='2')
						
								{
		
										$sub_rates = get_data(RESERV,array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();


						
									if(count($sub_rates)!=0)
						
									{
						
										$sub_rate = $sub_rates['non_refund_amount'];
						
									}
						
								}
						
								/*if($j%2 == 0)
						
								{
						
									$color='tabl_even';
						
								}
						
								else
						
								{
						
									$color='tabl_add';
						
								}*/
						
								if($date->format("D")=='Sat' || $date->format("D")=='Sun')
						
								{
						
									$color='#669933';
						
								}	
						
								else
						
								{
						
									$color='#D9E4C3';
						
								}
						
								if(in_array($ss_da,$da))
						
								{
						
									if(count($sub_rates)!='0')
						
									{
						
										if($sub_rate!=0)
						
										{
						
									?>
						
											<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?><a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price"><?php echo floatval($sub_rate);?></a> <?php } else { ?> <?php echo floatval($sub_rate);?> <?php } ?></td>
						
								  <?php }
						
										else 
						
										{ ?>
						
											<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
						
								 <?php 	}
						
									}
						
									else 
						
									{ 					
								?>
								<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
						
								<?php }
						
								}
						
								else { ?> 
						
										<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
						
								<?php } 
						
								}
						
								?>
						
							  </tr>
						
							  <?php 	}
									}
								}
								else
								{
									for($k=1;$k<=$members;$k++)
									{
										if($member_count < $members)
										{
											if($k%2 == 0)
											{
												$name = 'Guest Non refundable';
						
												$v = ceil($k/2);
						
												$refun=2;
											}
											else
											{
												$name = 'Guest';
						
												$v = ceil($k/2);
						
												$refun=1;
											}
										}
										else
										{
											$name = 'Guest';
						
											$v = $k;
						
											$refun=1;
										}
										$sub_available = get_data(MAP,array('property_id'=>$property_id,'rate_id'=>'0','guest_count'=>$v,'refun_type'=>$refun,'channel_id'=>$con_ch))->row_array();
										if(count($sub_available)!=0)
										{
										?>
							<tr class="p-b-o">
								<td class="ha2"><span class="pull-left"><?php echo ucfirst($property_name);?> - <?php echo $v.' '.$name;?></span></td>
								<td bgcolor="#a6a6a6" class="w_bdr">P</td>
							<?php 
							foreach($period as $date)
							{
								$j=$date->format("d");
						
								$ss_da = $date->format("d/m/Y");
						
								if($refun=='1')
								{
									$sub_rates = get_data(RESERV,array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();
										
									if(count($sub_rates)!=0)
									{
										$sub_rate = $sub_rates['refund_amount'];
									}
								}
								elseif($refun=='2')
								{
									$sub_rates = get_data(RESERV,array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();

									if(count($sub_rates)!=0)
									{
										$sub_rate = $sub_rates['non_refund_amount'];
									}
								}
								if($date->format("D")=='Sat' || $date->format("D")=='Sun')
								{
									$color='#669933';
								}	
								else
								{
									$color='#D9E4C3';
								}
								if(in_array($ss_da,$da)) 
								{
									if(count($sub_rates)!='0')
									{
										if($sub_rate!=0)
										{
								?>
								<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?><a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price"><?php echo floatval($sub_rate);?></a><?php } else { ?> <?php echo floatval($sub_rate);?> <?php   }?></td>
								<?php	}else { ?>
								<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
								 <?php }}else { ?>
								<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
								<?php }} else { ?> 
								<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>

								<?php } }?>  
						
								</tr>       
										<?php
										}
									}
								}
							}
							else if($pricing_type==1 && $non_refund==1)
							{
								$v=1;
								$refun=2;
								$sub_available = get_data(MAP,array('property_id'=>$property_id,'rate_id'=>'0','guest_count'=>$v,'refun_type'=>$refun,'channel_id'=>$con_ch))->row_array();
								if(count($sub_available)!=0)
								{
							?>
							<tr class="p-b-o">
							<td rowspan="2" align="left" class="ha2 msccrow_<?php echo $con_ch;?>"><span class="pull-left"><?php echo ucfirst($property_name);?> - Non refundable</span></td>
							<td bgcolor="#a6a6a6" class="w_bdr">P</td>
							<?php
							foreach($period as $date)
							{
								$j=$date->format("d");
						
								$ss_da = $date->format("d/m/Y");
						
								if($refun=='1')
								{
									$sub_rates = get_data(RESERV,array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();

									if(count($sub_rates)!=0)
									{
										$sub_rate = $sub_rates['refund_amount'];
									}
								}
								elseif($refun=='2')
								{
									$sub_rates = get_data(RESERV,array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();
			
									if(count($sub_rates)!=0)
									{
										$sub_rate = $sub_rates['non_refund_amount'];
									}
								}
					
								if($date->format("D")=='Sat' || $date->format("D")=='Sun')
								{
									$color='#669933';
								}	
								else
								{
									$color='#D9E4C3';
								}
						
								if(in_array($ss_da,$da))
								{
									if(count($sub_rates)!='0')
									{
										if($sub_rate!=0)
										{
							?>
							<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?><a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price"><?php echo floatval($sub_rate);?></a><?php } else { echo floatval($sub_rate); }?></td>
							<?php } else { ?>
							<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
							<?php }}else { ?>
							<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
							<?php }} else { ?> 
							<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
							<?php } }?>  
							</tr>
						
							<tr class="p-b-o">
							<td bgcolor="#a6a6a6">A</td>
							<?php 
							foreach($period as $date)
							{
								$i=$date->format("d");
						
								$ss_da = $date->format("d/m/Y");
						
								if($date->format("D")=='Sat' || $date->format("D")=='Sun')
								{
									$color='#669933';
								}	
								else
								{
									$color='#D9E4C3';
								}
								if(in_array($ss_da,$da))
								{
									$single = get_data(RESERV,array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row();
									
									if(count($single)!='0')
									{
										if($single->availability==0 && $single->availability!='')
										{
											$color = '#FF0000';
										}
										elseif($single->stop_sell==1)
										{
											$color = '#CC99FF';
										}
										elseif($single->availability < 0)
										{
											$color	= '#F4C327';
										}
										else
										{	
											$color='#D9E4C3';
										}
							?>
							<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr"> <?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Availability"><?php if($single->availability!='') { echo ($single->availability); } else { echo 'N/A';}?> </a> <?php } else {?> <?php if($single->availability!='') { echo ($single->availability); } else { echo 'N/A';} ?> <?php } ?></td>
							<?php } else  { ?>  
							<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Availability">N/A </a><?php } else { ?> N/A <?php } ?></td>
							<?php } } else { ?>   
							<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Availability">N/A </a><?php } else { ?> N/A <?php } ?></td>
							<?php }}?> 
							</tr>
							
							<tr style="display:none;" id="ms_sub_room_<?php echo $property_id.'_'.$v.'_'.$refun.'_'.$con_ch;?>"> <td> show MS </td></tr>
							<!-- Sub Room MS -->
							<tr style="display:none;" id="ss_sub_room_<?php echo $property_id.'_'.$v.'_'.$refun.'_'.$con_ch;?>"> <td> show SS </td></tr>
							<!-- Sub Room SS -->
							<tr style="display:none;" id="cta_sub_room_<?php echo $property_id.'_'.$v.'_'.$refun.'_'.$con_ch;?>"> <td> show CTA </td></tr>
							<!-- Sub Room CTA -->
							<tr style="display:none;" id="ctd_sub_room_<?php echo $property_id.'_'.$v.'_'.$refun.'_'.$con_ch;?>"> <td> show CTD </td></tr>
							<!-- Sub Room CTD -->
							
							<?php
								}
							}
						}
						if($rate_id!="0")
						{
							$main_rate_available = get_data(MAP,array('property_id'=>$property_id,'rate_id'=>$rate_id,'guest_count'=>'0','refun_type'=>'0','channel_id'=>$con_ch))->row_array();
							if(count($main_rate_available)!='0')
							{
								$Rprice_data='';
								$Ravail_data='';
								foreach($period as $date)
								{
									$ss_da = $date->format("d/m/Y");
							
									if($date->format("D")=='Sat' || $date->format("D")=='Sun')
									{
										$color='#669933';
									}	
									else
									{
										$color='#D9E4C3';
									}
									if(in_array($ss_da,$da))
									{
										$single = get_data('room_rate_types_base',array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'rate_types_id'=>$rate_id,'separate_date'=>$ss_da),'availability,stop_sell,price')->row();

										if(count($single)!='0')
										{
											if($single->availability==0 || $single->stop_sell==1)
											{
												$color = '#FF0000';
											}
											if($single->availability==0)
											{
												$color = '#FF0000';
											}
											elseif($single->stop_sell==1)
											{
												$color = '#CC99FF';
											}
											elseif($single->availability < 0)
											{
												$color	= '#F4C327';
											}
											
											$Rprice_data.='<td>';
											
											if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
											{
												$Rprice_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price"  data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'~'.$rate_id.'" data-url="'.lang_url().'inventory/inline_edit_channel_type" data-title="Change Price">'.floatval($single->price).'</a>';
											}
											else
											{
												$Rprice_data.=floatval($single->price);
											}
											
											$Rprice_data.='</td>';
											
											$Ravail_data.='<td width="28" bgcolor="'.$color.'" class="w_bdr">';
											
											if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
											{
												$Ravail_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'~'.$rate_id.'" data-url="'.lang_url().'inventory/inline_edit_channel_type" data-title="Change Availability">'.$single->availability.'</a>';
											}
											else
											{
												$Ravail_data.=$single->availability;
											}
											
											$Ravail_data.='</td>';
										}
										else  
										{
											$Rprice_data.='<td>';
											
											if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
											{
												$Rprice_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price"  data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'~'.$rate_id.'" data-url="'.lang_url().'inventory/inline_edit_channel_type" data-title="Change Price"> N/A </a>';
											}
											else
											{
												$Rprice_data.='N/A';
											}
											
											$Rprice_data.='</td>';
											
											$Ravail_data.='<td width="28" bgcolor="'.$color.'" class="w_bdr">';
											
											if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
											{
												$Ravail_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'~'.$rate_id.'" data-url="'.lang_url().'inventory/inline_edit_channel_type" data-title="Change Availability"> N/A </a>';
											} 
											else
											{ 
												$Ravail_data.='N/A'; 
											} 
											
											$Ravail_data.='</td>';
										}
									}
									else
									{
										$Rprice_data.='<td>';
										
										if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
										{
											$Rprice_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price"  data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'~'.$rate_id.'?>" data-url="'.lang_url().'inventory/inline_edit_channel_type" data-title="Change Price"> N/A </a>';
										} 
										else 
										{
											$Rprice_data.='N/A';
										}
										
										$Rprice_data.='</td>';
										
										$Ravail_data.='<td width="28" bgcolor="'.$color.'" class="w_bdr">';
										if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
										{
											$Ravail_data.='<a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="'.$ss_da.'-'.$property_id.'~'.$con_ch.'~'.$rate_id.'" data-url="'.lang_url().'inventory/inline_edit_channel_type" data-title="Change Availability"> N/A </a>';
										}
										else
										{
											$Ravail_data.='N/A';
										} 
										
										$Ravail_data.='</td>';
									}
								}									
								
						?>
						<tr class="p-b-o">
							<td width="400" rowspan="2" align="left" class="ha2 msccrow_<?php echo $con_ch;?> rate_clr"><strong><?php echo ucfirst($rate_name);?></strong></td>
							<td bgcolor="#a6a6a6">P</td>
						<?php echo $Rprice_data; ?>
						</tr>
   					    <tr class="p-b-o">
							<td bgcolor="#a6a6a6">A</td>
							<?php echo $Ravail_data; ?> 
						</tr>
						
						<tr class="p-b-o msccc m_<?php echo $con_ch;?>" style="display:none;" id="ms_main_rate_<?php echo $property_id.'_'.$rate_id.'_'.$con_ch;?>"> <td> show MS </td></tr>
						<!-- Main Rate MS -->
						<tr class="p-b-o msccc s_<?php echo $con_ch;?>" style="display:none;" id="ss_main_rate_<?php echo $property_id.'_'.$rate_id.'_'.$con_ch;?>"> <td> show SS </td></tr>
						<!-- Main Rate SS -->
						<tr class="p-b-o msccc c1_<?php echo $con_ch;?>" style="display:none;" id="cta_main_rate_<?php echo $property_id.'_'.$rate_id.'_'.$con_ch;?>"> <td> show CTA </td></tr>
						<!-- Main Rate CTA -->
						<tr class="p-b-o msccc c2_<?php echo $con_ch;?>" style="display:none;" id="ctd_main_rate_<?php echo $property_id.'_'.$rate_id.'_'.$con_ch;?>"> <td> show CTD </td></tr>
						<!-- Main Rate CTD -->

					   <?php 
							}
						if($pricing_type==2) 
						{
							if($non_refund==1)
							{
								for($k=1;$k<=$members;$k++)
								{
									if($member_count < $members)
									{
										if($k%2 == 0)
										{
											$name = 'Guest Non refundable';

											$v = ceil($k/2);
					
											$refun = '2';
										}
										else
										{
											$name = 'Guest';
					
											$v = ceil($k/2);
					
											$refun = '1';
										}
									}
									else
									{
										$name = 'Guest';
					
										$v = $k;
					
										$refun = '1';
									}
					
									if($rate_id!="0")
									{
										$sub_rate_available = get_data(MAP,array('property_id'=>$property_id,'rate_id'=>$rate_id,'guest_count'=>$v,'refun_type'=>$refun,'channel_id'=>$con_ch))->row_array();
										if(count($sub_rate_available)!='0')
										{
					
						?>
						<tr class="p-b-o">
							<td class="ha2"><span class="pull-left"><?php echo ucfirst($rate_name);?> - <?php echo $v.' '.$name;?></span></td>
							<td bgcolor="#a6a6a6" class="w_bdr">P</td>
						<?php 
						foreach($period as $date)
						{
							$j=$date->format("d");

							$ss_da = $date->format("d/m/Y");
					
							if($refun=='1')
							{
								$sub_rates = get_data('room_rate_types_additional',array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'rate_types_id'=>$rate_id,'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();

								if(count($sub_rates)!=0)
								{
									$sub_rate = $sub_rates['refund_amount'];
								}
							}
							elseif($refun=='2')
							{
								$sub_rates = get_data('room_rate_types_additional',array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'rate_types_id'=>$rate_id,'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();
								
								if(count($sub_rates)!=0)
								{
									$sub_rate = $sub_rates['non_refund_amount'];
								}
							}
							if($date->format("D")=='Sat' || $date->format("D")=='Sun')
							{
								$color='#669933';
							}	
							else
							{
								$color='#D9E4C3';
							}
							if(in_array($ss_da,$da))
							{
								if(count($sub_rates)!='0')
								{
									if($sub_rate!=0)
									{
								?>
									<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?><a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price"><?php echo floatval($sub_rate);?></a> <?php } else { ?> <?php echo floatval($sub_rate);?> <?php } ?></td>
							 <?php }else{ ?>
									<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>"  data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
							<?php	}}else{ ?>
									<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>"  data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
							<?php }}else { ?> 
									<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>"  data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
							<?php } 
								}
							?>
						</tr>
						  <?php } } }}else{
					
								for($k=1;$k<=$members;$k++)
								{
									if($member_count < $members)
									{
										if($k%2 == 0)
										{
											$name = 'Guest Non refundable';
					
											$v = ceil($k/2);
					
											$refun=2;
										}
										else
										{
											$name = 'Guest';
					
											$v = ceil($k/2);
					
											$refun=1;
										}
									}
									else
									{
										$name = 'Guest';
					
										$v = $k;
					
										$refun=1;
									}
									if($rate_id!="0")
									{
										$sub_rate_available = get_data(MAP,array('property_id'=>$property_id,'rate_id'=>$rate_id,'guest_count'=>$v,'refun_type'=>$refun,'channel_id'=>$con_ch))->row_array();
										if(count($sub_rate_available)!='0')
										{
									?>
										<tr class="p-b-o">
											<td class="ha2"><span class="pull-left"><?php echo ucfirst($rate_name);?> - <?php echo $v.' '.$name;?></span></td>
											<td bgcolor="#a6a6a6" class="w_bdr">P</td>
										<?php 
										foreach($period as $date)
										{
											$j=$date->format("d");
									
											$ss_da = $date->format("d/m/Y");
									
											if($refun=='1')
											{
												$sub_rates = get_data('room_rate_types_additional',array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();

												if(count($sub_rates)!=0)
												{
													$sub_rate = $sub_rates['refund_amount'];
												}
											}
											elseif($refun=='2')
											{
												$sub_rates = get_data('room_rate_types_additional',array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'rate_types_id'=>$rate_id,'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();

												if(count($sub_rates)!=0)
												{
													$sub_rate = $sub_rates['non_refund_amount'];
												}
											}

											if($date->format("D")=='Sat' || $date->format("D")=='Sun')
											{
												$color='#669933';
											}	
											else
											{
												$color='#D9E4C3';
											}
											if(in_array($ss_da,$da)) 
											{
												if(count($sub_rates)!='0')
												{
													if($sub_rate!=0)
													{
										?>
											<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?><a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price"><?php echo floatval($sub_rate);?></a><?php } else { ?> <?php echo floatval($sub_rate);?> <?php   }?></td>
											<?php } else { ?>
											<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
											<?php }}else { ?>
											<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
											<?php }} else { ?> 
											<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
											<?php } }?>  
											</tr>       
									<?php
										}
									}
								}
							}
						}
						else if($pricing_type==1 && $non_refund==1)
						{
							$v=1;
							$refun=2;
							$sub_rate_available = get_data(MAP,array('property_id'=>$property_id,'rate_id'=>$rate_id,'guest_count'=>$v,'refun_type'=>$refun,'channel_id'=>$con_ch))->row_array();
							if(count($sub_rate_available)!='0')
							{
						?>
						<tr class="p-b-o">
						<td rowspan="2" align="left" class="ha2 msccrow_<?php echo $con_ch;?>"><span class="pull-left"><?php echo ucfirst($property_name);?> - Non refundable</span></td>
						<td bgcolor="#a6a6a6" class="w_bdr">P</td>
						<?php
						foreach($period as $date)
						{
							$j=$date->format("d");
					
							$ss_da = $date->format("d/m/Y");
					
							if($refun=='1')
							{
								$sub_rates = get_data('room_rate_types_additional',array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();
			
								if(count($sub_rates)!=0)
								{
									$sub_rate = $sub_rates['refund_amount'];
								}
							}
							elseif($refun=='2')
							{
								$sub_rates = get_data('room_rate_types_additional',array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'rate_types_id'=>$rate_id,'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row_array();
								
								if(count($sub_rates)!=0)
								{
									$sub_rate = $sub_rates['non_refund_amount'];
								}
							}

							if($date->format("D")=='Sat' || $date->format("D")=='Sun')
							{
								$color='#669933';
							}	
							else
							{
								$color='#D9E4C3';
							}
							if(in_array($ss_da,$da))
							{
								//$single = get_data(TBL_UPDATE,array('owner_id'=>user_id(),'hotel_id'=>hotel_id(),'room_id'=>$property_id,'separate_date'=>$ss_da))->row();
								if(count($sub_rates)!='0')
								{
									if($sub_rate!=0)
									{
						?>
						<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?><a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price"><?php echo floatval($sub_rate);?></a><?php } else { echo floatval($sub_rate); }?></td>
						<?php } else { ?>
						<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
						<?php }}else { ?>
						<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
						<?php }} else { ?> 
						<td bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_price" data-type="number" data-name="price" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Price">N/A </a><?php } else { ?> N/A <?php } ?></td>
						<?php } }?>  
						</tr>
					
						<tr class="p-b-o">
						<td bgcolor="#a6a6a6">A</td>
						<?php 
						foreach($period as $date)
						{
							$i=$date->format("d");
					
							$ss_da = $date->format("d/m/Y");

							if($date->format("D")=='Sat' || $date->format("D")=='Sun')
							{
								$color='#669933';
							}
							else
							{
								$color='#D9E4C3';
							}
							if(in_array($ss_da,$da))
							{
								if(user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
								{
									$single = get_data('room_rate_types_additional',array('individual_channel_id'=>$con_ch,'owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'rate_types_id'=>$rate_id,'room_id'=>$property_id,'separate_date'=>$ss_da,'guest_count'=>$v,'refun_type'=>$refun))->row();
								}
								
								if(count($single)!='0')
								{
									if($single->availability==0 && $single->availability!='')
									{
										$color = '#FF0000';
									}
									elseif($single->stop_sell==1)
									{
										$color = '#CC99FF';
									}
									elseif($single->availability < 0)
									{
										$color	= '#F4C327';
									}
									else
									{	
										$color='#D9E4C3';
									}
						?>
						<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr"> <?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Availability"><?php if($single->availability!='') { echo ($single->availability); } else { echo 'N/A';}?> </a> <?php } else {?> <?php if($single->availability!='') { echo ($single->availability); } else { echo 'N/A';} ?> <?php } ?></td>
						<?php } else  { ?>  
						<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Availability">N/A </a><?php } else { ?> N/A <?php } ?></td>
						<?php } } else { ?>   
						<td width="28" bgcolor="<?php echo $color;?>" class="w_bdr"><?php if(date('Y/m/d',strtotime(str_replace('/','-',$ss_da)))>=date('Y/m/d') && user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1'){?> <a href="javascript:;" id="inline_username" class="inline_username inline_availability" data-type="number" data-name="availability" data-pk="<?php echo $ss_da.'-'.$property_id.','.$v.'~'.$refun.'|'.$con_ch.'|'.$rate_id;?>" data-url="<?php echo lang_url()?>inventory/inline_edit_guest_channel_type" data-title="Change Availability">N/A </a><?php } else { ?> N/A <?php } ?></td>
						<?php }}?> 
						</tr>
						
						<tr style="display:none;" id="ms_sub_rate_<?php echo $property_id.'_'.$rate_id.'_'.$v.'_'.$refun.'_'.$con_ch;?>"> <td> show MS </td></tr>
						<!-- Sub Rate MS -->
						<tr style="display:none;" id="ss_sub_rate_<?php echo $property_id.'_'.$rate_id.'_'.$v.'_'.$refun.'_'.$con_ch;?>"> <td> show SS </td></tr>
						<!-- Sub Rate SS -->
						<tr style="display:none;" id="cta_sub_rate_<?php echo $property_id.'_'.$rate_id.'_'.$v.'_'.$refun.'_'.$con_ch;?>"> <td> show CTA </td></tr>
						<!-- Sub Rate CTA -->
						<tr style="display:none;" id="ctd_sub_rate_<?php echo $property_id.'_'.$rate_id.'_'.$v.'_'.$refun.'_'.$con_ch;?>"> <td> show CTD </td></tr>
						<!-- Sub Rate CTD -->
						  
					 <?php
							}
						} 
					}
				}
			}
		}
	?>
		</tbody>
		
		</table>
		<?php if(user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || admin_id()!='' && admin_type()=='1')
		{
			?>
		<div class=" pull-right"> 
		
		<!--<input type="button" class="btn btn-success" value="Update" data-toggle="modal" data-target="#channelsModal"/> -->
		
		<input type="submit" class="btn btn-success" value="Update"/> 
		
		<input type="reset" class="btn btn-danger" value="Reset"/>
		
		</div>
        <?php } ?>
		
		</div>
		
		</div>
		
		</form>
</div>

</div>

</div>

</div>

</div>

<?php }} } else {  ?> <div class="bb1">

<br>

<div class="reser"><center><i class="fa fa-globe"></i></center></div>

<h2 class="text-center">You don't have any online channels configured for this room type yet</h2>

<p class="pad-top-20 text-center">You can sell your rooms from hundreds of online sales channels</p>

<br>

<div class="res-p"><center><a class="btn btn-primary" href="<?php echo lang_url();?>mapping/connectlist"><i class="fa fa-plus"></i>  Map To Channel</a></center></div>

</div> <?php } }  ?>

<script>
//$('.inline_username').editable();
$('.inline_price').editable({
    step: 'any',
});

$('.inline_availability').editable();

$('.inline_minimum').editable();

var offset = $('.navbar').height();
$("html:not(.legacy) table.table_stricky").stickyTableHeaders({fixedOffset: offset});
</script>