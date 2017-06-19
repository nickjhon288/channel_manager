<?php 
ini_set('memory_limit', '-1');
ini_set('display_errors','1');
class Reservation_model extends CI_Model
{
    public function __construct()
    {       
        parent::__construct();
        //date_default_timezone_set('Asia/kolkata');
    }
    function count_room($property_id)
    {
        $count = $this->db->select('property_id')->from(TBL_PROPERTY)->
        where(array('property_type'=>$property_id))->count_all_results();
        return $count;
    }   

    function mailsettings()
    {     
        $this->load->library('email');  
        $config['wrapchars'] = 76;  // Character count to wrap at.
        $config['priority'] = 1;  // Character count to wrap at.
        $config['mailtype'] = 'html'; // text or html Type of mail. If you send HTML email you must send it as a complete web page. Make sure you don't have any relative links or relative image paths otherwise they will not work.
        $config['charset'] = 'utf-8'; // Character set (utf-8, iso-8859-1, etc.).
        $this->email->initialize($config);          
    } 
    function save($product, $options=false, $categories=false)
    {       
        $available = get_data(TBL_UPDATE,array('room_id'=>($product['room_id'])))->row_array();
        $start = explode('/',$product['start_date']);
        $end = explode('/',$product['end_date']);
        //$da=array(); 
        //for($ss=$start[0]; $ss<=$end[0]; $ss++)
        //{
            //$da[].=$ss;   
        //} 
        if(count($available)!=0)
        {
            if($product['availability']!='')
            {
                $product['availability'] =$product['availability'];
            }
            else
            {
                $product['availability'] =$available['availability'];
            }
            if($product['price']!='')
            {
                $product['price'] =$product['price'];
                $products['price'] =$product['price'];
            }
            else
            {
                $product['price'] =$available['price'];
                $products['price'] =$available['price'];
            }
            if($product['minimum_stay']!='')
            {
                $product['minimum_stay'] =$product['minimum_stay'];
            }
            else
            {
                $product['minimum_stay'] =$available['minimum_stay'];
            }
            if(isset($product['cta'])!='')
            {
                $product['cta'] =$product['cta'];
            }
            elseif(isset($product['cta'])=='')
            {
                //$product['cta'] =$available['cta'];
                $product['cta'] ='0';
            }
            if(isset($product['ctd'])!='')
            {
                $product['ctd'] =$product['ctd'];
            }
            elseif(isset($product['ctd'])=='')
            {
                //$product['ctd'] =$available['ctd'];
                $product['ctd'] ='0';
            }
            if(isset($product['stop_sell'])!='')
            {
                $product['stop_sell'] =$product['stop_sell'];
            }
            elseif(isset($product['stop_sell'])=='')
            {
                //$product['stop_sell'] =$available['stop_sell'];
                $product['stop_sell'] ='0';
            }
            for($ss=$start[0]; $ss<=$end[0]; $ss++)
            {
                $product['separate_date'] = $ss;
                $this->db->where('room_id', $product['room_id']);
                $this->db->where('separate_date', $ss);
                $this->db->update(TBL_UPDATE, $product);
            }
            
            $this->db->where('property_id', $product['room_id']);
            $this->db->update(TBL_PROPERTY, $products);
            
            
        } 
        else
        {
            if($product['availability']!='')
            {
                $product['availability'] =$product['availability'];
            }
            else
            {
                $product['availability'] =1;
            }
            if($product['price']!='')
            {
                $product['price'] =$product['price'];
            }
            else
            {
                $product['price'] =100;
            }
            for($ss=$start[0]; $ss<=$end[0]; $ss++)
            {
                $product['separate_date'] = $ss;
                $this->db->insert(TBL_UPDATE, $product);
            }
        }


        //loop through the product options and add them to the db
    
        
        
        //return the product id
        return true;

    }
    
    
    function getallexist_subcatcntbymaincat($catid)
    {  
      // AMENITIES_TYPE   AMENITIES 
        
          $this->db->where('type_id',$catid);  
          $query=$this->db->get("room_amenities");
          if($query->num_rows()>1)
          {
               $cnt=$query->num_rows();
                return $cnt; 
               
          }
          else
          {
                return $cnt=0; 
          }
    }
    
    function payment_success_mail($user_id='',$payment_id)
    {
            
        $get_email_info     =   get_mail_template('8');
        
        $email_subject1= $get_email_info['subject'];

        $email_content1= $get_email_info['message']; 
        
        $plan_name = get_data(TBL_PLAN,array('id'=>insep_decode($payment_id)))->row()->plan_name;

        $row=get_data(USERS,array('user_id'=>current_user_type()));

        $aa=array(                   

        '###USERNAME###'=>$row->fname.' '.$row->lname,      

        '###id###'=>$row->transaction_id, 

        '###TYPE###'=>$plan_name, 

        '###Validity###'=>$row->plan_to, 

        '###status###'=>'Success',       

        ); 

    $email_content=strtr($email_content1,$aa);
    
    $admin_mail = get_data('admin_profile',array('pid'=>'1'))->row(); 

    $this->mailsettings();   

    $this->email->from($admin_mail->email,$email_content_title);

    $this->email->to($row->user_emailid); 

    $this->email->subject($email_subject1); 

    $this->email->message($email_content);

    $this->email->send();

    }
    
    function updatetransaction($transaction_id)
    {
        $payment_id = insep_encode($this->session->userdata('pay_type'));
        $plan_details = get_data(TBL_PLAN,array('plan_id'=>insep_decode($payment_id)))->row();
        $plan_duration = $plan_details->plan_types;
        $data['transaction_id']= $transaction_id;
        $data['plan_id']  = insep_decode($payment_id);
        $data['plan_price'] = $plan_details->plan_price;
        $data['payment_method'] = 'CRIDIT CARD';
        
                if($plan_duration=='Month')
                {
                    $plan = 1;
                    $plan_du = 'months';
                }
                elseif($plan_duration=='Year')
                {
                    $plan = 1;
                    $plan_du = 'years';
                }
                $data['plan_from'] = date('Y-m-d');
                $data['plan_to'] = date("Y-m-d", strtotime("+$plan $plan_du"));
                $data['subscribe_status'] = '1';
                if(update_data(TBL_USERS,$data,array('user_id'=>current_user_type())))
                {
                    return true;
                }
                else
                {
                    return false;
                }
    }
    
    function getallexist_subcatcntallcat($catid,$start_catlimit,$end_catlimit)
    {
        
           $this->db->where('type_id',$catid);   
            $this->db->limit($end_catlimit,$start_catlimit);   
          $query=$this->db->get("room_amenities");
          if($query->num_rows()>1)
          {
               $cnt=$query->result_array();  
                return $cnt; 
               
          }
          else
          {
                return $cnt=0; 
          }
          
    }
    
    function count_room_photos($property_id)
    {
        $count = $this->db->select('photo_id')->from(TBL_PHOTO)->
        where(array('room_id'=>insep_decode($property_id)))->count_all_results();
        return $count;
    }
    
    function add_photo($filename)
    {
        $ho_id = get_data(TBL_PHOTO,array('room_id'=>insep_decode($this->input->post('hotel_id'))))->row_array();
        if(count($ho_id)!=0)
        {
            $value = get_data(TBL_PHOTO,array('room_id'=>insep_decode($this->input->post('hotel_id'))))->row()->photo_names;
            $udata['room_id']=insep_decode($this->input->post('hotel_id'));
            $udata['photo_names'] = $value.','.$filename;
            if(update_data(TBL_PHOTO,$udata,array('room_id'=>insep_decode($this->input->post('hotel_id')))))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            $idata['room_id']=insep_decode($this->input->post('hotel_id'));
            $idata['photo_names'] = $filename;
            if(insert_data(TBL_PHOTO,$idata))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
    }
    
    function connected_channel($connected_channel)
    {
        $connect= explode(',',$connected_channel);
        $this->db->where_in('channel_id',$connect);
        $query = $this->db->get(TBL_CHANNEL);
        if($query)
        {
            return $query->result_array();
        }
        else
        {
            return false;
        }
    }
    
    function count_rate_types($property_id)
    {
        $count = $this->db->select('rate_type_id')->from(RATE_TYPES)->
        where(array('room_id'=>insep_decode($property_id)))->count_all_results();
        return $count;
    }
    
        // sharmila...
    
    function get_currency_name(){   
    $this->db->order_by('currency_name','asc'); 
    $sel = $this->db->get('currency');
    if($sel->num_rows>0){       
        return $sel->result();              
    }
        return false;
}
    function current_user(){
        $this->db->where('user_id',$this->session->userdata('per_user_id'));
        $res = $this->db->get('user_details');
        if($res -> num_rows == 1){
            return $res->row();
        }



            return false;
    }
    function get_roomtype($id){
        $this->db->where('room_id',$id);
        $this->db->where('status','1');
        $sel = $this->db->get('room_type'); 
        if($sel->num_rows() >0){
            return $sel->result();
        }
            return false;
    }
    
    function get_room_list($channel_name='')
    {
        $merge = array();
        /*if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $this->db->where('user_id',user_id());
        }
        else if(user_type()=='2')
        {
            $this->db->where('user_id',owner_id());
        }
        else if(admin_id()!='' && admin_type()=='1')
        {
            $this->db->where('user_id',user_id());
        }*/
        if($channel_name=='' || $channel_name=='Hoteratus')
        {
			$this->db->select('reservation_id,reservation_code,status,guest_name,room_id,channel_id,start_date,end_date,booking_date,currency_id,price,num_nights,num_rooms,created_date as current_date_time');
			$this->db->order_by('reservation_id','desc'); 
			$this->db->where('user_id',current_user_type());
            $this->db->where('hotel_id',hotel_id());
            $this->db->where('channel_id',0);
            $res = $this->db->get('manage_reservation');
           // echo $this->db->last_query();
            /* echo '<pre>';
            print_r($res->result());die; */
            if($res->num_rows >0)
            {
                $bulk_resrevation = $res->result();
            }
            else
            {
                $bulk_resrevation = array();
            }
			$merge = array_merge($merge,$bulk_resrevation);
        }
        
        if($channel_name!='Hoteratus')
        {
			$ch_details = get_data(CONNECT,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'channel_id'=>2))->row();
			$this->db->select('M.channel_name');
			$this->db->join('manage_channel as M','C.channel_id=M.channel_id');
			$this->db->where(array('user_id'=>current_user_type(),'hotel_id'=>hotel_id()));
            $channels = $this->db->get('user_connect_channel as C')->result();
            foreach ($channels as $channel)
			{
				$channel_name = strtoupper($channel->channel_name);
				if($channel_name == "BOOKING.COM")
				{
					$channel_name = "BOOKING";
				}
				if($channel_name == "BOOKONLINENOW")
				{
					$channel_name = "BN0W";
				}
				if($this->db->table_exists('import_reservation_'.$channel_name))
				{
					if($channel_name == "EXPEDIA")
					{
						$this->db->order_by('import_reserv_id','desc'); 
						$this->db->where('user_id',current_user_type());
						$this->db->where('hotel_id',hotel_id());
						$data = $this->db->get('import_reservation_'.$channel_name)->result();
						if($data)
						{
						$res = array();
						foreach($data as $val){
							//print_r($val);
							if($val->type == "Cancel"){
								$status = "Canceled";
							}else{
								$status =$val->type;
							}
                            $roomtypeid = $val->roomTypeID;
                            $rateplanid = $val->ratePlanID;
                            $roomdetails = getExpediaRoom($roomtypeid,$rateplanid,current_user_type(),hotel_id());
                            if(count($roomdetails) !=0)
                            {
                                $roomtypeid = $roomdetails['roomtypeId'];
                                $rateplanid = $roomdetails['rateplanid'];
                            }
                            $room_id = @get_data(TBL_PROPERTY,array('property_id'=>get_data(MAP,array('channel_id'=>$val->channel_id,'import_mapping_id'=>get_data('import_mapping',array('roomtype_id'=>$roomtypeid,'rate_type_id'=>$rateplanid,'user_id'=>current_user_type(),'hotel_id'=>hotel_id()))->row()->map_id))->row()->property_id))->row()->property_id;
                            if(!$room_id)
                            {
                                $room_id = @get_data(TBL_PROPERTY,array('property_id'=>get_data(MAP,array('channel_id'=>$val->channel_id,'import_mapping_id'=>get_data('import_mapping',array('roomtype_id'=>$roomtypeid,'rateplan_id'=>$rateplanid,'user_id'=>current_user_type(),'hotel_id'=>hotel_id()))->row()->map_id))->row()->property_id))->row()->property_id;
                            }
							$checkin=date('Y/m/d',strtotime($val->arrival));
							$checkout=date('Y/m/d',strtotime($val->departure));
							$nig =_datebetween($checkin,$checkout);
							$res[] = (object)array(
								'reservation_id' => $val->import_reserv_id,
								'reservation_code' => $val->booking_id,
								'status' => $status,
								'guest_name' => $val->givenName.' '.$val->middleName.' '.$val->surname,
								'room_id' => $room_id,
								'channel_id' => $val->channel_id,
								'start_date' => $val->arrival,
								'end_date' => $val->departure,
								'booking_date' =>$val->created_time,
								'currency_id' => $val->currency,
								'price' => $val->amountAfterTaxes,
								'num_nights' => $nig, 
								'current_date_time'=> $val->current_date_time,
							);
	
						}
						$merge = array_merge($merge,$res);
						
						}
						/*else
						{	
							$res[] = (object)array();
							$merge=array_merge($bulk_resrevation,$res);
						}*/
						/*echo '<pre>';
						print_r($bulk_resrevation);
						print_r($merge);*/
					}
					else if($channel_name == "RECONLINE")
					{
						$this->db->order_by('import_reserv_id','desc'); 
						$this->db->where('user_id',current_user_type());
						$this->db->where('hotel_id',hotel_id());
						$data = $this->db->get('import_reservation_'.$channel_name)->result();
						//echo $this->db->last_query();
						if($data)
						{
						$reco = array();
						foreach($data as $val){
							//print_r($val);
							if($val->STATUS == 11){
								$status = "Reserved";
							}else if($val->STATUS == 12){
								$status = "Modify";
							}else if($val->STATUS == 13){
								$status = "Confirmed";
							}
							$room_id = get_data(MAP,array('channel_id'=>$val->channel_id,'import_mapping_id'=>get_data(IM_RECO,array('CODE'=>$val->ROOMCODE,'user_id' => current_user_type(),'hotel_id'=>hotel_id()))->row()->re_id))->row()->property_id;
							$checkin=date('Y/m/d',strtotime($val->CHECKIN));
							$checkout=date('Y/m/d',strtotime($val->CHECKOUT));
							$nig =_datebetween($checkin,$checkout);
							$reco[] = (object)array(
								'reservation_id' => $val->import_reserv_id,
								'reservation_code' => $val->IDRSV,
								'status' => $status,
								'guest_name' => $val->FIRSTNAME,
								'room_id' => $room_id,
								'channel_id' => $val->channel_id,
								'start_date' => $val->CHECKIN,
								'end_date' => $val->CHECKOUT,
								'booking_date' =>$val->RSVCREATE,
								'currency_id' => $val->CURRENCY,
								'price' => $val->REVENUE,
								'num_nights' => $nig,
								'current_date_time' => $val->current_date_time,
								
							);
	
						}
						$merge = array_merge($merge,$reco);
						}
						/*else
						{
							$reco[] = (object)array();
							$merge=array_merge($bulk_resrevation,$reco);
						}*/
					}
					else if($channel_name == "GTA")
					{
						$this->db->order_by('import_reserv_id','desc'); 
						$this->db->where('user_id',current_user_type());
						$this->db->where('hotel_id',hotel_id());
						$data = $this->db->get('import_reservation_'.$channel_name)->result();
						//echo $this->db->last_query();
						if($data)
						{
							$gta = array();
							foreach($data as $val){
							//print_r($val);
							if($val->status == "Confirmed"){
								$status = "Confirmed";
							}else if($val->status == "Cancelled"){
								$status = "Canceled";
							}
							else {
								$status = "Modify";
							}
							$gta_id = @get_data(IM_GTA,array('ID'=>$val->room_id,'rateplan_id'=>$val->rateplanid,'user_id' => current_user_type(),'hotel_id'=>hotel_id()))->row()->GTA_id;
							$gtaid = @get_data(MAP,array('channel_id'=>$val->channel_id,'import_mapping_id'=>$gta_id));
							if($gtaid->num_rows != 0){
								$room_id = $gtaid->row()->property_id;
							}else{
								$room_id = 0;
							}
							$checkin=date('Y/m/d',strtotime($val->arrdate));    
							$checkout=date('Y/m/d',strtotime($val->depdate));
							$nig =_datebetween($checkin,$checkout);
							$gta[] = (object)array(
								'reservation_id' => $val->import_reserv_id,
								'reservation_code' => $val->booking_id,
								'status' => $status,
								'guest_name' => $val->leadname,
								'room_id' => $room_id,
								'channel_id' => $val->channel_id,
								'start_date' => $val->arrdate,
								'end_date' => $val->depdate,
								'booking_date' =>$val->modifieddate,
								'currency_id' => $val->currencycode,
								'price' => $val->totalroomcost,
								'num_nights' => $nig,
								'current_date_time' => $val->current_date_time,
							);
	
						}
							$merge = array_merge($merge,$gta);
						}
						/*else
						{
							$gta[] = (object)array();
							$merge=array_merge($bulk_resrevation,$gta);
						}*/
					}
					else if($channel_name == "HOTELBEDS")
					{
						$this->db->order_by('import_reserv_id','desc'); 
						$this->db->where('user_id',current_user_type());
						$this->db->where('hotel_id',hotel_id());
						$data = $this->db->get('import_reservation_'.$channel_name)->result();
						//echo $this->db->last_query();
						if($data)
						{
							$gta = array();
							foreach($data as $val)
							{
								//print_r($val);
								if($val->RoomStatus == "BOOKING")
								{
									$status = "Confirmed";
								}
								else if($val->RoomStatus == "CANCELED")
								{
									$status = "Canceled";
								}
								else if($val->RoomStatus == "MODIFIED")
								{
									$status = "Modify";
								}
									/* $htb_id = get_data(IM_HOTELBEDS_ROOMS,array('contract_name'=>$val->Contract_Name,'contract_code'=>$val->IncomingOffice,'characterstics' => $val->CharacteristicCode,'roomname' => $val->Room_code,'user_id' => user_id(),'hotel_id' => hotel_id())); */
												
									$htb_id = $this->db->query("SELECT map_id, TRIM(TRAILING '-' FROM  REPLACE(roomname,SUBSTRING_INDEX(roomname,'-',-1),'') ) as roomnames, TRIM(TRAILING '-' FROM  REPLACE(characterstics,SUBSTRING_INDEX(characterstics,'-',-1),'') ) as charactersticss FROM `".IM_HOTELBEDS_ROOMS."` where contract_name='".$val->Contract_Name."' and contract_code='".$val->IncomingOffice."' and sequence='".$val->Contract_Code."' and user_id='".current_user_type()."' and hotel_id='".hotel_id()."' having roomnames ='".$val->Room_code."' AND charactersticss ='".$val->CharacteristicCode."'");
						
									//echo $this->db->last_query();
									
									if($htb_id->num_rows != 0)
									{
										$htb_id = $htb_id->row()->map_id; 
										$htbid = get_data(MAP,array('channel_id'=>$val->channel_id,'import_mapping_id'=>$htb_id));
										if($htbid->num_rows != 0)
										{
											$room_id = $htbid->row()->property_id;
										}
										else
										{
											$room_id = 0;
										}
									}
									else
									{
										$room_id = 0;
									}
									$checkin=date('Y/m/d',strtotime($val->DateFrom));   
									$checkout=date('Y/m/d',strtotime($val->DateTo));
									$nig =_datebetween($checkin,$checkout);
									$Currency = explode(',',$val->Currency); 
									$htb[] = (object)array(
															'reservation_id' => $val->import_reserv_id,
															'reservation_code' => $val->RefNumber,
															'status' => $status,
															'guest_name' => $val->Holder,
															'room_id' => $room_id,
															'channel_id' => $val->channel_id,
															'start_date' => $val->DateFrom,
															'end_date' => $val->DateTo,
															'booking_date' =>$val->CreationDate,
															'currency_id' => $Currency[0],
															'price' => $val->TAmount,
															'num_nights' => $nig,
															'current_date_time' => $val->current_date_time,
														);
							}
							
							$merge = array_merge($merge,$htb);
						}
						/*else
						{
							$htb[] = (object)array();
							$merge=array_merge($bulk_resrevation,$htb);
						}*/
					}
					else if($channel_name == "BOOKING" && ( $ch_details->xml_type == 2 || $ch_details->xml_type == 1 ))
					{
						$this->db->order_by('import_reserv_id','desc'); 
						$this->db->where('user_id',current_user_type());
						$this->db->where('hotel_hotel_id',hotel_id());
						$data = $this->db->get('import_reservation_BOOKING')->result();
						if($data)
						{
							$bkg = array();
							foreach($data as $val){
							//print_r($val);
								if($val->status == "new"){
									$status = "Confirmed";
								}else if($val->status == "modified"){
									$status = "Modify";
								}else if($val->status == "cancelled"){
									$status = "Canceled";
								}
								$bk_details = booking_hotel_id();
								$bkg_rooms = get_data("import_reservation_BOOKING_ROOMS",array('reservation_id'=>$val->id, 'user_id'=>current_user_type(), 'hotel_hotel_id'=>hotel_id(),'hotel_id'=>$bk_details))->result_array();
	
								foreach ($bkg_rooms as $bkroom) {
	
								  $roomname = @get_data(MAP,array('channel_id'=>$val->channel_id,'import_mapping_id'=>get_data("import_mapping_BOOKING",array('B_room_id'=>$bkroom['id'], 'B_rate_id'=>$bkroom['rate_id'],'owner_id' => current_user_type(),'hotel_id' => hotel_id()))->row()->import_mapping_id));

									if($roomname->num_rows != 0){
										$room_id = $roomname->row()->property_id;
									}else{
										$room_id = 0;
									}
									if($bkroom['guest_name']!='')
									{
										$reservation_name 	=	$bkroom['guest_name'];
									}
									else
									{
										$reservation_name	=	get_data(BOOK_RESERV,array('id'=>$bkroom['reservation_id']))->row();
										$reservation_name	=	$reservation_name->first_name.' '.$reservation_name->last_name;
									}
									$bkg[] = (object)array(
										'reservation_id' => $bkroom['room_res_id'],
                                        'res_id' => $bkroom['reservation_id'],
										'reservation_code' => $bkroom['roomreservation_id'],
										'status' => $status,
										'guest_name' => $reservation_name,
										'room_id' => $room_id,
										'channel_id' => $bkroom['channel_id'],
										'start_date' => $bkroom['arrival_date'],
										'end_date' => $bkroom['departure_date'],
										'booking_date' =>$bkroom['date_time'],//$val->date,
										'currency_id' => $bkroom['currencycode'],
										'price' => $bkroom['totalprice'],
										'num_nights' => 1,
										'current_date_time' => $bkroom['current_date_time'],
									);
								}                       
							}
							$merge = array_merge($merge,$bkg);
						}
					}
					else if($channel_name == "BN0W")
					{
						$bnow	=	$this->bnow_model->getReservationLists('all');
						$merge = array_merge($merge,$bnow);
					}
                    else if($channel_name == "TRAVELREPUBLIC")
                    {
                        $travel   =   $this->travel_model->getReservationLists('all');
                        $merge = array_merge($merge,$travel);
                    }
                    elseif($channel_name == "DESPEGAR")
                    {   $this->load->model("despegar_model");
                        $despegar = $this->despegar_model->getReservationLists('all');
                        $merge = array_merge($merge,$despegar);
                    }
				}
			}
            if($merge)
            {
				
                uasort($merge,function($a,$b)
                {
                    return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
                });
				
					//print_r($bulk_resrevation);
					
                    return $merge;
            }
            else{
				
                return $bulk_resrevation;
            }
        }
        else
        {
            
            return $bulk_resrevation;
        }
    }
    
	function get_reservation_list($channel)
    {
        $channel_name=strtoupper($channel);
        if($channel_name == "BOOKING.COM")
		{
            $channel_name = "BOOKING";
			$ch_details = get_data(CONNECT,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'channel_id'=>2))->row();
			
        }
		if($channel_name == "BOOKONLINENOW")
		{
			$channel_name = "BN0W";
		}
        $this->db->order_by('import_reserv_id','desc'); 
        if ($this->db->table_exists('import_reservation_'.$channel_name) )
        {
            $this->db->where("user_id",current_user_type());
            if($channel_name == "BOOKING")
            {
                $this->db->where("hotel_hotel_id",hotel_id());
            }
            else{
                $this->db->where("hotel_id",hotel_id());
            }   
            $res = $this->db->get('import_reservation_'.$channel_name); 
            if($res->num_rows >0)
            {
                $data = $res->result();
                $list = array();
                if($channel_name == "GTA")
                {
                    foreach ($data as $res_det)
                    {
                        $list[] = (object)array(
                                                                'import_reserv_id' => $res_det->import_reserv_id,
                                                                'STATUS'       => $res_det->status,
                                                                'FIRSTNAME'    => $res_det->leadname,
                                                                'ROOMCODE'     => $res_det->room_id,
                                                                'CHECKIN'      => $res_det->arrdate,
                                                                'CHECKOUT'     => $res_det->depdate,
                                                                'RSVCREATE'    => $res_det->modifieddate,
                                                                'IDRSV'        => $res_det->booking_id,
                                                                'CURRENCY'     => $res_det->currencycode,
                                                                'REVENUE'      => $res_det->totalcost,
                                                                'channel_id'      => 8,
                                                                'roomtypeId'   => $res_det->roomcategory,
                                                                'rateplanid'   => $res_det->rateplanid,
                                                                'current_date_time'   => $res_det->current_date_time,
                                                            );
                    }
                    if($list)
					{
						uasort($list,function($a,$b)
						{
							return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
						});
						return $list;
					}
                }
                else if($channel_name == "RECONLINE")
                {
                    return $res->result();
                }
                else if($channel_name == "EXPEDIA")
                {
                    foreach ($data as $res_det) 
                    {
                        $roomtypeid = $res_det->roomTypeID;
                        $rateplanid = $res_det->ratePlanID;
                        $roomdetails = getExpediaRoom($roomtypeid,$rateplanid,current_user_type(),hotel_id());
                        if(count($roomdetails) !=0)
                        {
                            $roomtypeid = $roomdetails['roomtypeId'];
                            $rateplanid = $roomdetails['rateplanid'];
                        }
                        
                        $list[] = (object)array(
                            'import_reserv_id' => $res_det->import_reserv_id,
                            'STATUS'       => $res_det->type,
                            'FIRSTNAME'    => $res_det->givenName.' '.$res_det->middleName.' '.$res_det->surname,
                            'ROOMCODE'     => $res_det->source,
                            'CHECKIN'      => $res_det->arrival,
                            'CHECKOUT'     => $res_det->departure,
                            'RSVCREATE'    => $res_det->created_time,
                            'IDRSV'        => $res_det->booking_id,
                            'CURRENCY'     => $res_det->currency,
                            'REVENUE'      => $res_det->amountAfterTaxes,
                            'channel_id'   => 1,
                            'roomtypeId'   => $roomtypeid,
                            'rateplanid'   => $rateplanid,
                            'current_date_time'   => $res_det->current_date_time,
                        );                         
                    }
                    if($list)
					{
						uasort($list,function($a,$b)
						{
							return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
						});
						return $list;
					}
                }
				else if($channel_name == "HOTELBEDS")
                {
                    foreach ($data as $reservation_details) 
                    {
						/* $htb_id = get_data("import_mapping_HOTELBEDS_ROOMS",array('contract_name'=>$reservation_details->Contract_Name,'contract_code'=>$reservation_details->IncomingOffice,'characterstics' => $reservation_details->CharacteristicCode, 'roomname' => $reservation_details->Room_code,'user_id' => current_user_type(),'hotel_id' => hotel_id())); */
						
						$htb_id = $this->db->query("SELECT map_id, TRIM(TRAILING '-' FROM  REPLACE(roomname,SUBSTRING_INDEX(roomname,'-',-1),'') ) as roomnames, TRIM(TRAILING '-' FROM  REPLACE(characterstics,SUBSTRING_INDEX(characterstics,'-',-1),'') ) as charactersticss FROM `".IM_HOTELBEDS_ROOMS."` where contract_name='".$reservation_details->Contract_Name."' and contract_code='".$reservation_details->IncomingOffice."' and sequence='".$reservation_details->Contract_Code."' and user_id='".current_user_type()."' and hotel_id='".hotel_id()."' having roomnames ='".$reservation_details->Room_code."' AND charactersticss ='".$reservation_details->CharacteristicCode."'");
						
						//echo $this->db->last_query(); 
						
						if($htb_id->num_rows != 0)
						{
							$htb_id = $htb_id->row()->map_id; 
							
							$htbid = get_data(MAP,array('channel_id'=>$reservation_details->channel_id,'import_mapping_id'=>$htb_id));
							
							if($htbid->num_rows != 0)
							{
								$room_id = $htbid->row()->property_id;
							}
							else
							{
								$room_id = "0";
							}
						}
						else
						{
							$room_id = "0";
						}
						$totamount = $reservation_details->TAmount;
						$adult = $reservation_details->AdultCount;
						$child = $reservation_details->ChildCount + $reservation_details->BabyCount;                
						$checkin = $reservation_details->DateFrom;
						$checkout = $reservation_details->DateTo;
						$currency = explode(',',$reservation_details->Currency);
						$currency = $currency[0];
						$name = $reservation_details->Holder;

						$list[] = (object)array(
												'import_reserv_id' => $reservation_details->import_reserv_id,
												'STATUS'       => $reservation_details->RoomStatus,
												'FIRSTNAME'    => $name,
												'ROOMCODE'     => $room_id,
												'CHECKIN'      => $checkin,
												'CHECKOUT'     => $checkout,
												'RSVCREATE'    => $reservation_details->CreationDate,
												'IDRSV'        => $reservation_details->RefNumber,
												'CURRENCY'     => $currency,
												'REVENUE'      => $totamount,
												'channel_id'   => $reservation_details->channel_id,
												'current_date_time'   => $reservation_details->current_date_time,
												);                      
                    }
					if($list)
					{
						uasort($list,function($a,$b)
						{
							return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
						});
						return $list;
					}
                }
                else if($channel_name == "BOOKING" && ( $ch_details->xml_type == 2 || $ch_details->xml_type == 1 ))
                {
                    foreach ($data as $res_det)
                    {
						$bk_details = booking_hotel_id();
                        $bkgroom = get_data("import_reservation_BOOKING_ROOMS",array('hotel_id'=>$bk_details,'reservation_id' => $res_det->id))->result_array();

                        foreach ($bkgroom as $bkg) {
						if($bkg['guest_name']!='')
						{
							$reservation_name 	=	$bkg['guest_name'];
						}
						else
						{
							$reservation_name	=	get_data(BOOK_RESERV,array('id'=>$bkg['reservation_id']))->row();
							$reservation_name	=	$reservation_name->first_name.' '.$reservation_name->last_name;
						}
						
                            $list[] = (object)array(
                                'import_reserv_id' => $bkg['room_res_id'],
                                'STATUS'       => $res_det->status,
                                'FIRSTNAME'    => $reservation_name,
                                'ROOMCODE'     => $bkg['name'],
                                'CHECKIN'      => $bkg['arrival_date'],
                                'CHECKOUT'     => $bkg['departure_date'],
                                'RSVCREATE'    => $bkg['date_time'],//$res_det->date,
                                'IDRSV'        => $bkg['roomreservation_id'],
                                'CURRENCY'     => $bkg['currencycode'],
                                'REVENUE'      => $bkg['totalprice'],
                                'channel_id'   => $bkg['channel_id'],
                                'res_id'       => $bkg['reservation_id'],
                                'roomtypeId'   => $bkg['id'],
                                'rateplanid'   => $bkg['rate_id'],
                                'current_date_time'   => $bkg['current_date_time'],
                            );  
                        }                       
                    }
					if($list)
					{
						uasort($list,function($a,$b)
						{
							return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
						});
						return $list;
					}
                }
				else if($channel_name == "BN0W")
				{
					$bnow	=	$this->bnow_model->getReservationLists('separate');
					if($bnow)
					{
						uasort($bnow,function($a,$b)
						{
							return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
						});
						return $bnow;
					}
					return $bnow;
				}				
                else if($channel_name == "TRAVELREPUBLIC")
                {
                    $travel   =   $this->travel_model->getReservationLists('separate');
                    if($travel)
                    {
                        uasort($travel,function($a,$b)
                        {
                            return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
                        });
                        return $travel;
                    }
                    return $travel;
                }

                 else if($channel_name == "DESPEGAR")
                {   $this->load->model("despegar_model");
                    $travel   =   $this->despegar_model->getReservationLists('separate');
                    if($travel)
                    {
                        uasort($travel,function($a,$b)
                        {
                            return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
                        });
                        return $travel;
                    }
                    return $travel;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
			echo 'sdfsdfsdf'; die;
            return false;
        }
    }
    
	function reservationresult($type='')
    {
        $date = date('d/m/Y');
        $bdate = date('Y-m-d');
        if($type=='reserve')
        {
            $chaReserCheckCount = array();
            $det=$this->db->query('SELECT * , created_date as current_date_time FROM `manage_reservation` WHERE `booking_date`="'.$bdate.'" AND hotel_id='.hotel_id().' AND user_status="Booking" AND status="Reserved"');
            $chaReserCheckCount = array_merge($chaReserCheckCount,$det->result());
            
            $chaReserCheckCount = array_merge($chaReserCheckCount,all_reservation_result($type,$bdate));
            if($chaReserCheckCount)
			{
				uasort($chaReserCheckCount,function($a,$b)
				{
					return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
				});
				return $chaReserCheckCount;
			}
        }
        else if($type=='cancel')
        {
            $chaReserCheckCount = array();
            $det=$this->db->query('SELECT * , created_date as current_date_time FROM `manage_reservation` WHERE `modified_date`="'.$bdate.'" AND hotel_id='.hotel_id().' AND status="Canceled"');
            $chaReserCheckCount = array_merge($chaReserCheckCount,$det->result());
            
            $chaReserCheckCount = array_merge($chaReserCheckCount,all_reservation_result($type,$bdate));
			
			if($chaReserCheckCount)
			{
				uasort($chaReserCheckCount,function($a,$b)
				{
					return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
				});
				return $chaReserCheckCount;
			}
        }
        else if($type=='arrival')
        {
            $chaReserCheckCount = array();
            $det=$this->db->query('SELECT * , created_date as current_date_time FROM `manage_reservation` WHERE str_to_date(`start_date`, "%d/%m/%Y") = str_to_date("'.$date.'", "%d/%m/%Y") AND hotel_id='.hotel_id().' AND user_status="Arrival"');
            $chaReserCheckCount = array_merge($chaReserCheckCount,$det->result());
            
            $chaReserCheckCount = array_merge($chaReserCheckCount,all_reservation_result($type,$bdate));
            
			if($chaReserCheckCount)
			{
				uasort($chaReserCheckCount,function($a,$b)
				{
					return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
				});
				return $chaReserCheckCount;
			}
        }
        else if($type=='depature')
        {
            $chaReserCheckCount = array();
            $det=$this->db->query('SELECT * , created_date as current_date_time FROM `manage_reservation` WHERE str_to_date(`end_date`, "%d/%m/%Y") = str_to_date("'.$date.'", "%d/%m/%Y") AND hotel_id='.hotel_id().' AND user_status="Departure"');
            $chaReserCheckCount = array_merge($chaReserCheckCount,$det->result());
            
            $chaReserCheckCount = array_merge($chaReserCheckCount,all_reservation_result($type,$bdate));
            
            if($chaReserCheckCount)
			{
				uasort($chaReserCheckCount,function($a,$b)
				{
					return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
				});
				return $chaReserCheckCount;
			}
        }
		else if($type=='modify')
        {
            $chaReserCheckCount = array();
            $det=$this->db->query('SELECT * , created_date as current_date_time FROM `manage_reservation` WHERE str_to_date(`end_date`, "%d/%m/%Y") = str_to_date("'.$date.'", "%d/%m/%Y") AND hotel_id='.hotel_id().' AND user_status="modify"');
            $chaReserCheckCount = array_merge($chaReserCheckCount,$det->result());
            
            $chaReserCheckCount = array_merge($chaReserCheckCount,all_reservation_result($type,$bdate));
            
            if($chaReserCheckCount)
			{
				uasort($chaReserCheckCount,function($a,$b)
				{
					return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
				});
				return $chaReserCheckCount;
			}
        }
		
    }
    
    /*function get_all_room_list($rese_id){
        $this->db->where('reservation_id',$rese_id);
        $res = $this->db->get('manage_reservation1');   
        if($res->num_rows >0){
            return $res->result();
        }
            return false;
    }
*/
    function get_all_room_list($rese_id)
    {
        $this->db->where('reservation_id',$rese_id);
        $res = $this->db->get('manage_reservation');    
        if($res->num_rows ==1)
        {
            return $res->row();
        }
        return false;
    }
    
    function getRoomChannel($curr_cha_id,$reser_id)
    {
        if(unsecure($curr_cha_id)==11)
        {
            
        }
    }
    
    function get_admin(){
        $this->db->where('id','1');
        $query=$this->db->get('manage_admin_details');
        if($query->num_rows==1) { 
            return $query->row();
        } else { 
            return false;
        } 
    }
    
    function room_image($room_ids){
        $this->db->where('room_id',$room_ids);
        $room_img = $this->db->get('room_photos');
        if($room_img->num_rows>0){
            return $room_img->result();
        }
            return false;
    }
    
    function select_reservation($url)
	{
		$uri = floatval(insep_decode($url));
		$count = $this->db->select('reservation_id')->from(RESERVATION)->where(array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'reservation_id'=>$uri))->count_all_results();
		if($count!=0)
		{
			$guest_name = $this->input->post('guest_name');
			$last_name = $this->input->post('last_name');
			$email = $this->input->post('email');
			$mobile = $this->input->post('mobile');
			$country = $this->input->post('country');
			$province = $this->input->post('province');
			$street_name = $this->input->post('street_name');
			$city_name = $this->input->post('city_name');   
			if($this->is_card_details($url))
			{
				$this->db->where('manage_reservation.reservation_id',$uri);
				$this->db->JOIN('card_details','card_details.resrv_id = manage_reservation.reservation_id');
			}
			else
			{
				$this->db->where('manage_reservation.reservation_id',$uri);
			}
			$res = $this->db->get('manage_reservation');
			if($res->num_rows == 1 )
			{
				return $res->row();
			}
			return false;
		}
		else
		{
			redirect('reservation/reservationlist','refresh');
		}
    } 

    function is_card_details($resrv_id)
	{
		$uri = floatval(insep_decode($resrv_id));
        $this->db->where('resrv_id',$uri);
        $query = $this->db->get("card_details");
        if($query->num_rows() != 0)
		{
            return true;
        }
		else
		{
            return false;
        }
    }
    
    function select_reserve($uri){
        $guest_name = $this->input->post('guest_name');
        $last_name = $this->input->post('last_name');
        $email = $this->input->post('email');
        $mobile = $this->input->post('mobile');
        $country = $this->input->post('country');
        $province = $this->input->post('province');
        $street_name = $this->input->post('street_name');
        $city_name = $this->input->post('city_name');       
        $this->db->where('reservation_id',$uri);
        $res = $this->db->get('manage_reservation');
        if($res->num_rows == 1 ){
            return $res->row();
        }
            return false;
    } 
    
    function select_room($id){
        $this->db->where('reservation_id',$id);
        $res = $this->db->get('manage_reservation');    
        if($res->num_rows >0){
            return $res->result();
        }
            return false;
    }
    
    function edit_reservation($reser_id){
        $guest_name = $this->input->post('guest_name');
        $last_name = $this->input->post('last_name');
        $email = $this->input->post('email');
        $mobile = $this->input->post('mobile');
        $country = $this->input->post('country');
        $province = $this->input->post('province');
        $street_name = $this->input->post('street_name');
        $city_name = $this->input->post('city_name');
        $zipcode = $this->input->post('zipcode');
        $sel = array('guest_name'=>$guest_name,'last_name'=>$last_name,'email'=>$email,'mobile'=>$mobile,'country'=>$country,'province'=>$province,'street_name'=>$street_name,'city_name'=>$city_name,'zipcode'=>$zipcode,'modified_date'=>date('Y-m-d'));
        $this->db->where('reservation_id',$reser_id);
        $result = $this->db->update('manage_reservation',$sel);
        $data = array('reservation_id'=>$reser_id,'history_date'=>date('Y-m-d H:i:s'));
        $result = $this->db->insert('new_history',$data);
        // echo $this->db->last_query();die;
        if($result){
            return true;
        }
            return false;
    }
    
    function edit_reserve($reser_id){
        $guest_name = $this->input->post('guest_name');
        $last_name = $this->input->post('last_name');
        $email = $this->input->post('email');
        $mobile = $this->input->post('mobile');
        $country = $this->input->post('country');
        $province = $this->input->post('province');
        $street_name = $this->input->post('street_name');
        $city_name = $this->input->post('city_name');
        $sel = array('guest_name'=>$guest_name,'last_name'=>$last_name,'email'=>$email,'mobile'=>$mobile,'country'=>$country,'province'=>$province,'street_name'=>$street_name,'city_name'=>$city_name);
        $this->db->where('reservation_id',$reser_id);
        $result = $this->db->update('manage_reservation',$sel);
        if($result){
            return true;
        }
            return false;
    }
    
  // all countries..
    function get_country_name($country_id=''){  
        $this->db->order_by('country_name','asc');  
        $sel = $this->db->get('country');
    if($sel->num_rows>0){       
            return $sel->result();              
        }
            return false;
    }
    // get single country name...
    function get_country_name_id($country_id){  
        $this->db->order_by('country_name','asc');  
        $this->db->where('id',$country_id);
        $sel = $this->db->get('country');
    if($sel->num_rows ==1){     
            return $sel->row();             
        }
            return false;
    }
    // get single row from currency ..
    function get_curreny_name($currency_id){
        $this->db->order_by('currency_name','asc');
        $this->db->where('currency_id',$currency_id);
        $sel = $this->db->get('currency');
        if($sel->num_rows == 1){
            return $sel->row();
        }
            return false;
    }
    
    function add_adjustments(){
        $reservation_id = $this->input->post('reservation_id');
        $amount_type = $this->input->post('amount_type');
        $inr_amount = $this->input->post('inr_amount');
        $description = $this->input->post('description');
        $data = array('reservation_id'=>$reservation_id,'amount_type'=>$amount_type,'inr_code'=>$inr_amount,'description'=>$description,'adjustment_date'=>date('Y-m-d H:i:s'));
        $res = $this->db->insert('adjustments',$data);
        $res = $this->db->insert('history_reservation',$data);
        // echo $this->db->last_query(); die;
        if($res){
            return true;
        }
            return false;
        
    }
    
    function select_adjustments($uri){
        // echo 'dffd';die;
$this->db->order_by('adjust_id','desc');
        $this->input->post('amount_type');
        $this->input->post('inr_amount');
        $this->input->post('description');
        $this->db->where('reservation_id',$uri);
        $res = $this->db->get('adjustments');
        // echo $this->db->last_query();die;
        if($res->num_rows >0){
            return $res->result();
        }
            return false;
    }
    
    // get adjustments..
    function get_adjustments($adjust_id){
        $this->db->where('adjust_id',$adjust_id);
        $res = $this->db->get('adjustments');
        if($res->num_rows >0){
            return $res->result();
        }
            return false;
    }
    
    // edit adjustments..
    function edit_adjustments($update_id){
        $reservation_id = $this->input->post('reservation_id');
        $amount_type = $this->input->post('amount_type');
        $inr_amount = $this->input->post('inr_amount');
        $description = $this->input->post('description');
        $data = array('amount_type'=>$amount_type,'inr_code'=>$inr_amount,'description'=>$description,'adjustment_update'=>date('Y-m-d H:i:s'));
        $this->db->where('adjust_id',$update_id);
        $res = $this->db->update('adjustments',$data);
        $old_amount = $this->input->post('old_amount');
        $data = array('adjust_id'=>$update_id,'amount_type'=>$amount_type,'inr_code'=>$inr_amount,'description'=>$description,'reservation_id'=>$reservation_id,'old_amount'=>$old_amount);
        $res = $this->db->insert('history_reservation',$data);
        // echo $this->db->last_query();die;
        if($res){
            return true;
        }
            return false;
    }
    
    // delete adjustments...
    function delete_adjustment($del_id){
        $des = $this->input->post('des');
        $res = $this->input->post('res');
        $data = array('adjust_id'=>$del_id,'description'=>$des,'reservation_id'=>$res);
        $this->db->insert('history_reservation',$data);
        // echo $this->db->last_query();die;
        $this->db->where('adjust_id',$del_id);
        $this->db->delete('adjustments');
        return true;
    }
    
    // count adjustments..
    function total_adjustments($reservation_id){
        $count = $this->db->select('adjust_id')->from('adjustments')->
        where(array('reservation_id'=>$reservation_id))->count_all_results();
        return $count;
    }
    
    // adjustments results...
    function total_adjustmentsresults($res_id){
        // echo 'ddsf';die;
        $this->db->where('reservation_id',$res_id);
        $res = $this->db->get('adjustments');
        if($res->num_rows >0){
            return $res->result();
        }
            return false;
    }
    
    // select history_reservation...
    function get_history($history_uri){
        // echo 'dfdf';die;
        $this->db->order_by('history_id','desc');
        $this->db->where('reservation_id',$history_uri);
        $res = $this->db->get('history_reservation');
        if($res->num_rows > 0){
            return $res->result();
        }
            return false;
    }
    
    // add bank deatils...
    function add_bankdetails(){
        $user_id = current_user_type();
        $hotel_id = hotel_id();
        //$pay_name = $this->input->post('pay_name');
        $account_owner = $this->input->post('account_owner');
        $currency = $this->input->post('currency');
        $bank_name = $this->input->post('bank_name');
        $branch_name = $this->input->post('branch_name');
        $branch_code = $this->input->post('branch_code');
        $swift_code = $this->input->post('swift_code');
        $iban = $this->input->post('iban');
        $account_number = $this->input->post('account_number');
        $data = array('user_id'=>$user_id,'hotel_id'=>$hotel_id,'account_owner'=>$account_owner,'currency'=>$currency,'bank_name'=>$bank_name,'branch_name'=>$branch_name,'branch_code'=>$branch_code,'swift_code'=>$swift_code,'iban'=>$iban,'account_number'=>$account_number);
        $result = $this->db->insert('bank_details',$data);
        //echo $this->db->last_query();die;
        if($result)
        {
            return true;
        }
            return false;
    }
    
    // select user bank details...
    
    function get_bankdetails($bank_id='')
    {
                if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $user_id = user_id();
        }
        else if(user_type()=='2')
        {
            $user_id = owner_id();
        }
        $this->db->where('hotel_id',hotel_id());
                $this->db->where('user_id',$user_id);
        //$this->db->where('user_id',user_id());
        if($bank_id!='')
        {
            $this->db->where('bank_id',$bank_id);
        }
        $result = $this->db->get('bank_details');
        if($result->num_rows > 0){
            return $result->result();
        }
            return false;
    }
    
    // add pay name...
    function add_pay_name(){ 
                if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $user_id = user_id();
        }
        else if(user_type()=='2')
        {
            $user_id = owner_id();
        }
        $user_name = $this->input->post('user_name');
        $data = array('user_name'=>$user_name);
        $this->db->where('hotel_id',hotel_id());
        //$this->db->where('user_id',user_id());
                $this->db->where('user_id',$user_id);
        $result = $this->db->update('bank_info',$data);
        // echo $this->db->last_query();die;
        if($result){
            return true;
        }
            return false;
    }


    //  get_bank


function get_bank(){
                if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $user_id = user_id();
        }
        else if(user_type()=='2')
        {
            $user_id = owner_id();
        }
        $this->db->where('user_id',$user_id);
        //$this->db->where('user_id',user_id());
        $this->db->where('hotel_id',hotel_id());
        $result = $this->db->get('bank_info');
        if($result->num_rows > 0){
            return $result->row();
        }
            return false;
    }
    
    // tax categories results...
    function get_tax($tax_id){
        $this->db->where('tax_id',$tax_id);
        $this->db->where('user_id',current_user_type());
        $this->db->where('hotel_id',hotel_id());
        $res = $this->db->get('tax_categories');
        if($res->num_rows >0){
            return $res->result();
        }
            return false;
    }
    
    
    // edit bank deatils...
    function edit_bankdetails($bank_id){
        // $bank_id = $this->input->post('bank_id');
                if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $user_id = current_user_type();
        }
        else if(user_type()=='2')
        {
            $user_id = owner_id();
        }
        $account_owner = $this->input->post('account_owner');
        $currency = $this->input->post('currency');
        $bank_name = $this->input->post('bank_name');
        $branch_name = $this->input->post('branch_name');
        $branch_code = $this->input->post('branch_code');
        $swift_code = $this->input->post('swift_code');
        $iban = $this->input->post('iban');
        $account_number = $this->input->post('account_number');
        $data = array('account_owner'=>$account_owner,'currency'=>$currency,'bank_name'=>$bank_name,'branch_name'=>$branch_name,'branch_code'=>$branch_code,'swift_code'=>$swift_code,'iban'=>$iban,'account_number'=>$account_number);
                $this->db->where('user_id',$user_id);
        $this->db->where('hotel_id',hotel_id());
        $this->db->where('bank_id',$bank_id);
        $result = $this->db->update('bank_details',$data);
        //echo $this->db->last_query();die;
        if($result){
            return true;
        }
            return false;
    }
    function country_name_id($currency_id){
        $this->db->order_by('currency_name','asc');
        $this->db->where('currency_id',$currency_id);
        $sel = $this->db->get('currency');
        if($sel->num_rows == 1){
            return $sel->row();
        }
            return false;
    }
    
    // delete bank information...
    function delete_bank_info($del){
        $this->db->where('bank_id',$del);
        $this->db->delete('bank_details');
        return true;
    }
    // add paypal details...
    function add_paypal()
    {
        $user_id = current_user_type();
        $hotel_id = hotel_id();
        $status = $this->input->post('status');
        $paypal = insep_decode($this->input->post('pay_method'));
        $holder_name = $this->input->post('holder_name');
        $client_id = $this->input->post('client_id');
        $client_secret = $this->input->post('client_secret');
        $paypal_currency = $this->input->post('paypal_currency');
        $server = $this->input->post('server');
        $data = array('user_id'=>$user_id,'hotel_id'=>$hotel_id,'status'=>$status,'pay_id'=>$paypal,'holder_name'=>$holder_name,'client_id'=>$client_id,'client_secret'=>$client_secret,'paypal_currency'=>$paypal_currency,'server'=>$server);
        $result = $this->db->insert('paypal_details',$data);
        // echo $this->db->last_query();
        if($result){
            return true;
        }
            return false;
    }
    
    // get paypal details...
    function get_paypal(){
                if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $user_id = current_user_type();
        }
        else if(user_type()=='2')
        {
            $user_id = owner_id();
        }
        // $user_id = user_id();
                $this->db->where('user_id',$user_id);
        $hotel_id = hotel_id();
        $this->db->order_by('paypal_id','desc');
        $this->db->where('user_id',$user_id);
        $this->db->where('hotel_id',$hotel_id);
        $res = $this->db->get('paypal_details');
        // echo $this->db->last_query(); die;
        if($res->num_rows >0)
        {
            return $res->row();
        }
            return false;
    }
    
    // select paypal deatils...
    function edit_paypal(){
                if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $user_id = user_id();
        }
        else if(user_type()=='2')
        {
            $user_id = owner_id();
        }
        // $user_id = user_id();
        $paypal_id = $this->input->post('paypal_id');
        $status = $this->input->post('status');
        $paypal = $this->input->post('paypal');
        $holder_name = $this->input->post('pay_method_name');
        $client_id = $this->input->post('client_id');
        $client_secret = $this->input->post('client_secret');
        $paypal_currency = $this->input->post('paypal_currency');
        $server = $this->input->post('server');
        $data = array('pay_method_name'=>$holder_name,'client_id'=>$client_id,'client_secret'=>$client_secret,'paypal_currency'=>$paypal_currency,'server'=>$server);
        $this->db->where('paypal_id',$paypal_id);
        $this->db->where('user_id',$user_id);
        $this->db->where('hotel_id',hotel_id());
        $result = $this->db->update('paypal_details',$data);
        // echo $this->db->last_query(); die;
        if($result){
            return true;
        }
            return false;
    }
    
    // paypal details...
    function get_payment_name(){
    
    $sel =  $this->db->query("select * from payment_list where !find_in_set('".hotel_id()."',user_id) AND status ='1'");
    if($sel->num_rows>0){       
        return $sel->result();              
    }
    
        return false;
    }
    // get single paypal details...
    function get_payment_namebyid($paymentid){
        $this->db->order_by('payment_type','asc');  
        $this->db->where('pay_id',$paymentid);
        $res= $this->db->get('payment_list');
        if($res->num_rows == 1){
            return $res->row();
        }
            return false;
    }
    
    // add tax categories..
    function add_taxcategories(){
        $user_id = current_user_type();
        $hotel_id = hotel_id();
        $user_name = $this->input->post('user_name');
        $included_price = $this->input->post('included_price');
        $tax_rate = $this->input->post('tax_rate');
        $data = array('user_id'=>$user_id,'hotel_id'=>$hotel_id,'user_name'=>$user_name,'included_price'=>$included_price,'tax_rate'=>$tax_rate);
        $res = $this->db->insert('tax_categories',$data);
        // echo $this->db->last_query();die;
        if($res){
            return true;
        }
            return false;
    }
    
    
    // count tax categories..
    function total_taxcategory(){
        $count = $this->db->select('tax_id')->from('tax_categories')->
        where(array('user_id'=>current_user_type(),'hotel_id'=>hotel_id()))->count_all_results();
        return $count;
    }
    
    // tax categories results...
    function total_taxcategoryresults(){
        $this->db->order_by('tax_id','desc');
        $this->db->where('user_id',current_user_type());
        $this->db->where('hotel_id',hotel_id());
        $res = $this->db->get('tax_categories');
        if($res->num_rows >0){
            return $res->result();
        }
            return false;
    }
    
    // update tax categories...
    function edit_taxcategories($tax_id){
        // $tax_id = $this->input->post('tax_id');
        $user_name = $this->input->post('user_name');
        $included_price = $this->input->post('included_price');
        $tax_rate = $this->input->post('tax_rate');
        $data = array('user_name'=>$user_name,'included_price'=>$included_price,'tax_rate'=>$tax_rate);
        $this->db->where('tax_id',$tax_id);
        $this->db->where('user_id',current_user_type());
        $this->db->where('hotel_id',hotel_id());
        $res = $this->db->update('tax_categories',$data);
        // echo $this->db->last_query();die;
        if($res){
            return true;
        }
            return false;
    }
    
    // delete tax categories..
    function delete_taxcategory($del)
    {
        $this->db->where('tax_id',$del);
                if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $user_id = user_id();
        }
        else if(user_type()=='2')
        {
            $user_id = owner_id();
        }
        $this->db->where('user_id',$user_id);
        $this->db->where('hotel_id',hotel_id());
        $this->db->delete('tax_categories');
        return true;
    }
    
    // get payment list...
    function get_paymentlist(){
        $hotel_id = hotel_id();
        $res =  $this->db->query("select * from payment_list where find_in_set('".$hotel_id."',user_id) AND status='1'");
        //$res = $this->db->get('payment_list');
        if($res->num_rows >0){
            return $res->result();
        }
            return false;
    }

// user status info...
    
    function user_status($reservation_id){
        $value = $this->input->post('user_det'); 
        $data = array('user_status'=>$value);
        $this->db->where('reservation_id',$reservation_id);
        $res = $this->db->update('manage_reservation',$data);
        echo $this->db->last_query();die;
        if($res){
            return true;
        }
            return false;
    }
    
    // get_payment...
    /* function get_payment(){
        
      $this->db->select * from  payment_list where !find_in_set(.','.'user_id',user_id());
      return true;
      $this->db->where !find_in_set(.','.'user_id'=>user_id());
      $res = $this->db->get('payment_list');
      if($res){
          return true;
      }
          return false;
      
    }   */
    
    
    // filter ..
      function res_filter(){
        $reservation_code = $this->input->post('reservation_code');
        $guest_name = $this->input->post('guest_name');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $res = $this->db->query("select * from manage_reservation where reservation_code='$reservation_code' AND guest_name= '$guest_name' AND start_date='$start_date' AND end_date='$end_date'");
        /* echo '<pre>';
        print_r($res->row());die; */
        // echo $this->db->last_query();die;
        
        if($res->num_rows > 0){
            
            return $res->result();
        }
            return false;
        
    }
 
    // change status..
    function change_status(){
        
        $id = $this->input->post('id');
      
        
        if($this->input->post('method')=='confirm')
        {
            $data = array('status'=>'Confirmed');
        }
        else if($this->input->post('method')=='cancel')
        {
            $data = array('status'=>'Canceled','modified_date'=>date('Y-m-d'),'cancel_date'=>date('Y-m-d H:i:s'));
        }
		else if($this->input->post('method')=='noshow')
        {
            $data = array('status'=>'No Show','modified_date'=>date('Y-m-d'),'cancel_date'=>date('Y-m-d H:i:s'));
        }
		
        $this->db->where('reservation_id',$id);
        $sel = $this->db->update('manage_reservation',$data);
        if($sel){
            
            $can_note = array('reservation_id'=>$id,'user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'type'=>4,'created_date'=>date('Y-m-d H:i:s'),'status'=>'unseen');
            $ver = $this->db->insert('notifications',$can_note);
            
           // $property_details = get_data(TBL_USERS,array('user_id'=>get_data('manage_reservation',array('reservation_id'=>$id))->row()->user_id))->row();

              $property_details = get_data(HOTEL,array('hotel_id'=>get_data('manage_reservation',array('reservation_id'=>$id))->row()->hotel_id))->row();
            
            
            $reservation_details = get_data('manage_reservation',array('reservation_id'=>$id))->row();
            
            if($this->input->post('method')=='cancel' || $this->input->post('method')=='noshow')
            {
                if($this->input->post('can_options')=='1')
                {
					$endDate = date('d/m/Y', strtotime('+0 day', strtotime(date('Y-m-d',strtotime(str_replace("/","-",$reservation_details->end_date))))));
					
                    $startDate = DateTime::createFromFormat("d/m/Y",$reservation_details->start_date);
                
                    $endDate = DateTime::createFromFormat("d/m/Y",$endDate);
            
                    $periodInterval = new DateInterval("P1D");
            
                    //$endDate->add( $periodInterval );
            
                    $period = new DatePeriod( $startDate, $periodInterval, $endDate );
            
                    //$endDate->add( $periodInterval );
                    
                    foreach($period as $date)
                    {
                        $diff = $reservation_details->num_rooms;
                        $get_available = get_data(TBL_UPDATE,array('owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'room_id'=>$reservation_details->room_id,'individual_channel_id'=>0,'separate_date'=>$date->format("d/m/Y")))->row();
                                    
                        $upaval['availability'] = $get_available->availability + $diff ;
                        $upaval['stop_sell'] = 0;
                        $upaval['trigger_cal'] = 1 ;

                        update_data(TBL_UPDATE,$upaval,array('owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'individual_channel_id'=>0,'room_id'=>$reservation_details->room_id,'separate_date'=>$date->format("d/m/Y")));

                        //$this->db->query('call  UpdateAvailabilityInMain("'.TBL_UPDATE.'","'.$upaval['availability'].'","'.current_user_type().'","'.hotel_id().'","'.$reservation_details->room_id.'","'.$date->format("d/m/Y").'")');
                      

                        $channel['channel_id'] = 0;
                        $channel['property_id'] = $reservation_details->room_id;
                        $channel['rate_id'] = 0;
                        $channel['guest_count'] = 0;
                        $channel['refun_type'] = 0;
                        $channel['start'] = $reservation_details->start_date;
                        $channel['end'] = $reservation_details->end_date;

                        require_once(APPPATH.'controllers/mapping.php'); 
                        $callAvailabilities = new Mapping();

                        $roomMappingDetails =   get_data(MAP,array('property_id' => $reservation_details->room_id,'owner_id'=>current_user_type()))->row_array();

                        if(count($roomMappingDetails)!=0){
                            
                            $callAvailabilities->update_channel(current_user_type(),hotel_id(),$upaval,$channel,$date->format("d/m/Y"),$mapping_id="","manual");
                        }

                        //$callAvailabilities->update_subrooms($upaval['availability'],$channel,$date->format("d/m/Y"),current_user_type(),hotel_id());
                    }
                }
            }
            
            $cancel_details = get_data(PCANCEL,array('user_id'=>current_user_type()))->row(); 
            
            $other_details = get_data(POTHERS,array('user_id'=>current_user_type()))->row();
            
            if($other_details->smoking==1)
            {
                $smoke = 'Smoking is allowed';
            }
            else if($other_details==0)
            {
                $smoke = 'Smoking is not allowed.';
            }
            
            if($other_details->smoking==1)
            {
                $pets = 'Pets are allowed';
            }
            else if($other_details==0)
            {
                $pets = 'No pets allowed';
            }
            
            $admin_detail = get_data(TBL_SITE,array('id'=>1))->row();
            
            if($this->input->post('method')=='confirm')
            {
                $get_email_info1        =   get_mail_template('13');
            }
            else if($this->input->post('method')=='cancel')
            {
                $get_email_info1        =   get_mail_template('15');
            }
			else if($this->input->post('method')=='noshow')
            {
                $get_email_info1        =   get_mail_template('15');
            }

            $subject1           =   $get_email_info1['subject'];

            $template1          =   $get_email_info1['message'];

            $user_details = get_data(TBL_USERS,array('user_id'=>user_id()))->row_array();
            $username = ucfirst($user_details['fname']).' '.ucfirst($user_details['lname']);
        
            $message = "Location:Manual Reservation,Reservation Id:".$reservation_details->reservation_code.", Name:".ucfirst($reservation_details->guest_name).", Check In Date:".$reservation_details->start_date.", Check Out Date:".$reservation_details->end_date.", Room:".ucfirst($property_details->property_name).", Price:".$reservation_details->price.", Booking Status:Cancelled, IP:".$this->input->ip_address()." User:".$username;

            $this->inventory_model->write_log($message);

            $tbl_data1 ='';

            $tbl_data1 = '<div class="row">

      <div class="co-md-12 col-sm-12">
<table class="summaryTable">

  <tbody>

  <tr>

    <th>

       Hotel Name

    </th>

    <td>

      <b>'.ucfirst($property_details->property_name).'</b>

    </td>

  </tr>
  <tr>

    <th>

       Confirmation number 

    </th>

    <td>

      <b>'.$reservation_details->reservation_code.'</b>

    </td>

  </tr>
  <tr>

    <th>

       Guest Name 

    </th>

    <td>

      <b>'.ucfirst($reservation_details->guest_name.' '.$reservation_details->last_name).'</b>

    </td>

  </tr>

      <tr>

    <th>

      Check-in date

    </th>

    <td>

     '.$reservation_details->start_date.'

    </td>

  </tr>

  <tr>

    <th>

      Check-out date

    </th>

    <td>

      '.$reservation_details->end_date.'

    </td>

  </tr>



  <tr>

    <th>

      Daily Average Rate

    </th>

    <td>

      '.$reservation_details->price.'

    </td>

  </tr>

  <tr>

    <th>

      Order Total

    </th>

    <td>

      '.$reservation_details->price*$reservation_details->num_nights.'

    </td>

  </tr>
  
  <tr>

    <th>

      Guest Count

    </th>

    <td>

      '.$reservation_details->members_count.'

    </td>

  </tr>

  </tbody>

</table>

<h3>Hotel Policies</h3>

<table class="summaryTable">

  <tbody>

      <tr>

        <th>Cancellation</th>

        <td>

              '.$cancel_details->description.'


        </td>

      </tr>

      <tr>

        <th>Check-in time</th>

        <td>After '.$other_details->check_in_time.' day of arrival.</td>

      </tr>

      <tr>

        <th>Check-out time</th>

        <td>'.$other_details->check_out_time.' upon day of departure.</td>

      </tr>

      <tr>

        <th>Smoking</th>

        <td>'.$smoke.'.</td>

      </tr>

      <tr>

        <th>Pets</th>

        <td>'.$pets.'</td>

      </tr>

  </tbody>

</table>
      </div>

      </div>';  
      
      // print_r($data);die;

if($this->input->post('method')=='confirm')
{
        $data1 = array(

                    '###USERNAME###'=>$reservation_details->guest_name.' '.$reservation_details->last_name,

                    '###COMPANYLOGO###'=>base_url().'uploads/logo/'.$admin_detail->site_logo,

                    '###SITENAME###'=>$admin_detail->company_name,

                    '###STATUS###'=>$reservation_details->status,

                    '###PROPERTYUSER###'=>$property_details->property_name,
                    
                    '###CONFIRMRESERVATION###'=>$tbl_data1,
                    
                    '###SITELINK###'=>base_url(),
                    
                    '###RESERLINK###'=>base_url().'reservation/reservation_print/'.insep_encode($id),

                    );
}
else if($this->input->post('method')=='cancel' || $this->input->post('method')=='noshow')
{
    $data1 = array(

                    '###USERNAME###'=>$reservation_details->guest_name.' '.$reservation_details->last_name,

                    '###COMPANYLOGO###'=>base_url().'uploads/logo/'.$admin_detail->site_logo,

                    '###SITENAME###'=>$admin_detail->company_name,

                    '###STATUS###'=>$reservation_details->status,

                    '###PROPERTYUSER###'=>$property_details->property_name,
                                    
                    '###SITELINK###'=>base_url(),
                

                    );
}
        



            $subject_data1 = array(

                        '###SITENAME###'=>$admin_detail->company_name,
                        '{RESERVATIONCODE}'=>$reservation_details->reservation_code,

                );

                $subject_new1 = strtr($subject1,$subject_data1);


                $content_pop1 = strtr($template1,$data1);
                
            /*  print_r($content_pop1);
                
                print_r($content_pop);
                
                exit;*/
                $this->mailsettings();

                

                $this->email->from($admin_detail->email_id);

                

                $this->email->to($reservation_details->email);

                

                $this->email->subject($subject_new1);

                

                $this->email->message($content_pop1);

                

                if($this->email->send())

                {

                    
/*
                    $this->email->from($admin_detail->email_id);

                    

                    $this->email->to($admin_detail->acc_email);

                    

                    $this->email->subject($subject_new);

                    

                    $this->email->message($content_pop);

                    

                    $this->email->send();*/

                

                    //send_notification($admin_detail->email_id,$email,$content_pop1,$subject_new1);

                    

/*                  //send_notification($admin_detail->email_id,$admin_detail->acc_email,$content_pop,$subject_new);*/


        

                }
                return $id;

        
            return true;
        }
            return false;
    }
 
 
 function get_room_name_id($id){    
        // $this->db->order_by('room_type','asc');  
        $this->db->where('property_id',$id);
        $sel = $this->db->get('manage_property');
    if($sel->num_rows ==1){     
            return $sel->row();             
        }
            return false;
    }
    
    // policy cancellation ..
    function policy_cancel(){
        $this->db->where('user_id',current_user_type());
        $res = $this->db->get('policy_cancelation');
        if($res->num_rows ==1){
            return $res->row();
        }
            return false;
    }
    
    // get meal plan...
    function get_meal_plan_id($id){ 
        $this->db->where('meal_id',$id);
        $sel = $this->db->get('meal_plan');
    if($sel->num_rows ==1){     
            return $sel->row();             
        }
            return false;
    }
    
    // others policy
    function get_otherpolicy(){
      $this->is_login();
        $res = $this->db->get('policy_others');
        if($res->num_rows == 1){
            return $res->row();
        }
            return false;
    }
         function cash_status()

    {

       $this->is_login();

       extract($this->input->post());



       if($status_method=='active')

       {

           $udata['status'] = '1';

       }

       elseif($status_method=='passive')

       {

           $udata['status'] = '0';

       }

       if($status_type=='cash')

       {



           if(update_data('cash_details',$udata,array('user_id'=>current_user_type())))

           {

               $cash_details = get_data('cash_details',array('user_id'=>current_user_type()))->row();

               if($status_method=='active')

               {

               ?>

               <a data-remotes="true" href="javascript:;" class="cash_active" id="cash_active" type="cash" method="passive" data-id="<?php echo $cash_details->cash_id;?>">

               <input type="hidden" id="cash_current_status" value="<?php echo $cash_details->status?>" />

               <div class="onoffswitch-wrap ">

               <div class="onoffswitch">

               <input type="checkbox" id="active-channel" class="onoffswitch-checkbox" name="active-channel" checked="">

               <label for="active-channel" class="onoffswitch-label"></label>

               </div>

               <label class="switch-label" for="active-channel">Active</label>

               </div>

               </a>

               <?php

               }

               elseif($status_method=='passive')

               {

               ?>

               <a data-remotes="true" href="javascript:;" class="cls_passive" id="cash_active" type="cash" method="active" data-id="<?php echo $cash_details->cash_id;?>">

               <input type="hidden" id="cash_current_status" value="<?php echo $cash_details->status?>" />

               <div class="onoffswitch-wrap switch-label-deactivate">

                 <div class="onoffswitch">

               <input type="checkbox" id="active-channel" class="onoffswitch-checkbox" name="active-channel">

               <label for="active-channel" class="onoffswitch-label"></label>

               </div>

                <label class="switch-label" for="active-channel">Passive</label>

               </div>

               </a>

               <?php

               }

           }

       }

    }

    // 30/10/2015...

    function get_cash(){
        if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
      {
        $user_id = user_id();
      }
      else if(user_type()=='2')
      {
        $user_id = owner_id();
      }
       
       $this->db->where('hotel_id',hotel_id());

       $this->db->where('user_id',$user_id);

       $result = $this->db->get('cash_details');

       if($result->num_rows > 0){

           return $result->row();

       }

           return false;

    }

    // edit cash details...

    function edit_cashdet(){
        if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
       {
         $user_id = user_id();
       }
       else if(user_type()=='2')
       {
         $user_id = owner_id();
       }

       $user_name = $this->input->post('user_name');

       $user_det = $this->input->post('user_des');

       $data = array('user_name'=>$user_name,'description'=>$user_det);

       $this->db->where('user_id',$user_id);
       
       $this->db->where('hotel_id',hotel_id());

       $res = $this->db->update('cash_details',$data);
       

       // echo $this->db->last_query();die;

       if($res){

           return true;

       }

           return false;

    }

    function channel_name()
	{
		$this->db->select('C.channel_id,C.channel_name');
		$this->db->join(ALL .' as A','C.channel_id=A.channel_id');
        $this->db->where('C.status','active');
        $res = $this->db->get('manage_channel AS C');
        if($res->num_rows > 0)
        {
            return $res->result();
        }
		return false;
    }

    /*Start Subbaiah \*/

    function get_reserve()
    {
		if(user_type()=='1' || user_type()=='2' && in_array('2',user_edit()) || admin_id()!='' && admin_type()=='1')
		{
			$user_id	=	current_user_type();
		}
		
		$start_date		=	$this->input->get('dp1');
		
		$end_date	 	=	$this->input->get('dp2');
		
		$rooms 			=	$this->input->get('num_rooms');
		
		$adult 			=	$this->input->get('num_person');
		
		$child 			=	$this->input->get('num_child');
		
		$checkin_date	=	str_replace("/","-",$this->input->get('dp1'));
		
        $checkout_date	=	str_replace("/","-",$this->input->get('dp2'));
		
		$start			=	strtotime($checkin_date);
		
        $end 			=	strtotime($checkout_date);
		
        $nights	 		=	ceil(abs($end - $start) / 86400);

        $startDate      =   DateTime::createFromFormat("d/m/Y",$start_date);

        $endDate        =   DateTime::createFromFormat("d/m/Y",$end_date);

        $periodInterval =   new DateInterval( "P1D" );

        $period         =   new DatePeriod( $startDate, $periodInterval, $endDate );

        if($period)
        {
            $base_room_result = array();
            $j=0;
            foreach ($period as $key => $value) {

                $baseRoom = $this->db->query('
                                                SELECT P.description , U.room_update_id, U.room_id , U.separate_date , U.minimum_stay , U.price, P.price as base_price , P.image , P.property_name , P.member_count , P.children , P.number_of_bedrooms FROM '.TBL_UPDATE.' U JOIN '.TBL_PROPERTY.' P ON U.room_id = P.property_id WHERE U.separate_date="'.$value->format("d/m/Y").'" AND U.availability >="'.$rooms.'" AND U.minimum_stay <= "'.$nights.'" AND P.member_count >="'.$adult.'" AND P.children >="'.$child.'" AND individual_channel_id =0 AND stop_sell=0 AND P.owner_id="'.$user_id.'" AND P.hotel_id="'.hotel_id().'" GROUP BY U.room_id ORDER BY U.room_id DESC'
                                             );
				/* echo $this->db->last_query(); */
				if($baseRoom->num_rows != 0) {

                    $base_room_value =  $baseRoom->result_array();

                    $total_base_room_result[$j++]= array_merge($base_room_result,$base_room_value);
                }
                else {

                    $total_base_room_result = array();

                    break;
                }
            }

            $sub_room_result = array();
            $i=0;
            foreach ($period as $key => $value) {

                $subRoom = $this->db->query('
                                                SELECT P.description , R.rate_name , U.separate_date , R.uniq_id , U.room_id , U.rate_types_id , U.price , U.minimum_stay , R.price as base_price , P.image , P.property_name , P.member_count , P.children , P.number_of_bedrooms FROM '.RATE_BASE.' U JOIN '.TBL_PROPERTY.' P ON U.room_id = P.property_id JOIN '.RATE_TYPES.' R ON U.room_id = R.room_id WHERE U.separate_date="'.$value->format("d/m/Y").'" AND U.availability >="'.$rooms.'" AND U.minimum_stay <= "'.$nights.'" AND P.member_count >="'.$adult.'" AND P.children >="'.$child.'" AND individual_channel_id =0 AND stop_sell=0 AND P.owner_id="'.$user_id.'" AND P.hotel_id="'.hotel_id().'" GROUP BY U.room_id ORDER BY U.room_id DESC'
                                             );
                if($subRoom->num_rows != 0) {

                    $sub_room_value =  $subRoom->result_array();

                    $total_sub_room_result[$i++]= array_merge($sub_room_result,$sub_room_value);
                }
                else {

                    $total_sub_room_result = array();

                    break;
                } 
            }
            if(count(@$total_base_room_result)!=0 || count(@$total_sub_room_result)!=0)
            {
				$final_room_result = array_merge($total_base_room_result,$total_sub_room_result);

				if(count($final_room_result)!=0)
				{
					return $final_room_result;
				}
				else
				{
					return false;
				}
            }
			else
			{
				return false;
			}
        }
		else
		{
			return false;
		}
    }
	
	function save_reservation($transaction_id)
    {
         /*echo '<pre>';
        print_r($_GET);        
        die; */

        $Payment_Type =  $_GET['payment_type'];

		if(isset($_GET['guestmail']))
		{
			$guestmail 	=	$_GET['guestmail'];
		}
		else
		{
			$guestmail 	= 	'';
		}
        $start_date		=	$_GET['date1'];
		
        $end_date 		=	$_GET['date2'];
		
        $checkin_date	=	str_replace("/","-",$_GET['date1']);
		
        $checkout_date	=	str_replace("/","-",$_GET['date2']);
		
        $start 			= 	strtotime($checkin_date);
		
        $end 			= 	strtotime($checkout_date);

        $nights 		=	ceil(abs($end - $start) / 86400);
		
        $rooms 			=	$_GET['numrooms'];
		
        $adult 			=	$_GET['numpersons'];
		
        $child 			=	$_GET['numchilds'];
		
        $email 			=	$_GET['email'];
		
        $first_name 	=	$_GET['first_name'];
		
        $last_name 		=	$_GET['first_name'];
		
        $phone 			= 	$_GET['phone'];
		
        $room_id 		= 	$_GET['room_id'];
		
        $rate_type_id	= 	$_GET['rate_type_id'];
		
        $notes   		= 	$_GET['notes'];
		
        $street_name	= 	$_GET['street_name'];
		
        $country		= 	$_GET['country'];
		
        $province		= 	$_GET['province'];
		
        $city_name		= 	$_GET['city_name'];
		
        $zipcode		= 	$_GET['zipcode'];
		
        $get_numrows	=	$this->db->query('SELECT * FROM manage_reservation');

		
        $reservation_code	=	sprintf('%08d',$get_numrows->num_rows()+100);
        

        $price 			=	$_GET['oringinal_price'];

		if(user_type()=='1' || user_type()=='2' && in_array('2',user_edit()) || admin_id()!='' && admin_type()=='1')
		{
			$user_id=current_user_type();
		}
		
        $R_taxes = get_data(TAX,array('user_id'=>$user_id,'hotel_id'=>hotel_id()))->result_array();
		
        if(count($R_taxes)!=0)
        {
            foreach($R_taxes as $valuue)
            {
                extract($valuue);
				
                $t_data['user_id']			=	$user_id;
				
                $t_data['hotel_id']			=	hotel_id();
				
                $t_data['reservation_id']	=	$reservation_code;
				
                $t_data['tax_name'] 		=	$user_name;
				
                $t_data['tax_included'] 	=	$included_price;
				
                $t_data['tax_price'] 		=	$tax_rate;
				
                insert_data(R_TAX,$t_data);
            }
        }

        $room_count=$rooms;
		
        $this->db->set('reservation', 'reservation+'.$room_count.'', FALSE);
		
        $this->db->where('separate_date >=',$start_date); 
		
        $this->db->where('separate_date <=',$end_date); 
		
        $this->db->where('room_id',$room_id); 
		
        $this->db->update('room_update');

		//$end_date		=	date('d/m/Y', strtotime('+1 day', strtotime(date('Y-m-d',strtotime(str_replace("/","-",$end_date))))));
		
		$startDate 		=	DateTime::createFromFormat("d/m/Y",$start_date);

		$endDate 		=	DateTime::createFromFormat("d/m/Y",$end_date);

		$periodInterval = 	new DateInterval("P1D");

		$period 		=	 new DatePeriod( $startDate, $periodInterval, $endDate );
		
		foreach($period as $date)
		{
			if($room_id!='' && $rate_type_id=='')
			{
				$get_available = get_data(TBL_UPDATE,array('owner_id'=>$user_id,'hotel_id'=>hotel_id(),'room_id'=>$room_id,'individual_channel_id'=>0,'separate_date'=>$date->format("d/m/Y")),'room_update_id,availability')->row();
			}
			else if($room_id!='' && $rate_type_id!='')
			{
				$get_available = get_data(RATE_BASE,array('owner_id'=>$user_id,'hotel_id'=>hotel_id(),'room_id'=>$room_id,'rate_types_id'=>$rate_type_id,'individual_channel_id'=>0,'separate_date'=>$date->format("d/m/Y")),'room_update_id,availability')->row();
			}
			/* echo $this->db->last_query(); */
			$availability	=	$get_available->availability; 
			
			$room_update_id	=	$get_available->room_update_id; 
			
			
			/* echo $this->db->last_query();
			
			print_r($get_available);
			
			echo ' room_update_id = '.$get_available->room_update_id;
			
			die; */
			$diff = $room_count * 1;
			
			/*if($get_available->availability == '1')
			{
				$upaval['availability'] = '0';
			}
			else
			{
				$upaval['availability'] = $get_available->availability - $diff ;
			}*/
			
			$upaval['availability'] = $availability - $diff ;
			
			$upaval['trigger_cal'] = '1';

            if(intval($upaval['availability']) < 1){
                $upaval['stop_sell'] = 1;
            }
			
			if($room_id!='' && $rate_type_id=='')
			{
				update_data(TBL_UPDATE,$upaval,array('owner_id'=>$user_id,'hotel_id'=>hotel_id(),'individual_channel_id'=> 0,'room_id'=>$room_id,'separate_date'=>$date->format("d/m/Y")));
			}
			else if($room_id!='' && $rate_type_id!='')
			{
				update_data(RATE_BASE,$upaval,array('owner_id'=>$user_id,'hotel_id'=>hotel_id(),'individual_channel_id'=> 0,'room_id'=>$room_id,'rate_types_id'=>$rate_type_id,'separate_date'=>$date->format("d/m/Y")));
			}

			$this->db->where('room_update_id !=',$room_update_id);
			
			$this->db->where('owner_id',$user_id);
			
			$this->db->where('hotel_id',hotel_id());
			
			$this->db->where('room_id',$room_id);
			
			$this->db->where('separate_date',$date->format("d/m/Y"));
			
			if($room_id!='' && $rate_type_id!='')
			{
				$this->db->where('rate_types_id',$rate_type_id);
			}
			
			// ALTER TABLE `manage_reservation` ADD `rate_types_id` INT NOT NULL DEFAULT '0' AFTER `room_id`;
			
			//$this->db->set('availability','availability - '.$diff,false);
			$this->db->set('availability','CASE WHEN availability - '.$diff.' >=0 THEN availability-'.$diff.' WHEN availability-'.$diff.' < 0 AND individual_channel_id = 0 THEN availability-'.$diff.' WHEN availability-'.$diff.' < 0 AND individual_channel_id = 0 THEN 0 END' ,false);

			if($room_id!='' && $rate_type_id!='')
			{
				$this->db->update(RATE_BASE);
			}
			else if($room_id!='' && $rate_type_id==='')
			{
				$this->db->update(TBL_UPDATE);
			}

			$channel['channel_id'] = 0;
			
			$channel['property_id'] = $room_id;
			
			$channel['rate_id'] = 0;
			
			$channel['guest_count'] = 0;
			
			$channel['refun_type'] = 0;
			
			$channel['start'] = $start_date;
			
			$channel['end'] = $end_date;

			$roomMappingDetails =   get_data(MAP,array('property_id' => $room_id,'owner_id'=>$user_id))->row_array();

			if(count($roomMappingDetails)!=0)
			{
			
				require_once(APPPATH.'controllers/mapping.php'); 
				
				$callAvailabilities = new Mapping();
				
				$callAvailabilities->update_channel($user_id,hotel_id(),$upaval,$channel,$date->format("d/m/Y"),$mapping_id = "","manual");
			}
		}

         /*  bank details start */
			if($Payment_Type=='bt')
			{
				$bank_id      = $_GET['bank_type'];

				$bank_details = get_data('bank_details',array('bank_id'=>$bank_id))->row();

				///extract($bank_details);

				$badata['account_owner'] = $bank_details->account_owner;
				$badata['currency'] = $bank_details->currency;
				$badata['bank_name'] = $bank_details->bank_name;
				$badata['branch_name'] = $bank_details->branch_name;
				$badata['branch_code'] = $bank_details->branch_code;
				$badata['swift_code'] = $bank_details->swift_code;
				$badata['iban'] = $bank_details->iban;
				$badata['account_number'] = $bank_details->account_number;

				$reference = mt_rand(1000000,99999999);

				$reference_code = $reference;            

				$bank_deta = json_encode($badata);
			}

        // $update_bankdetails = $this->reservation_model->save_reservation($reference_code,$bank_deta);            
            // cash
           if($Payment_Type=='bt')
           {    
                $reference_code = $reference_code;
           }
           else
           {
                 $reference_code = '';
           }
           if($Payment_Type=='bt')
           {
                $bank_deta = $bank_deta;
           }
           else
           {
                $bank_deta = '';
           }

        /*   bank details end  */
        
        $data	=	array(
							'reservation_code'=>$reservation_code,
							
							'hotel_id'=>hotel_id(),
							
							'user_id'=>$user_id,
							
							'guest_name'=>$first_name,
							
							'last_name'=>$last_name,
							
							'mobile'=>$phone,
							
							'email'=>$email,
							
							'room_id'=>$room_id,
							
							'rate_types_id'=>$rate_type_id,
							
							'num_nights'=>$nights,
							
							'num_rooms' => $room_count,
							
							'members_count'=>$adult,
							
							'children'=>$child,
							
							'street_name'=>$street_name,
							
							'country'=>$country,
							
							'province'=>$province,
							
							'city_name'=>$city_name,
							
							'zipcode'=>$zipcode,
														
							'start_date'=>$start_date,
							
							'end_date'=>$end_date,
							
							'booking_date'=>date('Y-m-d'),
							
							'price'=>$price,
							
							'description'=>$notes,
							
							'price_details'=>insep_decode($_GET['price_day']),
							
							'payment_method'=>$_GET['payment_type'],
							
							'transaction_id'=>$transaction_id,

                            'reference_code'=>$reference_code,

                            'bank_details'=>$bank_deta,
							
							'currency_id'=>get_data(TBL_CUR,array('currency_id'=>get_data(HOTEL,array('owner_id'=>$user_id,'hotel_id'=>hotel_id()))->row()->currency))->row()->currency_id,
						);

        /*echo '<pre>';
        print_r($data);die;*/

      
        if(insert_data('manage_reservation',$data))
        {   
			$id =  $this->db->insert_id();
            $exp_month=$_GET['exp_month'];
            $exp_year=$_GET['exp_year'];
            $card_number=$_GET['card_number'];
            $card_type=$_GET['card_type'];
            $card_name=$_GET['card_name'];
			if($exp_month!='' && $exp_year!='' && $card_number!='' && $card_type!='' && $card_name!='')
			{
				$card=array(
					'exp_month'=>(string)safe_b64encode($exp_month),
					'name'=>(string)safe_b64encode($card_name),
					'card_type' => (string)safe_b64encode($card_type),
					'securitycode' => (string)safe_b64encode($_GET['security_code']),
					'exp_year'=>(string)safe_b64encode($exp_year),
					'number'=>(string)safe_b64encode($card_number),
					'user_id'=>$user_id,
					'resrv_id'=>$id,
				);
				$this->db->insert('card_details',$card);
			}
        
        $save_note = array('type'=>'3','created_date'=>date('Y-m-d H:i:s'),'status'=>'unseen','reservation_id'=>$id,'user_id'=>$user_id,'hotel_id'=>hotel_id());

        $ver = $this->db->insert('notifications',$save_note);
        
        
        $property_details = get_data(TBL_USERS,array('user_id'=>get_data('manage_reservation',array('reservation_id'=>$id))->row()->user_id))->row();
        
        $cancel_details = get_data(PCANCEL,array('user_id'=>$user_id,'hotel_id'=>hotel_id()))->row();  
        
        $other_details = get_data(POTHERS,array('user_id'=>$user_id,'hotel_id'=>hotel_id()))->row();
        
        $propertyname = get_data(HOTEL,array('owner_id' => $user_id,'hotel_id'=>hotel_id()))->row()->property_name;
        
        if($other_details->smoking==1)
		{
			$smoke = 'Smoking is allowed';
		}
		else if($other_details->smoking==0)
		{
			$smoke = 'Smoking is not allowed.';
		}

		if($other_details->pets==1)
		{
			$pets = 'Pets are allowed';
		}
		else if($other_details->pets==0)
		{
			$pets = 'No pets allowed';
		}

		if($other_details->valet_parking==1)
		{
			$valet_parking = 'Valet parking is allowed';
		}
		else if($other_details->valet_parking==0)
		{
			$valet_parking = 'Valet parking is not allowed.';
		}

		if($other_details->child_pricing==1)
		{
			$child_pricing = 'Pets child pricing allowed';
		}
		else if($other_details->child_pricing==0)
		{
			$child_pricing = 'No child pricing allowed';
		}
        
        $admin_detail = get_data(TBL_SITE,array('id'=>1))->row();

        
        $get_email_info     =   get_mail_template('12');

        $subject            =   $get_email_info['subject'];

        $template           =   $get_email_info['message'];
        
        
        $get_email_info1   	=   get_mail_template('11');

        $subject1          	=   $get_email_info1['subject'];

        $template1         	=   $get_email_info1['message'];


		if($Payment_Type=='bt')
		{

			$get_bank_Details = get_data('manage_reservation',array('reservation_id'=>$id))->row();
		   /* echo '<pre>';
			print_r($get_bank_Details);die;*/

			$Reference_code = $get_bank_Details->reference_code;

			$bank_details = json_decode($get_bank_Details->bank_details);

           /* echo $refer;
            echo '<pre>';           
            print_r($bank_details);*/

            $account_owner = $bank_details->account_owner;
            $bank_name = $bank_details->bank_name;
            $branch_name = $bank_details->branch_name;
            $account_number = $bank_details->account_number;
            $iban = $bank_details->iban;

                
                    $tbl_data1 = '<div class="row">
                <div class="co-md-12 col-sm-12">
                <table class="summaryTable">

                <tbody>

                <tr>

                <th>

                Name

                </th>

                <td>

                <b>'.ucfirst($first_name).'</b>

                </td>

                </tr>
                <tr>

                <th>

               Reference Code

                </th>

                <td>

                <b>'.$Reference_code.'</b>

                </td>

                </tr>
                <tr>

                <th>

                Account Owner

                </th>

                <td>

                <b>'.ucfirst($account_owner).'</b>

                </td>

                </tr>

                <tr>

                <tr>
                <th>
               Bank Name
                </th>
                <td>
                '.$bank_name.'
                </td>
                </tr>

                <th>

                Branch Name

                </th>

                <td>

                '.$branch_name.'

                </td>

                </tr>

                <tr>

                <th>

                Account Nnumber

                </th>

                <td>

                '.$account_number.'

                </td>

                </tr>  

                 <tr>

                <th>

                IFSC Code

                </th>

                <td>

                '.$iban.'

                </td>

                </tr>            

                </tbody>

                </table>

                

                
                </div>

                </div>';
                }
                else
                {
                $tbl_data1 = '<div class="row">
                <div class="co-md-12 col-sm-12">
                <table class="summaryTable">

                <tbody>

                <tr>

                <th>

                Hotel Name

                </th>

                <td>

                <b>'.ucfirst($property_details->property_name).'</b>

                </td>

                </tr>
                <tr>

                <th>

                Confirmation number 

                </th>

                <td>

                <b>'.$reservation_code.'</b>

                </td>

                </tr>
                <tr>

                <th>

                Guest Name 

                </th>

                <td>

                <b>'.ucfirst($first_name).'</b>

                </td>

                </tr>

                <tr>

                <tr>
                <th>
                No.of Rooms
                </th>
                <td>
                '.$rooms.'
                </td>
                </tr>

                <th>

                Check-in date

                </th>

                <td>

                '.$start_date.'

                </td>

                </tr>

                <tr>

                <th>

                Check-out date

                </th>

                <td>

                '.$end_date.'

                </td>

                </tr>



                <tr>

                <th>

                No.of Nights

                </th>

                <td>

                '.$nights.'

                </td>

                </tr>

                <tr>

                <th>

                Order Total

                </th>

                <td>

                '.$price*$rooms.'

                </td>

                </tr>

                <tr>

                <th>

                Adult Count

                </th>

                <td>

                '.$adult.'

                </td>

                </tr>

                <tr>

                <th>

                Child Count

                </th>

                <td>

                '.$child.'

                </td>

                </tr>

                </tbody>

                </table>

                <h3>Hotel Policies</h3>

                <table class="summaryTable">

                <tbody>

                <tr>

                <th>Cancellation</th>

                <td>

                '.$cancel_details->description.'


                </td>

                </tr>

                <tr>

                <th>Check-in time</th>

                <td>After '.$other_details->check_in_time.' day of arrival.</td>

                </tr>

                <tr>

                <th>Check-out time</th>

                <td>'.$other_details->check_out_time.' upon day of departure.</td>

                </tr>

                <tr>

                <th>Valet parking</th>

                <td>'.$valet_parking.'.</td>

                </tr>

                <tr>

                <th>Smoking</th>

                <td>'.$smoke.'.</td>

                </tr>

                <tr>

                <th>Pets</th>

                <td>'.$pets.'</td>

                </tr>

                <tr>

                <th>Child pricing</th>

                <td>'.$child_pricing.'</td>

                </tr>';
					
				$new_policy_details = get_data(PADD,array('user_id'=>$user_id,'hotel_id'=>hotel_id()))->result();
				
                if($new_policy_details!='')
                {         
					foreach($new_policy_details as $new_policy)
					{ 
						$tbl_data1.=  '<tr>

						<th>'.$new_policy->policy_name.'</th>

						<td>'.$new_policy->description.'</td>

						</tr>';

					} 
				}

                '</tbody>

                </table>
                </div>

                </div>';  

                /*echo '<pre>';
                print_r($tbl_data1);die;*/

                $tbl_data = '<div class="row">

                <div class="co-md-12 col-sm-12">
                <table class="summaryTable">

                <tbody>

                <tr>

                <th>

                Hotel Name

                </th>

                <td>

                <b>'.ucfirst($propertyname).'</b>

                </td>

                </tr>
                <tr>

                <th>

                Confirmation number 

                </th>

                <td>

                <b>'.$reservation_code.'</b>

                </td>

                </tr>

                <tr>
                <th>
                No.of Rooms
                </th>
                <td>
                '.$rooms.'
                </td>
                </tr>

                <tr>

                <th>

                Guest Name 

                </th>

                <td>

                <b>'.ucfirst($first_name).'</b>

                </td>

                </tr>

                <tr>

                <th>



                Check-in date

                </th>

                <td>

                '.$start_date.'

                </td>

                </tr>

                <tr>

                <th>

                Check-out date

                </th>

                <td>

                '.$end_date.'

                </td>

                </tr>



                <tr>

                <th>

                No.of Nights

                </th>

                <td>

                '.$nights.'

                </td>

                </tr>

                <tr>

                <th>

                Order Total

                </th>

                <td>

                '.$price*$rooms.'

                </td>

                </tr>

                <tr>

                <th>

                Adult Count

                </th>

                <td>

                '.$adult.'

                </td>

                </tr>
                <tr>

                <th>

                Child Count

                </th>

                <td>

                '.$child.'

                </td>

                </tr>


                </tbody>

                </table>

                </div>

                </div>'; 
                }

  /* echo '<pre>';
   print_r($tbl_data1);die;*/

   if($Payment_Type!='bt'){

    $data = array(

                '###COMPANYLOGO###'=>base_url().'uploads/logo/'.$admin_detail->site_logo,

                '###SITENAME###'=>$admin_detail->company_name,

                '###CONFIRMRESERVATION###'=>$tbl_data,
                
                '###SITELINK###'=>base_url(),
                
                '###RESERLINK###'=>base_url().'reservation/reservation_print/'.secure(0).'/'.insep_encode($id),
                
                '###CONFIRMLINK###'=>base_url().'reservation/admin_confirm/'.insep_encode($id),

                );
 
  }
    
    $data1 = array(

               // '###USERNAME###'=>$first_name.' '.$last_name,
                '###USERNAME###'=>$first_name,

                '###COMPANYLOGO###'=>base_url().'uploads/logo/'.$admin_detail->site_logo,

                '###SITENAME###'=>$admin_detail->company_name,

                '###STATUS###'=>'Reserved',

                '###PROPERTYUSER###'=>$propertyname,
                
                '###CONFIRMRESERVATION###'=>$tbl_data1,
                
                '###SITELINK###'=>base_url(),
                
                '###RESERLINK###'=>base_url().'reservation/reservation_print/'.secure(0).'/'.insep_encode($id),

                );

    $subject_data = array(
                    '###PROPERTYUSER###'=>$propertyname,
                    '{RESERVATIONCODE}'=>$reservation_code,);   

        $subject_data1 = array(
                    '###SITENAME###'=>$admin_detail->company_name,
                    '{RESERVATIONCODE}'=>$reservation_code,
            );

            $subject_new1 = strtr($subject1,$subject_data1);

            $content_pop1 = strtr($template1,$data1);
            
            $subject_new = strtr($subject,$subject_data);


            $content_pop = strtr($template,$data);
        
            /*print_r($content_pop1);
            
            print_r($content_pop);
            
            exit;*/
            $this->mailsettings();

			if($guestmail!='')
			{
				$this->email->from($admin_detail->email_id);

				$this->email->to($email);

				$this->email->subject($subject_new1);

				$this->email->message($content_pop1);

				$this->email->send();
			}

			$this->email->from($admin_detail->email_id);

			//$this->email->to($admin_detail->acc_email);

			$this->email->to(get_data(HOTEL,array('hotel_id'=>hotel_id()))->row()->email_address);

			$this->email->subject($subject_new);

			$this->email->message($content_pop);

			$this->email->send();
            return $id;
    }
        else
        {  
         return false;
        }
            
    }
    
    function check_availdate($roomid)
	{
        if(user_type()=='1' || user_type()=='2' && in_array('2',user_edit()) || admin_id()!='' && admin_type()=='1')
		{
			$user_id=current_user_type();
		}
        $start_date = $this->input->get('dp1');//$_REQUEST['dp1'];
        $end_date = $this->input->get('dp2');//$_REQUEST['dp2'];
        $rooms = $this->input->get('num_rooms');//$_REQUEST['num_rooms'];
        $adult = $this->input->get('num_person');//$_REQUEST['num_person'];
        //echo 'SELECT U.room_id FROM '.TBL_UPDATE.' U JOIN '.TBL_PROPERTY.' P  WHERE U.room_id="'.$roomid.'" AND str_to_date(U.separate_date, "%d/%m/%Y") between str_to_date("'.$start_date.'", "%d/%m/%Y") AND str_to_date("'.$end_date.'", "%d/%m/%Y") AND U.availability="-1"  AND P.owner_id="'.$user_id.'" GROUP BY U.room_id ORDER BY U.room_id DESC';
        $det=$this->db->query('SELECT U.room_id FROM '.TBL_UPDATE.' U JOIN '.TBL_PROPERTY.' P  WHERE U.room_id="'.$roomid.'" AND str_to_date(U.separate_date, "%d/%m/%Y") between str_to_date("'.$start_date.'", "%d/%m/%Y") AND str_to_date("'.$end_date.'", "%d/%m/%Y") AND U.availability="-1"  AND P.owner_id="'.$user_id.'" GROUP BY U.room_id ORDER BY U.room_id DESC');  
		
		$det = $this->db->query('
									SELECT U.room_id FROM '.TBL_UPDATE.' U JOIN '.TBL_PROPERTY.' P ON U.room_id = P.property_id 
									WHERE str_to_date(U.separate_date, "%d/%m/%Y") between str_to_date("'.$start_date.'", "%d/%m/%Y") AND str_to_date("'.$end_date.'", "%d/%m/%Y") AND U.availability >=U.reservation AND U.availability >="'.$rooms.'" AND U.minimum_stay <= "'.$nights.'" AND P.owner_id="'.$user_id.'" AND P.hotel_id="'.hotel_id().'" GROUP BY U.room_id ORDER BY U.room_id DESC'
								);
								
        if($det->num_rows > 0)
        {
            return "ok";        
        }
        else
        {
            return "no";
        }
    }
    
    function get_room(){
    $start_date = $this->input->post('start_date');
    $end_date = $this->input->post('end_date');
    $rooms = $this->input->post('room');
    $adult = $this->input->post('adult');
    $user_id=current_user_type();
    $this->db->where('availability >=reservation');
    $this->db->where('separate_date >=',$start_date);   
    $this->db->where('separate_date <=',$end_date);     
    $this->db->where('availability >=',$rooms);      
    $this->db->where('minimum_stay >=',$adult); 
    $this->db->order_by('room_id','desc');  
    $det = $this->db->get('room_update');
    if($det->num_rows >0){
        return $det->result();      
    }   
        return false;
    }
    function get_room_details($id="")
	{
        if(user_type()=='1' || user_type()=='2' && in_array('2',user_edit()) || admin_id()!='' && admin_type()=='1')
		{
			$user_id=current_user_type();
		}
        if($id!="")
		{
            $this->db->where('property_id',$id);
            $this->db->where('owner_id',$user_id);
            $query = $this->db->get('manage_property');
        }
		else
		{
            $this->db->where('owner_id',$user_id);
            $query = $this->db->get('manage_property');
        }
        if($query->num_rows>0)
		{
            return $query->result();
        }
		else
		{
            return false;
        }
    }
    function get_reservation_details($id=""){
        if($id!=""){
            $this->db->where('reservation_id',$id);
            $query = $this->db->get('manage_reservation');
        }else{
            $query = $this->db->get('manage_reservation');

        }
        if($query->num_rows>0){
            return $query->result();
        }else{
            return false;
        }
    }
    
    function total_tax_reservation($channel_id,$reservation_id)
    {
    
        if(unsecure($channel_id)==0)
        {
            $reservation_code = get_data(RESERVATION,array('reservation_id'=>$reservation_id))->row()->reservation_code;
        }
        elseif(unsecure($channel_id)==11)
        {
            $reservation_code = get_data(REC_RESERV,array('import_reserv_id'=>$reservation_id))->row()->IDRSV;
        }
        elseif(unsecure($channel_id)==1)
        {
            $reservation_code = get_data('import_reservation_EXPEDIA',array('import_reserv_id'=>$reservation_id))->row()->booking_id;
        }
        elseif(unsecure($channel_id)==5)
        {
            $reservation_code = get_data('import_reservation_HOTELBEDS',array('import_reserv_id'=>$reservation_id))->row()->echoToken;
        }
        elseif(unsecure($channel_id)==2)
        {
            $reservation_code = get_data('import_reservation_BOOKING_ROOMS',array('room_res_id'=>$reservation_id))->row()->roomreservation_id;
        }
		elseif(unsecure($channel_id)==17)
        {
            $reservation_code = get_data(BNOW_RESER,array('import_reserv_id'=>$reservation_id))->row()->ResID_Value;
        }
        elseif(unsecure($channel_id)==15)
        {
            $reservation_code = get_data(IM_TRAVEL_RESER,array('import_reserv_id'=>$reservation_id))->row()->BookingId;
        }
        elseif(unsecure($channel_id)==36)
        {
            $reservation_code = get_data('import_reservation_DESPEGAR',array('Import_reservation_ID'=>$reservation_id))->row()->ResID_Value;
        }
        $count = $this->db->select('reserv_tax_id')->from(R_TAX)->
        where(array('reservation_id'=>$reservation_code,'channel_id'=>unsecure($channel_id)))->count_all_results();
        return $count;
    }
    function get_reservation_price_old($room_id='',$ptype='',$sdate='',$edate='',$guest=''){
            $this->db->where('separate_date >=',$sdate);    
            $this->db->where('separate_date <=',$edate);    
          if($ptype=="2")
            {
            $this->db->where('guest_count',$guest); 
            }  
            $query = $this->db->get('reservation_table');
               if($query->num_rows>0)
               {
                 return $query->result();
               }
               else
               {
                 return false;
               }
           }
           
    function get_reservation_price($room_id='',$ptype='',$sdate='',$edate='',$guest=''){
        
        if($ptype=="2")
        {   
            $query=$this->db->query('SELECT * FROM `reservation_table` WHERE `room_id` = "'.$room_id.'" AND str_to_date(`separate_date`, "%d/%m/%Y") between str_to_date("'.$sdate.'", "%d/%m/%Y") AND str_to_date("'.$edate.'", "%d/%m/%Y") AND `guest_count` >="'.$guest.'"');
        }
        else
        {
                $query=$this->db->query('SELECT * FROM `reservation_table` WHERE `room_id` = "'.$room_id.'" AND str_to_date(`separate_date`, "%d/%m/%Y") between str_to_date("'.$sdate.'", "%d/%m/%Y") AND str_to_date("'.$edate.'", "%d/%m/%Y")');
        }
           /* $this->db->where('separate_date >=',$sdate);  
            $this->db->where('separate_date <=',$edate);    
          if($ptype=="2"){
            $this->db->where('guest_count',$guest); 
            } 
            $query = $this->db->get('reservation_table');*/ 
               if($query->num_rows>0){
                return $query->result();
               }else{
                return false;
               }
           }
    /*Stop Thirupathi*/

    /*Start testing */
        
    function get_currency_test(){
        // echo 'sdfdf'; die;
        $from = 'USD';
        $to = 'INR';
        $amount = 300;  
        $content = file_get_contents('https://www.google.com/finance/converter?a='.$amount.'&from='.$from.'&to='.$to);
        $doc = new DOMDocument;
        @$doc->loadHTML($content);
        $xpath = new DOMXpath($doc);   
        $result = $xpath->query('//*[@id="currency_converter_result"]/span')->item(0)->nodeValue;
        echo str_replace(' '.$to, '', $result); 
    }
    
    function reservationcounts($type='',$param='')
    {
        $date = date('d/m/Y');
        $bdate = date('Y-m-d');
		if($param=='')
		{
			$hotel_id = hotel_id();
		}
		else if($param!='')
		{
			$hotel_id = insep_decode($param);
		}
			
        if($type=='reserve')
        {
            $det=$this->db->query('SELECT * FROM `manage_reservation` WHERE `booking_date`="'.$bdate.'" AND hotel_id='.$hotel_id.' AND user_status="Booking" AND status="Reserved"');
            $manaul_new =  $det->num_rows();
            return $manaul_new + all_reservation_count($type,$bdate,$hotel_id);
        }
        else if($type=='cancel')
        {
            $det=$this->db->query('SELECT * FROM `manage_reservation` WHERE `modified_date`="'.$bdate.'" AND hotel_id='.$hotel_id.' AND status="Canceled"');
            $manaul_cancel = $det->num_rows();
            return $manaul_cancel+all_reservation_count($type,$bdate,$hotel_id);
        }
        else if($type=='arrival')
        {
            $det=$this->db->query('SELECT * FROM `manage_reservation` WHERE str_to_date(`start_date`, "%d/%m/%Y") = str_to_date("'.$date.'", "%d/%m/%Y") AND hotel_id='.$hotel_id.' AND user_status="Arrival"');
            $manaul_arr = $det->num_rows();
            return $manaul_arr + all_reservation_count($type,$bdate,$hotel_id);
        }
        else if($type=='depature')
        {
            $det=$this->db->query('SELECT * FROM `manage_reservation` WHERE str_to_date(`end_date`, "%d/%m/%Y") = str_to_date("'.$date.'", "%d/%m/%Y") AND hotel_id='.$hotel_id.' AND user_status="Departure"');
            $manaul_dep = $det->num_rows();
            return $manaul_dep + all_reservation_count($type,$bdate,$hotel_id);
        }
		else if($type=='modify')
        {
            $det=$this->db->query('SELECT * FROM `manage_reservation` WHERE str_to_date(`end_date`, "%d/%m/%Y") = str_to_date("'.$date.'", "%d/%m/%Y") AND hotel_id='.$hotel_id.' AND user_status="Departure"');
            $manaul_modify = $det->num_rows();
            return $manaul_modify + all_reservation_count($type,$bdate,$hotel_id);
        }
    }

    
    
    function get_count_room($type,$param='',$param2='')
    {
		if($param=='')
		{
			$hotel_id = hotel_id();
		}
		else if($param!='')
		{
			$hotel_id = insep_decode($param);
		}
		if($param2=='')
		{
			$user_id = current_user_type();
		}
		else if($param2!='')
		{
			$user_id = insep_decode($param2);
		}
		if($type=='today')
        {
			$det=$this->db->query('SELECT SUM(R.availability) as availability FROM `'.TBL_UPDATE.'` R JOIN manage_property P ON R.room_id = P.property_id WHERE	DATE_FORMAT(STR_TO_DATE(R.separate_date,"%d/%m/%Y"),"%Y-%m-%d") = CURDATE() AND R.individual_channel_id="0" AND R.hotel_id='.$hotel_id.' AND R.owner_id='.$user_id.' AND P.status="Active" ORDER BY P.property_id DESC');
			$manaul_rooms_today =  $det->row();
			return $manaul_rooms_today->availability;
        }
        else if($type=='week')
        {
			$det=$this->db->query('SELECT SUM(R.availability) as availability FROM `'.TBL_UPDATE.'` R JOIN manage_property P ON R.room_id = P.property_id WHERE	( DATE_FORMAT(STR_TO_DATE(R.separate_date,"%d/%m/%Y"),"%Y-%m-%d") >= CURDATE() AND DATE_FORMAT(STR_TO_DATE(R.separate_date,"%d/%m/%Y"),"%Y-%m-%d") <= ADDDATE(CURDATE(), INTERVAL 7 DAY) )AND R.individual_channel_id="0" AND R.hotel_id='.$hotel_id.' AND R.owner_id='.$user_id.' AND P.status="Active" ORDER BY P.property_id DESC');
            $manaul_rooms_week =  $det->row();
            return $manaul_rooms_week->availability;
        }
        else if($type=='month')
        {
			$det=$this->db->query('SELECT SUM(R.availability) as availability FROM `'.TBL_UPDATE.'` R JOIN manage_property P ON R.room_id = P.property_id WHERE	( DATE_FORMAT(STR_TO_DATE(R.separate_date,"%d/%m/%Y"),"%Y-%m-%d") >= CURDATE() AND DATE_FORMAT(STR_TO_DATE(R.separate_date,"%d/%m/%Y"),"%Y-%m-%d") <= ADDDATE(CURDATE(), INTERVAL 30 DAY) )AND R.individual_channel_id="0" AND R.hotel_id='.$hotel_id.' AND R.owner_id='.$user_id.' AND P.status="Active" ORDER BY P.property_id DESC');
            $manaul_rooms_month =  $det->row();
            return $manaul_rooms_month->availability;
        }
		/* echo $this->db->last_query(); */
    }
    
    function confirmed_reserve($type='',$param='',$param2='')
    {
		if($param=='')
		{
			$hotel_id = hotel_id();
		}
		else if($param!='')
		{
			$hotel_id = insep_decode($param);
		}
		if($param2=='')
		{
			$user_id = current_user_type();
		}
		else if($param2!='')
		{
			$user_id = insep_decode($param2);
		}
		
        if($type=='today')
        {
			$det=$this->db->query("SELECT reservation_id FROM `".RESERVATION."` WHERE ( (DATE_FORMAT(STR_TO_DATE(start_date,'%d/%m/%Y'),'%Y-%m-%d') >= CURDATE() AND DATE_FORMAT(STR_TO_DATE(start_date,'%d/%m/%Y'),'%Y-%m-%d') <= CURDATE()) OR ( (DATE_FORMAT(STR_TO_DATE(end_date,'%d/%m/%Y'),'%Y-%m-%d') > CURDATE() AND DATE_FORMAT(STR_TO_DATE(end_date,'%d/%m/%Y'),'%Y-%m-%d') <= CURDATE() ) ) ) AND user_id =".$user_id." AND hotel_id = ".$hotel_id." AND status NOT IN('Canceled') ORDER BY reservation_id DESC");
            $manaul_rooms_today =  $det->num_rows();
            return $manaul_rooms_today + all_confirmed_reserve($type,$hotel_id,$user_id);
		}
        else if($type=='week')
        {
			$det=$this->db->query("SELECT reservation_id FROM `".RESERVATION."` WHERE ( (DATE_FORMAT(STR_TO_DATE(start_date,'%d/%m/%Y'),'%Y-%m-%d') >= CURDATE() AND DATE_FORMAT(STR_TO_DATE(start_date,'%d/%m/%Y'),'%Y-%m-%d') <= ADDDATE(CURDATE(), INTERVAL 7 DAY)) OR ( (DATE_FORMAT(STR_TO_DATE(end_date,'%d/%m/%Y'),'%Y-%m-%d') > CURDATE() AND DATE_FORMAT(STR_TO_DATE(end_date,'%d/%m/%Y'),'%Y-%m-%d') <= ADDDATE(CURDATE(), INTERVAL 7 DAY) ) ) )AND user_id =".$user_id." AND hotel_id = ".$hotel_id." AND status NOT IN('Canceled') ORDER BY reservation_id DESC");
            $manaul_rooms_week =  $det->num_rows();
            return $manaul_rooms_week + all_confirmed_reserve($type,$hotel_id,$user_id);
        }
        else if($type=='month')
        {
			$det=$this->db->query("SELECT reservation_id FROM `".RESERVATION."` WHERE ( (DATE_FORMAT(STR_TO_DATE(start_date,'%d/%m/%Y'),'%Y-%m-%d') >= CURDATE() AND DATE_FORMAT(STR_TO_DATE(start_date,'%d/%m/%Y'),'%Y-%m-%d') <= ADDDATE(CURDATE(), INTERVAL 30 DAY)) OR ( (DATE_FORMAT(STR_TO_DATE(end_date,'%d/%m/%Y'),'%Y-%m-%d') > CURDATE() AND DATE_FORMAT(STR_TO_DATE(end_date,'%d/%m/%Y'),'%Y-%m-%d') <= ADDDATE(CURDATE(), INTERVAL 30 DAY) ) ) )AND user_id =".$user_id." AND hotel_id = ".$hotel_id." AND status NOT IN('Canceled') ORDER BY reservation_id DESC");
            $manaul_rooms_month =  $det->num_rows();
            return $manaul_rooms_month + all_confirmed_reserve($type,$hotel_id,$user_id);
        }
    }
    
    // 03/02/2016..
	function reservation_channel()
	{
		$today = date('d/m/Y');
		$today_date = date('Y-m-d');
		$tomorrow = date('Y-m-d',strtotime($today_date."-30 days"));
		$startDate = DateTime::createFromFormat("Y-m-d",$today);
		$endDate = DateTime::createFromFormat("Y-m-d",$tomorrow);
		$this->db->select('A.auto_id,A.channel_id,A.channel_table_name,A.import_mapping_table,A.fetch_query_count,A.fetch_query_all,A.ical_query,A.reports_query');
		$this->db->join(CONNECT.' as C','A.channel_id=C.channel_id');
		$this->db->where(array('C.user_id'=>user_id(),'C.hotel_id'=>hotel_id()));
		$query = $this->db->get(ALL.' as A');
		if($query)
		{
			$all_api_new_book = $query->result_array();
		}
		$total_confirm_count = array();
		foreach($all_api_new_book as $table_field)
		{
			extract($table_field); 
			if($channel_id==2)
			{
				$hotel_id = 'hotel_hotel_id';
			}
			else
			{
				$hotel_id = 'hotel_id';
			}
			$select = explode(',',$fetch_query_count);
			$ch_details = get_data(CONNECT,array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'channel_id'=>$channel_id))->row();
			$det=$this->db->query('SELECT `channel_id`, COUNT(channel_id) as total FROM `'.$channel_table_name.'` WHERE str_to_date(`'.$select[5].'`, "%Y-%m-%d") <= str_to_date("'.$today_date.'", "%Y-%m-%d") AND str_to_date(`'.$select[6].'`, "%Y-%m-%d") >= str_to_date("'.$tomorrow.'", "%Y-%m-%d") AND '.$hotel_id.'='.hotel_id().' AND user_id='.current_user_type().' AND '.$select[17].'="'.$ch_details->hotel_channel_id.'" AND '.$select[2].'!="'.$select[13].'" GROUP BY `channel_id` ORDER BY `total` desc LIMIT 4');
			$chaConfirmCheckCount =  $det->result_array();
			$total_confirm_count = array_merge($total_confirm_count,$chaConfirmCheckCount);
		}
		uasort($total_confirm_count,function($a,$b)
		{
			return ($a['total'])<($b['total'])?1:-1;
		});
		return $total_confirm_count;
	}

    function channel_names($reser)
    {
       $this->db->where('channel_id',$reser);
       $ver = $this->db->get('manage_channel');
       if($ver->num_rows()>0)
       {
         return $ver->row();
       }
       else
       {
         return false;
       }
    }

    function reservationcounts_old($type='')
    {
        
  $date = date('d/m/Y');
  $newdate = strtotime ( '-1 month' , strtotime ( $date ) ) ;
  $prev_date = date ( 'd/m/Y' , $newdate );
   if($type=='today'){
      $det=$this->db->query('SELECT * FROM `reservation_table` WHERE str_to_date(`separate_date`, "%d/%m/%Y") = str_to_date("'.$date.'", "%d/%m/%Y") AND hotel_id='.hotel_id().'');
      return $det->num_rows();
    }else if($type=='total'){
         $det=$this->db->query('SELECT * FROM `reservation_table` WHERE  hotel_id='.hotel_id().'');
      return $det->num_rows();
    }else if($type=='last30'){
         $det=$this->db->query('SELECT * FROM `reservation_table` WHERE  str_to_date(`separate_date`, "%d/%m/%Y") between str_to_date("'.$prev_date.'", "%d/%m/%Y") AND str_to_date("'.$date.'", "%d/%m/%Y")
      AND hotel_id='.hotel_id().'');
      return $det->num_rows();
    }else if($type=='cancel'){
         $det=$this->db->query('SELECT * FROM `reservation_table` WHERE  str_to_date(`separate_date`, "%d/%m/%Y") between str_to_date("'.$prev_date.'", "%d/%m/%Y") AND str_to_date("'.$date.'", "%d/%m/%Y")
      AND hotel_id='.hotel_id().'');
      return $det->num_rows();
    }
    }
    
    
    
    function add_extras($reser_id)
    {

        $reser_id = insep_decode($reser_id);

        $description = $this->input->post('description');

        $amount = $this->input->post('price');
        
        $curr_cha_id = unsecure($this->input->post('curr_cha_id'));

        $data = array('channel_id'=>$curr_cha_id,'reservation_id'=>$reser_id,'description'=>$description,'amount'=>$amount,'extra_date'=>date('Y-m-d H:i:s'));

        $ver = $this->db->insert('extras',$data);

        $data = array('channel_id'=>$curr_cha_id,'reservation_id'=>$reser_id,'description'=>$description,'amount'=>$amount,'extra_date'=>date('Y-m-d H:i:s'),'history_date'=>date('Y-m-d H:i:s'));

        $res = $this->db->insert('new_history',$data);

        if($ver)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function get_extras_id($extra_id)
    {
        $this->db->where('extra_id',$extra_id);
        
        $ver = $this->db->get('extras');
        
        if($ver->num_rows()==1)
        {
            return $ver->row();
        }
        else
        {
            return false;
        }
    }





    function edit_extras($reser_id)
    {
        $reser_id = insep_decode($reser_id);

        $extra_id = $this->input->post('extra_id');

        $description = $this->input->post('description');

        $amount = $this->input->post('price');
        
        $curr_cha_id = unsecure($this->input->post('curr_cha_id'));

        $data = array('description'=>$description,'amount'=>$amount,'extra_update'=>date('Y-m-d H:i:s'));

        $this->db->where(array('channel_id'=>$curr_cha_id,'extra_id'=>$extra_id));

        $ver = $this->db->update('extras',$data);

        $old_amount = $this->input->post('old_amount');

        $data = array('extra_id'=>$extra_id,'channel_id'=>$curr_cha_id,'reservation_id'=>$reser_id,'description'=>$description,'amount'=>$amount,'extra_update'=>date('Y-m-d H:i:s'),'history_date'=>date('Y-m-d H:i:s'),'old_amount'=>$old_amount);

        $res = $this->db->insert('new_history',$data);

        if($ver)
        {
            return true;
        }
        else
        {
            return false;
        }
    }



    function delete_extras($extra_id,$reser_id,$description)
    {
        $this->db->where('extra_id',$extra_id);

        $data = array('extra_id'=>$extra_id,'reservation_id'=>$reser_id,'description'=>$description,'history_date'=>date('Y-m-d H:i:s'));

        $ver = $this->db->delete('extras');

        $res = $this->db->insert('new_history',$data);

        return true;
    }


    function get_history_details($channel_id,$history_uri)
    {

        $this->db->order_by('history_id','desc');
        
        $this->db->where('channel_id',unsecure($channel_id));

        $this->db->where('reservation_id',$history_uri);

        $res = $this->db->get('new_history');

        if($res->num_rows > 0){

            return $res->result();

        }

            return false;

    }



    function update_notes($reservation_id,$description){

        $data = array('description'=>$description);

        $this->db->where('reservation_id',$reservation_id);

        $ver = $this->db->update('manage_reservation',$data);



        if($ver)

        {

            return true;

        }

        else

        {

            return false;

        }

    }
    
    

    function get_extras($channel,$id)
    {
    
        $this->db->order_by('extra_id','desc');
        
        $this->db->where('channel_id',$channel);
        
        $this->db->where('reservation_id',$id);
        
        $ver = $this->db->get('extras');
    
        if($ver->num_rows()>0)
        {

            return $ver->result();
        }
        else
        {
            return false;
        }
    }

    function total_extras($channel,$reser)
    {
        $count = $this->db->select('extra_id')->from('extras')->
        where(array('channel_id'=>unsecure($channel),'reservation_id'=>$reser))->count_all_results();
        return $count;
    }

    function total_extras_result($channel,$reser)
    {
        $this->db->where('channel_id',unsecure($channel));
        $this->db->where('reservation_id',$reser);
        $ver = $this->db->get('extras');
        if($ver->num_rows()>0)
        {
            return $ver->result();
        }
        else
        {
            return false;
        }
    }

    function extra_count($channel,$id)
    {
        $this->db->where('channel_id',$channel);
        
        $this->db->where('reservation_id',$id);
        
        $ver = $this->db->get('extras');
        
        if($ver->num_rows()>0)
        {
            return $ver->num_rows();
        }
        
        else
        {
            return false;
        }
    }
    function payment_paid_count($channel,$reser)
    {
        $count = $this->db->select('payment_id')->from('add_payment')->
        where(array('channel_id'=>$channel,'reservation_id'=>$reser))->count_all_results();
        return $count;
    }
    function get_payment_paid($channel,$id)
    {
        $this->db->where('channel_id',$channel);
        
        $this->db->where('reservation_id',$id);
        
        $ver = $this->db->get('add_payment');
        
        if($ver->num_rows()>0)
        {
            return $ver->result();
        }
        else
        {
            return false;
        }
    }

    function insert_payment($id)
    {
        $reservation_id = $id;
        $total_amount   = $this->input->post('total_amount');
        $due_amount     = $this->input->post('due_amount');
        $paid_amount    = $this->input->post('paid_amount');
        $insert_payment = '';//$this->input->post('insert_payment');
        $payment_method = $this->input->post('payment_method');
        $notes          = $this->input->post('notes');
        $curr_cha_id    = unsecure($this->input->post('curr_cha_id'));
        $data = array('reservation_id'=>$reservation_id,'channel_id'=>$curr_cha_id,'total_amount'=>$total_amount,'due_amount'=>$due_amount,'paid_amount'=>$paid_amount,'insert_payment'=>$insert_payment,'payment_method'=>$payment_method,'notes'=>$notes);
        $ver = $this->db->insert('add_payment',$data);
        if($ver)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function delete_payment($payment_id)
    {
        $this->db->where('payment_id',$payment_id);
        $this->db->delete('add_payment');
        return true;
    }

    function save_card(){
    $exp_month=$_GET['exp_month'];
    $exp_year=$_GET['exp_year'];
    $card_number=$_GET['card_number'];
    $card_name=$_GET['card_name'];
    $card_type=$_GET['card_type'];
    $this->db->where('user_id',current_user_type());
     $sql=$this->db->get('card_details');
    $data=array(
        'exp_month'=>(string)safe_b64encode($exp_month),
        'name'=>(string)safe_b64encode($card_name),
        'exp_year'=>(string)safe_b64encode($exp_year),
        'number'=>(string)safe_b64encode($card_number),
        'user_id'=>current_user_type()
        );
     if($sql->num_rows>0){
        $this->db->where('user_id',current_user_type());
       $this->db->update('card_details',$data); 
     }else{
       $this->db->insert('card_details',$data);
     }
    }


   // 30/01/2016...
      
    function invoice_count($channel,$id)
    {   
        $this->db->where('channel_id',$channel);
        
        $this->db->where('reservation_id',$id);
    
        $ver = $this->db->get('invoice');
        
        if($ver->num_rows() >0)
        {
            return $ver->num_rows();
        }
        else
        {
            return false;
        }
    }

    function get_invoices($channel,$id)
    {
        $this->db->where('channel_id',$channel);
        
        $this->db->where('reservation_id',$id);
        
        $ver = $this->db->get('invoice');
        
        if($ver->num_rows()==1)
        {
            return $ver->row();
        }
        else
        {
            return false;
        }
    }

    function welcome_mail($reservation_id)
    {
        
        $this->mailsettings();
        
        $reservation_id = insep_decode($reservation_id);

        $get_mail_template = get_mail_template('18');

        $email_title = $get_mail_template['subject'];

        $email_message = $get_mail_template['message'];
        
        $welcome = get_data('welcome_remainder_emails',array('reservation_id'=>$reservation_id,'mail_type'=>'1'))->row();

        $admin_detail = get_data(TBL_SITE,array('id'=>1))->row();

            $data1 = array(

                    '###COMPANYLOGO###'=>base_url().'uploads/logo/'.$admin_detail->site_logo,

                    '###SITENAME###'=>$admin_detail->company_name,
    
                    '###LINK###'=>base_url(),
                    
                    '###MESSAGE###'=>$welcome->email_message,
                    
                    '###HOTELNAME###'=>ucfirst(get_data(HOTEL,array('hotel_id'=>hotel_id()))->row()->property_name),
                    
    
                    );
    
            $subject_data1 = array(

                        '###SITENAME###'=>$admin_detail->company_name,
                        '{WELCOMESUBJECT}'=>$welcome->email_title,

                );

        $subject_new = strtr($email_title,$subject_data1);

        $this->email->subject($subject_new);

        $content_pop = strtr($email_message,$data1);

       // echo $content_pop;die;

        $this->email->message($content_pop);

        $this->email->from($admin_detail->email_id);

        $this->email->to($welcome->user_email);

        $this->email->subject($subject_new);

        $this->email->message($content_pop);

        $this->email->send();
        
        $copy_mail = explode(',',$welcome->copy_message);
        
        foreach($copy_mail as $cm)
        {
            if($cm=='1')
            {
                $this->email->subject($subject_new);
    
                $this->email->message($content_pop);
        
                $this->email->from($admin_detail->email_id);
        
                $this->email->to($admin_detail->email_id);
        
                $this->email->subject($subject_new);
        
                $this->email->message($content_pop);
        
                $this->email->send();
            }
        }
    }
    
    function remainder_mail()
    {
        
        extract($this->input->post());
            
        $this->mailsettings();

        $get_mail_template = get_mail_template('19');

        $sub_email_title = $get_mail_template['subject'];

        $sub_email_message = $get_mail_template['message'];
        
        $admin_detail = get_data(TBL_SITE,array('id'=>1))->row();

            $data1 = array(

                    '###COMPANYLOGO###'=>base_url().'uploads/logo/'.$admin_detail->site_logo,

                    '###SITENAME###'=>$admin_detail->company_name,
    
                    '###LINK###'=>base_url(),
                    
                    '###MESSAGE###'=>$email_message,
                    
                    '###HOTELNAME###'=>ucfirst(get_data(HOTEL,array('hotel_id'=>hotel_id()))->row()->property_name),
    
                    );
            
            $subject_data1 = array(

                        '###SITENAME###'=>$admin_detail->company_name,
                        '{REMAINDERSUBJECT}'=>$email_title,

                );

        $subject_new = strtr($sub_email_title,$subject_data1);

        $this->email->subject($subject_new);

        $content_pop = strtr($sub_email_message,$data1);

        $this->email->message($content_pop);

        $this->email->from($admin_detail->email_id);

        $this->email->to($user_email);


        $this->email->subject($subject_new);

        $this->email->message($content_pop);

        $this->email->send();
        
        $copy_mail = $this->input->post('copy_message');
        
        foreach($copy_mail as $cm)
        {
            if($cm=='1')
            {
                $this->email->subject($subject_new);
    
                $this->email->message($content_pop);
        
                $this->email->from($admin_detail->email_id);
        
                $this->email->to($admin_detail->email_id);
        
                $this->email->subject($subject_new);
        
                $this->email->message($content_pop);
        
                $this->email->send();
            }
        }
    }

    function get_property_name($prop_id)
    {
        $this->db->where('property_id',$prop_id);
        $ver = $this->db->get('manage_property');
        if($ver->num_rows()==1)
        {
            return $ver->row();
        }
        else
        {
            return false;
        }
    }

    function billing_details()
    {
		$this->db->where('user_id',current_user_type());
		$this->db->where('hotel_id',hotel_id());
        $ver = $this->db->get('bill_info');
        if($ver->num_rows()==1)
        {
            return $ver->row();
        }
        else
        {
            return false;
        }
    }
    
    function get_channel_name($channel_id)
    {
        return get_data(TBL_CHANNEL,array('channel_id'=>$channel_id))->row();   
    }

       function invoice_delete($id)
    {
        $this->db->where('id',$id);
        $this->db->delete('invoice');
        return true;
    }

    function update_reservation()
    {
       // $property_name = $this->input->post('property_name');
       $reservation_id = $this->input->post('reservation_id');
       $start_date = $this->input->post('start_date');
       $end_date = $this->input->post('end_date');
       $data = array('start_date'=>$start_date,'end_date'=>$end_date);
       $this->db->where('reservation_id',$reservation_id);
       $ver = $this->db->update('manage_reservation',$data);
       if($ver)
       {    
         return true;
       }
       else
       {
         return false;
       }
    }
    function reservation_count()
    {
        $this->db->where('status','unseen');
        if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $this->db->where('user_id',user_id());
        }
        else if(user_type()=='2')
        {
            $this->db->where('user_id',owner_id());
        }
        $this->db->where('hotel_id',hotel_id());
        $this->db->where('type','3');
        $this->db->group_by('type');
        $fetch=$this->db->get('notifications');
        if($fetch->num_rows()==1)
        {
            return $fetch->row();
        }
        else
        {
            return false;
        }
    }
    function reservation_cancel()
    {
        $this->db->where('status','unseen');
        if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
        {
            $this->db->where('user_id',user_id());
        }
        else if(user_type()=='2')
        {
            $this->db->where('user_id',owner_id());
        }
        $this->db->where('hotel_id',hotel_id());
        $this->db->where_in('type',4);
        $this->db->group_by('type');
        $fetch=$this->db->get('notifications');
        if($fetch->num_rows==1)
        {
            return $fetch->row();
        }
        else
        {
            return false;
        }
    }
    
    function room_ICAL($room_id='')
    {
        $ic_room_id = $room_id;
        $hotel_details = get_data(HOTEL,array('hotel_id'=>hotel_id()))->row_array();
        $country_details = get_data(TBL_COUNTRY,array('id'=>$hotel_details['country']))->row()->country_name;
		$this->db->select('separate_date,availability,stop_sell');
        $this->db->where('owner_id',current_user_type());
        $this->db->where('hotel_id',hotel_id());
        $this->db->where('individual_channel_id','0');
        $this->db->where('room_id',insep_decode($room_id));
        $data_ical = $this->db->get(TBL_UPDATE)->result_array();
        if(count($data_ical)!=0)
        {
            $schedules = "BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH";
            $i=0;  
            foreach($data_ical as $value)
            {
                extract($value);
                $i++;
				if($availability==0 || $stop_sell==1)
				{
					$startDate = date('Y-m-d',strtotime(str_replace('/','-',$separate_date)));
					$strDate = strtotime(date('Y-m-d',strtotime(str_replace('/','-',$separate_date))));
					$endDate = strtotime(date('Y-m-d',strtotime(str_replace('/','-',$separate_date))));
					$schedules .= "\nBEGIN:VEVENT";
					$schedules .= "\nUID:" . time().rand(11111, 99999);
					$schedules .= "\nDTSTAMP:" . date("Y-m-d H:i:s");
					$schedules .= "\nLOCATION:".$hotel_details['address'].' , '.$hotel_details['town'].' , '.$country_details;
					$schedules .= "\nURL;VALUE=".$hotel_details['web_site'];
					$schedules .= "\nDTSTART:" . date("Ymd", $strDate)."T".date("His")."Z";
					$schedules .= "\nDTEND:". date("Ymd",$endDate)."T".date("His")."Z";
					$schedules .= "\nSUMMARY:BOOKED";
					/* if(all_ical_count($startDate)==0)
					{
						$schedules .= "\nSUMMARY:".$availability.' AVAILABILITY';
					}
					else
					{
						$availabilitys=0;
						$schedules .= "\nSUMMARY:".$availabilitys.' RESERVATION';
					} */
					$schedules .= "\nEND:VEVENT";
					$schedules .= "\n";
				}
            }
            $schedules .= "\nEND:VCALENDAR";
            $available = get_data(ICAL,array('room_id'=>insep_decode($ic_room_id)))->row_array();
            
            if(count($available)!=0)
            {
                $file_name = $available['ical_link'];
                file_put_contents("uploads/ICAL/room/".$file_name,$schedules);
            }
            else
            {
                $file_name = time().rand(11111, 99999).'_aval_'.insep_decode($ic_room_id).'.ics';
                $ic_data['ical_link'] = $file_name;
                $ic_data['room_id']   = insep_decode($ic_room_id);
                insert_data(ICAL,$ic_data);
                file_put_contents("uploads/ICAL/room/".$file_name,$schedules);  
            }
            return true;
        }
        else
        {
            return false;
        }
    }
    function get_photo($property_id)
    {
        $this->db->where('room_id',$property_id);
        $sql=$this->db->get('room_photos');
        if($sql->num_rows > 0){
        $photo=$sql->row()->photo_names;
        $split_photo=explode(",",$photo);
        return $split_photo[0];
        }else{
        return "no";
        }
    }
    
    /*Subbaiah Get Reservation From Channel Start \*/
    function channel_reservation_count($property_id,$start_date,$end_date)
    {
        $total_reser_count = "";
	    if(user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || in_array('3',user_view()) || admin_id()!='' && admin_type()=='1')
		{
			$ch_details = get_data(CONNECT,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'channel_id'=>2))->row();
			
            $roomMappingCheck = get_data(MAP,array('owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'property_id'=>insep_decode($property_id)),'mapping_id,channel_id,import_mapping_id')->result_array();
            
			if(count($roomMappingCheck)!=0)
            {   
                $total_reser_count = 0;
                foreach($roomMappingCheck as $roomMap)
                {
                    extract($roomMap);
                    if($channel_id==11)
                    {   
                        $chaMapCheck = get_data(IM_RECO,array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'re_id'=>$import_mapping_id),'CODE')->row_array();
                        if(count($chaMapCheck)!=0)
                        {
							$chaReserCheckCount = $this->db->query("SELECT COUNT(*) AS `numrows` FROM `".REC_RESERV."` WHERE ( (DATE_FORMAT(CHECKIN,'%Y-%m-%d') >= '".$start_date."' AND DATE_FORMAT(CHECKIN,'%Y-%m-%d') <= '".$end_date."') OR ( (DATE_FORMAT(CHECKOUT,'%Y-%m-%d') > '".$start_date."' AND DATE_FORMAT(CHECKOUT,'%Y-%m-%d') <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND ROOMCODE='".$chaMapCheck['CODE']."' AND STATUS NOT IN('13')");
														
							if($chaReserCheckCount)
							{
								$chaReserCheckCount = $chaReserCheckCount->row();	
								if($chaReserCheckCount)
								{
									$chaReserCheckCount = $chaReserCheckCount->numrows;
								}
								else
								{
									$chaReserCheckCount = 0;
								}
							}
							else
							{
								$chaReserCheckCount = 0;
							}
                            $total_reser_count =$total_reser_count+$chaReserCheckCount;
                        }
                    }
                    else if($channel_id==1)
                    {   
                        $chaMapCheck = get_data('import_mapping',array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'map_id'=>$import_mapping_id),'roomtype_id,rate_type_id,rateplan_id')->row_array();
                        if(count($chaMapCheck)!=0)
                        {
							if($chaMapCheck['rateplan_id']=='')
							{
								$rate_type_id = $chaMapCheck['rate_type_id'];
							}
							else
							{
								$rate_type_id = $chaMapCheck['rate_type_id'];
							}
							
							$chaReserCheckCount = $this->db->query("SELECT COUNT(*) AS `numrows` FROM `".EXP_RESERV."` WHERE ( (arrival >= '".$start_date."' AND arrival <= '".$end_date."') OR ( (departure > '".$start_date."' AND departure <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND roomTypeID='".$chaMapCheck['roomtype_id']."' AND ratePlanID='".$rate_type_id."' AND type NOT IN('Cancel')");

							if($chaReserCheckCount)
							{
								$chaReserCheckCount = $chaReserCheckCount->row();	
								if($chaReserCheckCount)
								{
									$chaReserCheckCount = $chaReserCheckCount->numrows;
								}
								else
								{
									$chaReserCheckCount = 0;
								}
							}
							else
							{
								$chaReserCheckCount = 0;
							}
							$total_reser_count 		= $total_reser_count+$chaReserCheckCount;
                            $expediares = get_data('import_mapping',array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'roomtype_id'=>$chaMapCheck['roomtype_id'],'rateplan_id'=>$chaMapCheck['rate_type_id']),'roomtype_id,rate_type_id,rateplan_id')->result_array();

                            if(count($expediares) != 0)
                            {
                                foreach($expediares as $extra)
                                {
                                    $chaReserCheckCountextra = $this->db->query("SELECT COUNT(*) AS `numrows` FROM `".EXP_RESERV."` WHERE ( (arrival >= '".$start_date."' AND arrival <= '".$end_date."') OR ( (departure > '".$start_date."' AND departure <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND roomTypeID='".$extra['roomtype_id']."' AND ratePlanID='".$extra['rate_type_id']."' AND type NOT IN('Cancel')");

                                    if($chaReserCheckCountextra)
                                    {
                                        $chaReserCheckCountextra = $chaReserCheckCountextra->row();   
                                        if($chaReserCheckCountextra)
                                        {
                                            $chaReserCheckCountextra = $chaReserCheckCountextra->numrows;
                                        }
                                        else
                                        {
                                            $chaReserCheckCountextra = 0;
                                        }
                                    }
                                    else
                                    {
                                        $chaReserCheckCountextra = 0;
                                    }
                                    $total_reser_count      = $total_reser_count+$chaReserCheckCountextra;
                                }
                            }
                        }
                    }
                    else if($channel_id==8)
                    {   
                        $chaMapCheck = get_data(IM_GTA,array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'GTA_id'=>$import_mapping_id),'ID,rateplan_id')->row_array();
                        if(count($chaMapCheck)!=0)
                        {
							$chaReserCheckCount = $this->db->query("SELECT COUNT(*) AS `numrows` FROM `".GTA_RESERV."` WHERE ( (arrdate >= '".$start_date."' AND arrdate <= '".$end_date."') OR ( (depdate > '".$start_date."' AND depdate <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND room_id='".$chaMapCheck['ID']."' AND rateplanid='".$chaMapCheck['rateplan_id']."' AND status NOT IN('Cancelled')");
							
							if($chaReserCheckCount)
							{
								$chaReserCheckCount = $chaReserCheckCount->row();	
								if($chaReserCheckCount)
								{
									$chaReserCheckCount = $chaReserCheckCount->numrows;
								}
								else
								{
									$chaReserCheckCount = 0;
								}
							}
							else
							{
								$chaReserCheckCount = 0;
							}
                            
                             $total_reser_count =$total_reser_count+$chaReserCheckCount;
                        }
                    }
					else if($channel_id==2 && ($ch_details->xml_type==1 || $ch_details->xml_type==2))
                    {   
						$bk_details = booking_hotel_id();
                        $chaMapCheck = get_data(BOOKING,array('hotel_id'=>hotel_id(),'owner_id'=>current_user_type(),'import_mapping_id'=>$import_mapping_id),'B_room_id,B_rate_id')->row_array();
                        if(count($chaMapCheck)!=0)
                        {
							$chaReserCheckCount = $this->db->query("SELECT COUNT(*) AS `numrows` FROM `".BOOK_ROOMS."` WHERE ( (arrival_date >= '".$start_date."' AND arrival_date <= '".$end_date."') OR ( (departure_date > '".$start_date."' AND departure_date <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_hotel_id = '".hotel_id()."' AND id='".$chaMapCheck['B_room_id']."' AND hotel_id='".$bk_details."' AND rate_id='".$chaMapCheck['B_rate_id']."' AND status NOT IN('cancelled')");
														
							if($chaReserCheckCount)
							{
								$chaReserCheckCount = $chaReserCheckCount->row();	
								if($chaReserCheckCount)
								{
									$chaReserCheckCount = $chaReserCheckCount->numrows;
								}
								else
								{
									$chaReserCheckCount = 0;
								}
							}
							else
							{
								$chaReserCheckCount = 0;
							}
                            $total_reser_count =$total_reser_count+$chaReserCheckCount;
                        }
                    }
                    if($channel_id == 15)
                    {
                        $chaMapCheck = get_data(IM_TRAVELREPUBLIC,array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'map_id'=>$import_mapping_id),'RoomTypeId')->row_array();
                        if(count($chaMapCheck)!=0)
                        {
                            $result = $this->db->query("SELECT RoomTypeId FROM ".IM_TRAVEL_RESER_ROOMS." WHERE ( (CheckInDate >= '".$start_date."' AND CheckInDate <= '".$end_date."') OR ( (CheckOutDate > '".$start_date."' AND CheckOutDate <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."'")->result();
                            /*echo $this->db->last_query();
                            print_r($result);*/
                            $chaReserCheckCount = 0;
                            foreach($result as $res)
                            {
                                $roomid = explode(',',rtrim($res->RoomTypeId,','));
                                
                                for($r = 0; $r<count($roomid); $r++)
                                {
                                    if($roomid[$r] == $chaMapCheck['RoomTypeId'])
                                    {
                                        $chaReserCheckCount += 1;
                                    }
                                }
                            }                        

                            $total_reser_count =$total_reser_count+$chaReserCheckCount;
                        }
                    }
                }
            }
			$total_reser_count	=	$total_reser_count + $this->bnow_model->channel_reservation_count($property_id,$start_date,$end_date);
			$total_reser_count	=	$total_reser_count + $this->hotelbeds_model->channel_reservation_count($property_id,$start_date,$end_date);
            
        }
		/* echo $this->db->last_query().'<br>'; */
        return $total_reser_count;
    }
    
    function channel_reservation_result($property_id,$start_date,$end_date)
    {
        if(user_type()=='1' || user_type()=='2' && in_array('3',user_edit()) || in_array('3',user_view()) || admin_id()!='' && admin_type()=='1')
		{
			$ch_details = get_data(CONNECT,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'channel_id'=>2),'xml_type')->row();
            $roomMappingCheck = get_data(MAP,array('owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'property_id'=>insep_decode($property_id)),'mapping_id,channel_id,import_mapping_id')->result_array();
			
			/* echo '<pre>';
			print_r($roomMappingCheck); */

            if(count($roomMappingCheck)!=0)
            {
                $i=0;
                $chaReserCheckCount = array();
                foreach($roomMappingCheck as $roomMap)
                {
                    extract($roomMap);
                    if($channel_id==11)
                    {
                        $chaMapCheck = get_data(IM_RECO,array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'re_id'=>$import_mapping_id),'CODE')->row_array();
                        if(count($chaMapCheck)!=0)
                        {
							$cahquery = $this->db->query("SELECT `import_reserv_id` as reservation_id, DATE_FORMAT(CHECKIN,'%d/%m/%Y') as start_date, DATE_FORMAT(CHECKOUT,'%d/%m/%Y') as end_date ,channel_id , IDRSV as booking_number FROM `".REC_RESERV."` WHERE ( (DATE_FORMAT(CHECKIN,'%Y-%m-%d') >= '".$start_date."' AND DATE_FORMAT(CHECKIN,'%Y-%m-%d') <= '".$end_date."') OR ( (DATE_FORMAT(CHECKOUT,'%Y-%m-%d') > '".$start_date."' AND DATE_FORMAT(CHECKOUT,'%Y-%m-%d') <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND ROOMCODE='".$chaMapCheck['CODE']."' AND STATUS NOT IN('13') ORDER BY import_reserv_id DESC");
                            if($cahquery)
                            {
                                $chaReserCheckCount = array_merge($chaReserCheckCount,$cahquery->result());
                            }
                        }
                    }
                    else if($channel_id==1)
                    {
                        $chaMapCheck = get_data('import_mapping',array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'map_id'=>$import_mapping_id),'roomtype_id,rate_type_id, rateplan_id')->row_array();
						
                        if(count($chaMapCheck)!=0)
						{
							$cahquery = $this->db->query("SELECT `import_reserv_id` as reservation_id, DATE_FORMAT(arrival,'%d/%m/%Y') as start_date, DATE_FORMAT(departure,'%d/%m/%Y') as end_date ,channel_id , booking_id as booking_number FROM `".EXP_RESERV."` WHERE ( (arrival >= '".$start_date."' AND arrival <= '".$end_date."') OR ( (departure > '".$start_date."' AND departure <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND roomTypeID='".$chaMapCheck['roomtype_id']."' AND ratePlanID='".$chaMapCheck['rate_type_id']."' AND type NOT IN('Cancel') ORDER BY import_reserv_id DESC");
                            if($cahquery)
                            {
                                $chaReserCheckCount = array_merge($chaReserCheckCount,$cahquery->result());
                            }
                            $expediares = get_data('import_mapping',array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'roomtype_id'=>$chaMapCheck['roomtype_id'],'rateplan_id' => $chaMapCheck['rate_type_id']),'roomtype_id,rate_type_id, rateplan_id, rateAcquisitionType')->result_array();
                            
                            if(count($expediares) != 0)
                            {
                                foreach($expediares as $extra){
                                    $cahquery = $this->db->query("SELECT `import_reserv_id` as reservation_id, DATE_FORMAT(arrival,'%d/%m/%Y') as start_date, DATE_FORMAT(departure,'%d/%m/%Y') as end_date ,channel_id , booking_id as booking_number FROM `".EXP_RESERV."` WHERE ( (arrival >= '".$start_date."' AND arrival <= '".$end_date."') OR ( (departure > '".$start_date."' AND departure <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND roomTypeID='".$extra['roomtype_id']."' AND ratePlanID='".$extra['rate_type_id']."' AND type NOT IN('Cancel') ORDER BY import_reserv_id DESC");
                                    if($cahquery)
                                    {
                                        $chaReserCheckCount = array_merge($chaReserCheckCount,$cahquery->result());
                                    }
                                }
                            }
                        }
						
						/*$chaMapCheck_Rate = get_data('import_mapping',array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'roomtype_id'=>$chaMapCheck['roomtype_id'],'rateplan_id'=>$chaMapCheck['rate_type_id'],'rateAcquisitionType'=>'Derived'),'roomtype_id,rate_type_id, rateplan_id')->result_array();*/
						
						/* $chaMapCheck_Rate	=	$this->db->query("SELECT roomtype_id,rate_type_id, rateplan_id FROM ".IM_EXP." WHERE user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND roomtype_id='".$chaMapCheck['roomtype_id']."' AND rateplan_id='".$chaMapCheck['rate_type_id']."' AND ( rateAcquisitionType='Derived' OR rateAcquisitionType='Linked' )");
						if($chaMapCheck_Rate)
						{
							$chaMapCheck_Rate = $chaMapCheck_Rate->result_array();
						} */
						/* echo $this->db->last_query().'<br>'; */
						/*if(count($chaMapCheck_Rate)!=0)
						{
							foreach($chaMapCheck_Rate as $value)		
							{
								extract($value);
								$cahquery_rate = $this->db->query("SELECT `import_reserv_id` as reservation_id, DATE_FORMAT(arrival,'%d/%m/%Y') as start_date, DATE_FORMAT(departure,'%d/%m/%Y') as end_date ,channel_id , booking_id as booking_number FROM `".EXP_RESERV."` WHERE ( (arrival >= '".$start_date."' AND arrival <= '".$end_date."') OR ( (departure > '".$start_date."' AND departure <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND roomTypeID='".$roomtype_id."' AND ratePlanID='".$rate_type_id."' AND type NOT IN('Cancel') ORDER BY import_reserv_id DESC");
								if($cahquery_rate)
								{
									$chaReserCheckCount = array_merge($chaReserCheckCount,$cahquery_rate->result());
								}
							}
						}*/
						/* echo $this->db->last_query().'<br>'; */
                    }
                    else if($channel_id==8)
                    {
                        $chaMapCheck = get_data(IM_GTA,array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'GTA_id'=>$import_mapping_id),'ID,rateplan_id')->row_array();
                        if(count($chaMapCheck)!=0)
                        {
							$cahquery = $this->db->query("SELECT `import_reserv_id` as reservation_id, DATE_FORMAT(arrdate,'%d/%m/%Y') as start_date, DATE_FORMAT(depdate,'%d/%m/%Y') as end_date ,channel_id , booking_id as booking_number FROM `".GTA_RESERV."` WHERE ( (arrdate >= '".$start_date."' AND arrdate <= '".$end_date."') OR ( (depdate > '".$start_date."' AND depdate <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' AND room_id='".$chaMapCheck['ID']."' AND rateplanid='".$chaMapCheck['rateplan_id']."' AND status NOT IN('Cancelled') ORDER BY import_reserv_id DESC");
							
                            if($cahquery)
                            {
                                $chaReserCheckCount = array_merge($chaReserCheckCount,$cahquery->result());
                            }
                        }
                    }
					else if($channel_id==2 && ($ch_details->xml_type==1 || $ch_details->xml_type==2))
                    {
						$bk_details = booking_hotel_id();
                        $chaMapCheck = get_data(BOOKING,array('hotel_id'=>hotel_id(),'owner_id'=>current_user_type(),'import_mapping_id'=>$import_mapping_id),'B_room_id,B_rate_id')->row_array();
                        if(count($chaMapCheck)!=0)
						{
							$cahquery = $this->db->query("SELECT room_res_id as reservation_id, DATE_FORMAT(arrival_date,'%d/%m/%Y') as start_date, DATE_FORMAT(departure_date,'%d/%m/%Y') as end_date ,channel_id , roomreservation_id as booking_number FROM `".BOOK_ROOMS."` WHERE ( (arrival_date >= '".$start_date."' AND arrival_date <= '".$end_date."') OR ( (departure_date > '".$start_date."' AND departure_date <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_hotel_id = '".hotel_id()."' AND id='".$chaMapCheck['B_room_id']."' AND rate_id='".$chaMapCheck['B_rate_id']."' AND hotel_id='".$bk_details."' AND status NOT IN('cancelled') ORDER BY room_res_id DESC");
                            if($cahquery)
                            {
                                $chaReserCheckCount = array_merge($chaReserCheckCount,$cahquery->result());
                            }
                        } 
                    }
                    else if($channel_id==15)
                    {
                        $chaMapCheck = get_data(IM_TRAVELREPUBLIC,array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'map_id'=>$import_mapping_id),'RoomTypeId')->row_array();
                        if(count($chaMapCheck)!=0)
                        {
                            $result = $this->db->query("SELECT * FROM ".IM_TRAVEL_RESER_ROOMS." WHERE ( (CheckInDate >= '".$start_date."' AND CheckInDate <= '".$end_date."') OR ( (CheckOutDate > '".$start_date."' AND CheckOutDate <= '".$end_date."') ) ) AND user_id ='".current_user_type()."' AND hotel_id = '".hotel_id()."' ORDER BY room_id DESC")->result();
                            $cahquery = array();
                            foreach($result as $res)
                            {
                                $room_ids = explode(',', rtrim($res->RoomTypeId,','));

                                for($i=0; $i<count($room_ids);$i++)
                                {
                                    if($room_ids[$i] == $chaMapCheck['RoomTypeId'])
                                    {
                                        $cahquery[] = (object)array(
                                            'reservation_id' => $res->room_id,
                                            'start_date' => date('d/m/Y',strtotime($res->CheckInDate)),
                                            'end_date' => date('d/m/Y',strtotime($res->CheckOutDate)),
                                            'channel_id' => 15, 
                                            'booking_number' => $res->BookingId,
                                            'position' => $i,

                                        );
                                    }
                                }
                            }
                            if(count($cahquery) != 0){
                                $chaReserCheckCount = array_merge($chaReserCheckCount,$cahquery);
                            }

                        } 
                    }
                }
            }
			$bnow_reservation =	$this->bnow_model->channel_reservation_result($property_id,$start_date,$end_date);
			if($bnow_reservation)
			{
				$chaReserCheckCount	=	array_merge($chaReserCheckCount,$bnow_reservation);
			}
			$hotelbeds_reservation =	$this->hotelbeds_model->channel_reservation_result($property_id,$start_date,$end_date);
			if($hotelbeds_reservation)
			{
				$chaReserCheckCount	=	array_merge($chaReserCheckCount,$hotelbeds_reservation);
			}
        }
	    return $chaReserCheckCount;
    }
    
    function getRoomRelation($property_id,$all_channel_id)
    {
		$ch_details = get_data(CONNECT,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'channel_id'=>2))->row();
        //echo insep_decode($property_id).' '.$all_channel_id   ; 
        $roomMappingCheck = get_data(MAP,array('owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'property_id'=>insep_decode($property_id),'channel_id'=>$all_channel_id),'mapping_id,channel_id,import_mapping_id')->result_array();
       /* echo $this->db->last_query();
         echo '<pre>';
        print_r($roomMappingCheck); */
            if(count($roomMappingCheck)!=0)
            {   
                $RoomRelation = array();
                foreach($roomMappingCheck as $roomMap)
                {
                    extract($roomMap);
                    //echo $import_mapping_id;
                    if($channel_id==11)
                    {
                        $chaMapCheck = get_data(IM_RECO,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'re_id'=>$import_mapping_id),'CODE')->result_array();
                        
                        if(count($chaMapCheck)!=0)
                        {
                            $RoomRelation = array_merge($RoomRelation,$chaMapCheck);
                        }
                    }
                    if($channel_id==1)
                    {
                        $chaMapCheck = get_data('import_mapping',array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'map_id'=>$import_mapping_id),'roomtype_id,rate_type_id,rateplan_id')->result_array();
                        if(count($chaMapCheck)!=0)
                        {
                            $RoomRelation = array_merge($RoomRelation,$chaMapCheck);
                        }
						
						$chaMapCheck = get_data('import_mapping',array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'map_id'=>$import_mapping_id),'roomtype_id,rate_type_id,rateplan_id')->row_array();
						 
						$chaMapCheck_Rate = get_data('import_mapping',array('hotel_id'=>hotel_id(),'user_id'=>current_user_type(),'roomtype_id'=>$chaMapCheck['roomtype_id'],'rateplan_id'=>$chaMapCheck['rate_type_id'],'rateAcquisitionType'=>'Derived'),'roomtype_id,rate_type_id, rateplan_id')->result_array();
						/* echo $this->db->last_query().'<br>'; */
						if(count($chaMapCheck_Rate)!=0)
						{
							$RoomRelation = array_merge($RoomRelation,$chaMapCheck_Rate);
						}
						
                    }
                    if($channel_id==8)
                    {
                        $chaMapCheck = get_data(IM_GTA,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'GTA_id'=>$import_mapping_id),'ID,rateplan_id')->result_array();
                        if(count($chaMapCheck)!=0)
                        {
                            $RoomRelation = array_merge($RoomRelation,$chaMapCheck);
                        }
                    }
					if($channel_id==2 && ($ch_details->xml_type==1 || $ch_details->xml_type==2))
                    {
                        $chaMapCheck = get_data(BOOKING,array('owner_id'=>current_user_type(),'hotel_id'=>hotel_id(),'import_mapping_id'=>$import_mapping_id),'B_room_id,B_rate_id')->result_array();
                        if(count($chaMapCheck)!=0)
                        {
                            $RoomRelation = array_merge($RoomRelation,$chaMapCheck);
                        }
                    }
                    if($channel_id==15)
                    {
                        $chaMapCheck = get_data(IM_TRAVELREPUBLIC,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'map_id'=>$import_mapping_id),'RoomTypeId')->result_array();
                        if(count($chaMapCheck)!=0)
                        {
                            $RoomRelation = array_merge($RoomRelation,$chaMapCheck);
                        }
                    }
                }
				
				$bnow_relation		=	$this->bnow_model->getRoomRelation($property_id,$all_channel_id);
				
				if($bnow_relation)
				{
					$RoomRelation	=	array_merge($RoomRelation,$bnow_relation);
				}
                //echo '<pre>';
                //print_r($RoomRelation);   
                return $RoomRelation;
            }
			
    }
    
    function getSingleReservationReconline($reservation_id,$room_id,$cha_booking)
    {
        //echo $room_id; die;
        $cahquery = $this->db->query('SELECT `import_reserv_id` as reservation_id, DATE_FORMAT(CHECKIN,"%d/%m/%Y") as start_date, DATE_FORMAT(CHECKOUT,"%d/%m/%Y") as end_date , channel_id , FIRSTNAME as guest_name , IDRSV as reservation_code, DATEDIFF(CHECKOUT,CHECKIN) AS num_nights , ADULTS as members_count , REVENUE as price , ROOMCODE FROM (`import_reservation_RECONLINE`) WHERE `user_id` = "'.current_user_type().'" AND `hotel_id` = "'.hotel_id().'" AND `import_reserv_id` = "'.$reservation_id.'" AND `ROOMCODE` = "'.$room_id.'" AND `IDRSV` = "'.$cha_booking.'"');
        if($cahquery)
        {
            $chaReserResult = $cahquery->row_array();
        }
        return $chaReserResult;
    }
    function getSingleReservationTravelrepublic($reservation_id,$roomtype_id,$position,$cha_booking)
    {
        //echo $cha_booking;
        $booking_id = explode('~', $cha_booking);
        $result = $this->db->query("SELECT * FROM ".IM_TRAVEL_RESER_ROOMS." WHERE BookingId = '".$booking_id[0]."' AND user_id='".current_user_type()."' AND hotel_id ='".hotel_id()."'")->row_array();

        $room_ids = explode(',', rtrim($result['RoomTypeId'],','));
        $checkin            =   date('Y/m/d',strtotime($result['CheckInDate']));
        $checkout           =   date('Y/m/d',strtotime($result['CheckOutDate']));
        
        $duration           =   _datebetween($checkin,$checkout);
        $cost  = explode(',', rtrim($result['TotalNetCost'],','));
        $occupants  = explode('$%$', rtrim($result['Occupants'],'$%$')); 
        $Guest =   explode('&&',rtrim($occupants[$position],'&&'));

        $cahquery = array(
                'reservation_id' => $result['room_id'].'_'.$position,
                'start_date' => $checkin,
                'end_date' => $checkout,
                'channel_id' => 15,
                'guest_name' => '',
                'num_nights' => $duration,
                'members_count' => count($Guest),
                'price' => $cost[$position],
                'room_id' => $room_ids[$position],
                'id' => $result['BookingId'],
                'reservation_code' => $result['BookingId'],
            );



        /*$cahquery = $this->db->query('SELECT `room_res_id` as reservation_id, DATE_FORMAT(arrival_date,"%d/%m/%Y") as start_date, DATE_FORMAT(departure_date,"%d/%m/%Y") as end_date , channel_id , guest_name, roomreservation_id as reservation_code, DATEDIFF(departure_date,arrival_date) AS num_nights , numberofguests as members_count , totalprice as price , id , rate_id FROM (`'.BOOK_ROOMS.'`) WHERE `user_id` = "'.current_user_type().'" AND `hotel_hotel_id` = "'.hotel_id().'" AND `id` = "'.$roomtype_id.'" AND `rate_id` = "'.$rate_type_id.'" AND `hotel_id`="'.$bk_details.'" AND `roomreservation_id` = "'.$cha_booking.'"');*/
        if($cahquery)
        {
            $chaReserResult = $cahquery;
        }
        return $chaReserResult;
    }
	function getSingleReservationExpedia($reservation_id,$roomtype_id='',$rate_type_id='',$rateplan_id='',$cha_booking='')
   	{
		/* if($rateplan_id!='')
		{
			$rate_type_id  = $rate_type_id;
		}
		else
		{
			$rate_type_id  = $rate_type_id;	
		} */
		/* $cahquery = $this->db->query('SELECT `import_reserv_id` as reservation_id, DATE_FORMAT(arrival,"%d/%m/%Y") as start_date, DATE_FORMAT(departure,"%d/%m/%Y") as end_date , channel_id , CONCAT_WS(" ", givenName, middleName, surname) as guest_name , booking_id as reservation_code, DATEDIFF(departure,arrival) AS num_nights , adult as members_count , amountAfterTaxes as price , roomTypeID , ratePlanID FROM (`import_reservation_EXPEDIA`) WHERE (`arrival` >= CURDATE() OR `departure` >= CURDATE()) AND `user_id` = "'.current_user_type().'" AND `hotel_id` = "'.hotel_id().'" AND `roomTypeID` = "'.$roomtype_id.'" AND `ratePlanID` = "'.$rate_type_id.'"  AND `booking_id` = "'.$cha_booking.'"'); */
		
		$cahquery = $this->db->query('SELECT `import_reserv_id` as reservation_id, DATE_FORMAT(arrival,"%d/%m/%Y") as start_date, DATE_FORMAT(departure,"%d/%m/%Y") as end_date , channel_id , CONCAT_WS(" ", givenName, middleName, surname) as guest_name , booking_id as reservation_code, DATEDIFF(departure,arrival) AS num_nights , adult as members_count , amountAfterTaxes as price , roomTypeID , ratePlanID , status FROM (`import_reservation_EXPEDIA`) WHERE (`arrival` >= CURDATE() OR `departure` >= CURDATE()) AND `user_id` = "'.current_user_type().'" AND `hotel_id` = "'.hotel_id().'" AND `import_reserv_id` = "'.$reservation_id.'"');

		//echo $this->db->last_query().'<br>';
        if($cahquery)
        {
            $chaReserResult = $cahquery->row_array();
        }
        return $chaReserResult;
    }
   
	function getSingleReservationGta($reservation_id,$roomtype_id,$rate_type_id,$cha_booking)
    {
        //echo $cha_booking;
        $cahquery = $this->db->query('SELECT `import_reserv_id` as reservation_id, DATE_FORMAT(arrdate,"%d/%m/%Y") as start_date, DATE_FORMAT(depdate,"%d/%m/%Y") as end_date , channel_id , leadname as guest_name , booking_id as reservation_code, DATEDIFF(depdate,arrdate) AS num_nights , adults as members_count , totalcost as price , room_id , rateplanid , status FROM (`import_reservation_GTA`) WHERE `user_id` = "'.current_user_type().'" AND `hotel_id` = "'.hotel_id().'" AND `room_id` = "'.$roomtype_id.'" AND `rateplanid` = "'.$rate_type_id.'" AND `booking_id` = "'.$cha_booking.'"');
        if($cahquery)
        {
            $chaReserResult = $cahquery->row_array();
        }
        return $chaReserResult;
    }
	
	function getSingleReservationBooking($reservation_id,$roomtype_id,$rate_type_id,$cha_booking)
    {
        //echo $cha_booking;
		$ch_details = get_data(CONNECT,array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'channel_id'=>2))->row();
		if($ch_details->xml_type==1 || $ch_details->xml_type==2)
        {
			$bk_details = booking_hotel_id();
			$cahquery = $this->db->query('SELECT `room_res_id` as reservation_id, DATE_FORMAT(arrival_date,"%d/%m/%Y") as start_date, DATE_FORMAT(departure_date,"%d/%m/%Y") as end_date , channel_id , guest_name, roomreservation_id as reservation_code, DATEDIFF(departure_date,arrival_date) AS num_nights , numberofguests as members_count , totalprice as price , id , status , rate_id FROM (`'.BOOK_ROOMS.'`) WHERE `user_id` = "'.current_user_type().'" AND `hotel_hotel_id` = "'.hotel_id().'" AND `id` = "'.$roomtype_id.'" AND `rate_id` = "'.$rate_type_id.'" AND `hotel_id`="'.$bk_details.'" AND `roomreservation_id` = "'.$cha_booking.'"');
			if($cahquery)
			{
				$chaReserResult = $cahquery->row_array();
			}
			return $chaReserResult;
		}
		else
		{
			return false;
		}
    }
   
   /*Subbaiah Get Reservation From Channel End */

    function send_confirmation_email($channel_id,$id,$user_id,$hotel_id)
    {
        $channel_data = get_data("user_connect_channel", array("user_id" => $user_id,'hotel_id'=>$hotel_id,'channel_id' => $channel_id))->row();

        $data = get_data("import_reservation_EXPEDIA", array('user_id' => $user_id,'hotel_id' => $hotel_id,'booking_id' => $id))->row_array();
        if($channel_data){

            $get_email_info     =   get_mail_template('20');
        
            $email_subject1= $get_email_info['subject'];

            $email_content1= $get_email_info['message'];

            //$row=get_data(USERS,array('user_id'=>user_id()));
            if($data['type'] == "Book"){
                $status = "New Booking";
            }else if($data['type'] == "Modify"){
                $status = "Modified";
            }else if($data['type'] == "Cancel"){
                $status = "Canceled";
            }

            $staydate = explode(',', $data['stayDate']);
            $baserate = explode(',', $data['baseRate']);
            $dailyprice = "";

            for($i=0; $i<count($staydate); $i++){
                if($staydate[$i] != ""){

                    $dailyprice .= '<tr><td style="border: 1px solid #d9d9d9; line-height: 1.42857;padding: 8px; width: 50%;">'.date('M d, Y',strtotime(str_replace('/','-',$staydate[$i]))).'<br></td>   
                        <td  style="border: 1px solid #d9d9d9; line-height: 1.42857;   padding: 8px; width: 50%;">Adult rate<span class="subtext">(Basic deal)</span>'.$data['currency'].' '.$baserate[$i].'</td></tr>';
                }
            }

            $sitename = get_data('manage_hotel', array('hotel_id'=> $hotel_id,'owner_id' =>$user_id))->row()->property_name;
            $channel_name = get_data('manage_channel',array('channel_id' => $data['channel_id']))->row()->channel_name;

            $sub = array(
                        '###CHANNEL###' => $channel_name,
                    );

            $aa=array(

                '###SITENAME###' => $sitename,                                                                                            

                '###CHANNEL###' => $channel_name,                

                '###NAME###'=>$data['givenName'].' '.$data['middleName'].' '.$data['surname'],      

                '###RESERID###'=>$data['booking_id'], 

                '###STATUS###'=>$status, 

                '###ADDRESS###' => $data['address'].','.$data['city'].','.$data['stateProv'],
                '###COUNTRY###' => $data['country'],

                '###CHECKIN###'=>date('M d, Y',strtotime(str_replace('/','-',$data['arrival']))),

                '###CHECKOUT###'=>date('M d, Y',strtotime(str_replace('/','-',$data['departure']))),  

                '###CURRENCY###'=>$data['currency'],

                '###SUBTOTAL###'=>$data['amountAfterTaxes'],

                '###GRANDTOTAL###'=> $data['amountAfterTaxes'],

                '###CREATEDDATE###' => $data['created_time'],

                '###MOBILE### ' =>   $data['cityAreaCode'].$data['number'].$data['extension'],
                '###EMAIL###' => $data['Email'],

                '###COMMISION###' => 'None',

                '###PAYMENT###' => 'Card Payment',

                '###GUESTCOUNT###' => $data['adult'] + $data['child'],

                '###MEALPLAN###' => 'No',

                '###DAILYPRICE###' => $dailyprice,

                '###NOTES###' => $data['SpecialRequest'],

            ); 

            $email_content=strtr($email_content1,$aa);
			
			if($email_content!='')
			{
				$subject = strtr($email_subject1,$sub);

				$admin_detail = get_data(TBL_SITE,array('id'=>1))->row();

				$this->mailsettings(); 

				$this->email->clear(TRUE);  

				$this->email->from($admin_detail->email_id);

				$this->email->to($channel_data->reservation_email); 
			
				$this->email->subject($subject); 

				$this->email->message($email_content);

				$this->email->send();
			}
            //return true;
        }

    }

    public function update_plan_status($id,$status){

        $this->db->where("user_buy_id",$id);
        $this->db->set("plan_status",$status);
        $this->db->update("user_membership_plan_details");
    }

    // Send Arrival And Departure Email //

    function get_todays_arrival_departure($type,$user_id,$hotel_id)
    {
        $date = date('d/m/Y');
        $bdate = date('Y-m-d');

        if($type=='arrival')
        {
            $chaReserCheckCount = array();
            $det=$this->db->query('SELECT * , created_date as current_date_time FROM `manage_reservation` WHERE str_to_date(`start_date`, "%d/%m/%Y") = str_to_date("'.$date.'", "%d/%m/%Y") AND hotel_id='.$hotel_id.' AND user_status="Arrival"');
            $chaReserCheckCount = array_merge($chaReserCheckCount,$det->result());
            
            $chaReserCheckCount = array_merge($chaReserCheckCount,all_reservation_result($type,$bdate,$user_id,$hotel_id));
            if($chaReserCheckCount)
            {
                uasort($chaReserCheckCount,function($a,$b)
                {
                    return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
                });  
                return $chaReserCheckCount;              
            }
        }
        else if($type=='depature')
        {
            $chaReserCheckCount = array();
            $det=$this->db->query('SELECT * , created_date as current_date_time FROM `manage_reservation` WHERE str_to_date(`end_date`, "%d/%m/%Y") = str_to_date("'.$date.'", "%d/%m/%Y") AND hotel_id='.$hotel_id.' AND user_status="Departure"');
            $chaReserCheckCount = array_merge($chaReserCheckCount,$det->result());
            
            $chaReserCheckCount = array_merge($chaReserCheckCount,all_reservation_result($type,$bdate,$user_id,$hotel_id));
            if($chaReserCheckCount)
            {
                uasort($chaReserCheckCount,function($a,$b)
                {
                    return strtotime($a->current_date_time)<strtotime($b->current_date_time)?1:-1;
                });
                return $chaReserCheckCount;
            }
        }
    }
	function reservation_all_channel()
	{
		$this->db->select('C.channel_name');
		$this->db->join(CONNECT .' as U','C.channel_id=U.channel_id');
		$this->db->where(array('U.user_id'=>current_user_type(),'hotel_id'=>hotel_id()));
		$all_channel = $this->db->get(TBL_CHANNEL .' as C')->result_array();
		//$all_channel = get_data(TBL_CHANNEL)->result_array();
		if(count($all_channel)!=0)
		{
			$ch_name[]='Hoteratus';
			foreach($all_channel as $channel)
			{
			   extract($channel);
			   $ch_name[]=$channel_name;
			}
			return json_encode($ch_name);
		}
		else
		{
			$ch_name[]='Hoteratus';
			return json_encode($ch_name);
		}
		
	}
}