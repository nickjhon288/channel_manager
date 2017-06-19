<?php 
ini_set('memory_limit', '-1');
ini_set('display_errors','1');
class channel_model extends CI_Model
{
	public function check_login()
	{
		$this->db->where('email_address',$this->security->xss_clean($this->input->post('login_email')));
		$this->db->or_where('user_name',$this->security->xss_clean($this->input->post('login_email')));
		//$this->db->where('user_password',$this->input->post('login_pwd'));
		$res = $this->db->get(TBL_USERS);
		if($res->num_rows()>0)
		{
			$result = $res->row();
			$t_hasher = new PasswordHash(8, FALSE);
			$hash = $result->password;
			$check = $t_hasher->CheckPassword($this->security->xss_clean($this->input->post('login_pwd')), $hash);
			if($check)
			{				   
				if($result->attempt_cnt<=6 && $result->pw_ck=='0' || $result->pw_ck=='2')
				{  
					if($result->pw_ck=='0'){				 	
				 	$pdata = array('pw_ck'=>'1');
				 	$Updatess = update_data(TBL_USERS,$pdata,array('user_id'=>$result->user_id));				 	
				   } 

					$Adata = array('attempt_cnt'=>'0');
					$Update_attempt = update_data(TBL_USERS,$Adata,array('user_id'=>$result->user_id));

				if($result->status==0 && $result->acc_active==0)
				{  // inactive status..
					$this->session->set_userdata('ch_user_mail',$result->email_address);
					return 3;
				}
				else if($result->status==0 || $result->acc_active==0)
				{  // inactive status..
					$this->session->set_userdata('ch_user_mail','deactive');
					return 2;
				}
				else
				{
					if($result->User_Type=='1')
					{					
						$this->session->set_userdata('ch_user_id',$result->user_id);
						$this->session->set_userdata('ch_user_type',$result->User_Type);
						$hotel_id = get_data(HOTEL,array('owner_id'=>$result->user_id))->row()->hotel_id;
						$this->session->set_userdata('ch_hotel_id',$hotel_id);
						if($result->pw_ck=='2')
						{							
						   return 1;
						}
						/*elseif($result->pw_ck=='1')
						{	*/						
							return 12;
						/*}*/	
					}
					elseif($result->User_Type=='2')
					{
						$assingn_hotel = get_data(ASSIGN,array('user_id'=>$result->user_id))->row_array();
						// print_r($assingn_hotel);die;
						if(count($assingn_hotel)!=0)
						{
							$hotel_id = get_data(HOTEL,array('hotel_id'=>$assingn_hotel['hotel_id']))->row_array();
							if(count($hotel_id)!=0)
							{
								$this->session->set_userdata('ch_user_id',$result->user_id);
								$this->session->set_userdata('ch_user_type',$result->User_Type);
								$this->session->set_userdata('ch_hotel_id',$hotel_id['hotel_id']);
								$this->session->set_userdata('owner_id',$hotel_id['owner_id']);
								//return 1;

							if($result->pw_ck=='1' || $result->pw_ck=='2')
							{
							   return 1;
							}
							elseif($result->pw_ck=='0')
							{
							   return 12;
							}	
							}
							else
							{
							   $this->session->set_userdata('ch_hotel_id','No_Hotel');
							   return 4;
							}
						}
						else
						{
							$this->session->set_userdata('ch_hotel_id','No_Hotel');
							return 4;
						}
					}
				}
			}
			else
			{ 
				return 10;
			}
		  }
			else
			{					
			$attempt_cnt = $result->attempt_cnt;
			if($attempt_cnt!='')
			{
				if($attempt_cnt<=5)
				{
					$Cnt = $attempt_cnt+1;
			    }
			    else
			    {
			    	$Cnt = $result->attempt_cnt;
			    }
			}	
			else
			{
				$Cnt = 1;
			}		
	     	
	     	$login_cnt = $result->attempt_cnt;	

			if($login_cnt<=6)
			{					
				if($result->pw_ck=='0')
				{		 	
			 		$pdata = array('pw_ck'=>'1');
			 		$Updatess = update_data(TBL_USERS,$pdata,array('user_id'=>$result->user_id));				 	
			    } 

				$Udate = array('attempt_cnt'=>$Cnt);
				$update_user = update_data(TBL_USERS,$Udate,array('user_id'=>$result->user_id));
				if($login_cnt==6)
				{					

						$seed = str_split('abcdefghijklmnopqrstuvwxyz'.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.'0123456789!@#$%^&*()'); 
						shuffle($seed);  
						 $rand = '';
						foreach (array_rand($seed, 10) as $k) 
						{
						  $rand .= $seed[$k];
						}
						$password = $rand;

						$t_hasher = new PasswordHash(8, FALSE);

						$hash = $t_hasher->HashPassword($password);		

						$old_password = $result->user_password;

						$org_pass = json_decode($old_password,true);

						$new_count = count($org_pass);

						if($new_count>=4)
						{
							$new = array($hash=>"1");	

							$passs = array_shift($org_pass);			

							$insert_pass = array_merge($org_pass, $new);

							$encode_password = json_encode($insert_pass);
						}
						else
						{
							$new = array($hash=>"1");

							$insert_pass = array_merge($org_pass, $new);

							$encode_password = json_encode($insert_pass);
						}	

						$updata['password']=$hash;

						$updata['user_password'] = $encode_password;

						
						/*echo '<pre>';
						print_r($updata);die;*/

						if(update_data(TBL_USERS,$updata,array('user_id'=>$result->user_id)))
						{							
						$user_info = get_data(TBL_USERS,array('user_id'=>$result->user_id))->row();

						$admin_detail = get_data(TBL_SITE,array('id'=>1))->row();

						$get_email_info		=	get_mail_template('27');

						/*echo '<pre>';
						print_r($get_email_info);die;*/

						$subject		=	$get_email_info['subject'];
						$template		=	$get_email_info['message'];

						//echo $admin_detail->email_id.'  '.$user_info->email_address;die;

						$this->mailsettings();
						$this->email->from($admin_detail->email_id);
						$this->email->to($user_info->email_address);
						$data = array(
						'###NAME###'=>$result->fname.' '.$result->lname,
						'###password###'=>$password,
						'###SITENAME###'=>$admin_detail->company_name,
						);			

						$subject_data = array(
						'###SITENAME###'=>$admin_detail->company_name,
						);
						$subject_new = strtr($subject,$subject_data);

						$this->email->subject($subject_new);

						$content_pop = strtr($template,$data);
						//echo $content_pop; die;
						$this->email->message($content_pop);

						if($this->email->send())
						{							
						//send_notification($admin_detail->email_id,$this->input->post('email_address'),$content_pop,$subject_new);
						}
						 return 8;
						}
						else
						{							
						 return false;
						}
					
				}					
			}			
			//	return 0;
			}
			
		} 
		else
		{
			
			return 0;  // no account..
		}
	}

	function user_channel()
	{
		if(user_type()=='1')
		{
			$query = $this->db->query('SELECT GROUP_CONCAT('.TBL_PROPERTY.'.connected_channel) AS connected_channel FROM '.TBL_PROPERTY.' where owner_id='.user_id().'');
		}
		else if(user_type()=='2')
		{
			$query = $this->db->query('SELECT GROUP_CONCAT('.TBL_PROPERTY.'.connected_channel) AS connected_channel FROM '.TBL_PROPERTY.' where owner_id='.owner_id().'');
		}
		else if(admin_id()!='' && admin_type()=='1')
		{
			$query = $this->db->query('SELECT GROUP_CONCAT('.TBL_PROPERTY.'.connected_channel) AS connected_channel FROM '.TBL_PROPERTY.' where owner_id='.user_id().'');
		}
		$row = $query->row();
		$getvl=$row->connected_channel;
        $exp_hids=explode(",",$getvl); 
		$connected_channel = array_unique($exp_hids);
		$this->db->select('*');
		$this->db->where_in('channel_id',$connected_channel);
		$channel = $this->db->get(TBL_CHANNEL);
		return $channel->result_array();

	}
	
	function user_channel_hotel()
	{
		$query = $this->db->query('SELECT GROUP_CONCAT(C.channel_id) AS connected_channel FROM `'.CONNECT.'` C JOIN `'.MAP.'` M  ON M.channel_id=C.channel_id where C.user_id='.current_user_type().' AND C.hotel_id='.hotel_id().' AND C.status="enabled"');
		$row = $query->row();
		$getvl=$row->connected_channel;
        $exp_hids	=explode(",",$getvl); 
		$connected_channel = array_unique($exp_hids);
		$this->db->select('channel_id,channel_name');
		$this->db->where_in('channel_id',$connected_channel);
		$channel = $this->db->get(TBL_CHANNEL);
		return $channel->result_array();
	}
	
	

    function recom_channel()

    {

		if(user_type()=='1')
		{
       		$query = $this->db->query('SELECT GROUP_CONCAT(manage_hotel.connected_channel) AS connected_channel FROM `manage_hotel` where owner_id='.user_id().' AND hotel_id='.hotel_id().'');
		}
		elseif(user_type()=='2')
		{
			$query = $this->db->query('SELECT GROUP_CONCAT(manage_hotel.connected_channel) AS connected_channel FROM `manage_hotel` where owner_id='.owner_id().' AND hotel_id='.hotel_id().'');
		}

       $row = $query->row();

       $getvl=$row->connected_channel;

       $exp_hids=explode(",",$getvl); 

       $connected_channel = array_unique($exp_hids);

       $this->db->select('*');

       $this->db->where('status','active');

       $this->db->where_not_in('channel_id',$connected_channel);

       $channel = $this->db->get(TBL_CHANNEL);

       return $channel->result_array();

    }
	
	

    function hotel_channel()

    {
        if(user_type()=='1')
        {
                $query = $this->db->query('SELECT GROUP_CONCAT(manage_hotel.connected_channel) AS connected_channel FROM `manage_hotel` where owner_id='.hotel_id().'');
        }else if(user_type()=='2'){
                $query = $this->db->query('SELECT GROUP_CONCAT(manage_hotel.connected_channel) AS connected_channel FROM `manage_hotel` where owner_id='.owner_id().'');
        }
        $row = $query->row();

       $getvl=$row->connected_channel;

       $exp_hids=explode(",",$getvl); 

       $connected_channel = array_unique($exp_hids);

       $this->db->select('*');

       $this->db->where_in('channel_id',$connected_channel);

       $channel = $this->db->get(TBL_CHANNEL);

       return $channel->result_array();

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
	
	// Channel Section Start
	function count_all_channels()
	{
		$count = $this->db->select('channel_id')->from(TBL_CHANNEL)->
		where('status != ""')->count_all_results();
		return $count;
	}
	function channel_subscribe()
	{
		 $channel_subscribe = get_data(TBL_USERS,array('user_id'=>current_user_type()))->row()->channel_subscribe_status;
		 return $channel_subscribe;
	}
	function updatetransaction($transaction_id)
	{
		$payment_id = insep_encode($this->session->userdata('cha_type'));
		$plan_details = get_data(CHA_PLAN,array('channel_id'=>insep_decode($payment_id)))->row();
		$plan_duration = $plan_details->channel_type;
		$data['channel_subscribe_txnid']= $transaction_id;
		$data['channel_subscribe_planid']  = insep_decode($payment_id);
		$data['channel_subscribe_price'] = $plan_details->channel_price;
		$data['channel_subscribe_method'] = 'CRIDIT CARD';
		
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
				$data['channel_subscribe_from'] = date('Y-m-d');
				$data['channel_subscribe_to'] = date("Y-m-d", strtotime("+$plan $plan_du"));
				$data['channel_subscribe_status'] = '1';
				if(update_data(TBL_USERS,$data,array('user_id'=>current_user_type())))
				{
					return true;
				}
				else
				{
					return false;
				}
	}
	function payment_success_mail($user_id='',$payment_id)
	{
		
		$get_email_info		=	get_mail_template('14');
		
		$email_subject1= $get_email_info['subject'];

		$email_content1= $get_email_info['message']; 
		if(insep_decode($payment_id)==1)
		{
			$plan_names = get_data(CHA_PLAN,array('channel_id'=>insep_decode($payment_id)))->row();
			$plan_name = $plan_names->channel_price.' '.$plan_names->channel_plan;
		}
		else		
		{
			$plan_name = get_data(CHA_PLAN,array('channel_id'=>insep_decode($payment_id)))->row()->channel_plan;
		}

		$row=get_data(TBL_USERS,array('user_id'=>current_user_type()))->row();

	  	$aa=array(  				 

		'###USERNAME###'=>$row->fname.' '.$row->lname, 		

		'###id###'=>$row->transaction_id, 

	    '###TYPE###'=>$plan_name, 

		'###Validity###'=>$row->plan_to, 

		'###status###'=>'Success', 		 

		); 

	$email_content=strtr($email_content1,$aa);
	//print_r($email_content);
	$admin_mail = get_data('site_config',array('id'=>'1'))->row(); 

	$this->mailsettings();   

	$this->email->from($admin_mail->email_id);

	$this->email->to($row->email_address); 

	$this->email->subject($email_subject1); 

	$this->email->message($email_content);

	if($this->email->send())
	{
		return true;
	}
	else{
		return false;
	}
	}


	/*function mailsettings()
	{ 	  
		$this->load->library('email');  
		$config['wrapchars'] = 76;  // Character count to wrap at.
		$config['priority'] = 1;  // Character count to wrap at.
		$config['mailtype'] = 'html'; // text or html Type of mail. If you send HTML email you must send it as a complete web page. Make sure you don't have any relative links or relative image paths otherwise they will not work.
		$config['charset'] = 'utf-8'; // Character set (utf-8, iso-8859-1, etc.).
		$this->email->initialize($config);	 	    
	}*/

	function mailsettings()
	{ 	  
		$this->load->library('email');  
		$config['wrapchars'] = 76;  // Character count to wrap at.
		$config['priority'] = 1;  // Character count to wrap at.
		$config['mailtype'] = 'html'; // text or html Type of mail. If you send HTML email you must send it as a complete web page. Make sure you don't have any relative links or relative image paths otherwise they will not work.
		$config['charset'] = 'utf-8'; // Character set (utf-8, iso-8859-1, etc.).
		$this->email->initialize($config);	 	    
	} 


	// Channel Section End           

         function get_password(){
		$this->db->where('user_id',$this->session->userdata('ch_user_id'));
		$res = $this->db->get('manage_users');
		if($res->num_rows>0){
			$result = $res->row();
			$t_hasher = new PasswordHash(8, FALSE);
			$hash = $result->password;
			$check = $t_hasher->CheckPassword($this->input->post('old_pass'), $hash);
			if($check)
			{
				return true;
			}
			else
			{
				return false;
			}
			return true;
		}
		else{return false;}
			
	}
	
	/*function update_password($new_pass)
	{
		$t_hasher = new PasswordHash(8, FALSE);
		$hash = $t_hasher->HashPassword($new_pass);
		$res = array('password'=>$hash);
		$this->db->where('user_id',$this->session->userdata('ch_user_id'));
		$result = $this->db->update('manage_users',$res);
		if($result)
		{
			return true;
		}
			return false;
	}*/


	function update_password($new_pass)
	{	

		$t_hasher = new PasswordHash(8, FALSE);

		$hash121212 = $t_hasher->HashPassword($new_pass);	

		$user_password_det = get_data(TBL_USERS,array('user_id'=>$this->session->userdata('ch_user_id')));	

		if($user_password_det->num_rows()==1)
		{
			$password_det = $user_password_det->row();

			$old_password = $password_det->user_password;

			$org_pass = json_decode($old_password,true);		

			$check12 = '';

		    foreach($org_pass as $key=>$value){
		    	
		    	$check12 .= $t_hasher->CheckPassword($new_pass, $key);

		    }		 

			/*if (array_key_exists($hash121212,$org_pass))
			{ 
				echo 'sdsdjsd';die;
			    return false;				
			}*/
			if($check12!='')
			{
				//echo 'dsdsdsdsd isfdd';die;
				return false;		
			}
			else
			{
				//echo 'dskdjklsdjl';die;

				$new_count = count($org_pass);

				if($new_count>=4)
				{

					$t_hasher = new PasswordHash(8, FALSE);

		            $hash = $t_hasher->HashPassword($new_pass);	

					$new = array($hash=>"1");	

					$passs = array_shift($org_pass);							

					$insert_pass = array_merge($org_pass, $new);

					$encode_password = json_encode($insert_pass);

					$update_pass = array('password'=>$hash,'user_password'=>$encode_password,'pw_ck'=>'2');

					$UpdatePassword = update_data(TBL_USERS,$update_pass,array('user_id'=>$this->session->userdata('ch_user_id')));

					if($UpdatePassword)
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

					$t_hasher = new PasswordHash(8, FALSE);

					$hash = $t_hasher->HashPassword($new_pass);	

					$new = array($hash=>"1");								

					$insert_pass = array_merge($org_pass, $new);

					$encode_password = json_encode($insert_pass);	

					$update_pass = array('password'=>$hash,'user_password'=>$encode_password,'pw_ck'=>'2');

					$UpdatePassword = update_data(TBL_USERS,$update_pass,array('user_id'=>$this->session->userdata('ch_user_id')));

					if($UpdatePassword)
					{
						return true;
					}
					else
					{
						return false;
					}
			 }	
		   }		  
	}
}
	
	/*Start hiding from IE Mac \*/
	function assign_hotels()
	{
		$this->db->select('H.hotel_id,H.property_name');
		$this->db->join(ASSIGN.' as A','A.hotel_id=H.hotel_id');
		$this->db->where('A.user_id',user_id());
		$query = $this->db->get(HOTEL.' as H')->result_array();
		if(count($query)!=0)
		{
			return $query;
		}
		else
		{
			return false;
		}
	}
	/*Stop hiding from IE Mac */
	//02/12/2015...
	function all_users()
	{	
		$this->db->where('hotel_id',hotel_id());
		if(user_type()=='1' || admin_id()!='' && admin_type()=='1'){
			$this->db->where('owner_id',user_id());
		}
		else if(user_type()=='2'){
			$this->db->where('owner_id',owner_id());
		}
		$res = $this->db->get('assign_priviledge');
		if($res->num_rows>0)
		{
			$user_assign = $res->result();
			$assign_user[]='';
			foreach($user_assign  as $assign)
			{
				$assign_user[]= $assign->user_id;
			}
		
		$this->db->where('owner_id',user_id());
		$this->db->where_not_in('user_id',$assign_user);
		$this->db->where('User_Type','2');
		$res = $this->db->get('manage_users');
		
		if($res->num_rows > 0)
		{
			return $res->result();
		}
		else
		{
			return false;
		}
		}else
		{
			$this->db->where('owner_id',user_id());
			$this->db->where('User_Type','2');
			$res = $this->db->get('manage_users');
			if($res->num_rows > 0)
			{
				return $res->result();
			}
			else
			{
				return false;
			}
		}
	}
	
	function add_user()
	{
		$user_name = implode(',',$this->security->xss_clean($this->input->post('user_name')));
		$this->db->where('user_id',$user_name);
		$ded = $this->db->get('manage_users');
		if($ded->num_rows==1){
			$deal = $ded->row();
			$dean = $deal->user_id;
		$access = implode(',',$this->security->xss_clean($this->input->post('access')));
		$data = array('user_id'=>$dean,
					  'access'=>$access,
					  'owner_id'=>current_user_type(),
					  'hotel_id'=>hotel_id());
		$res = $this->db->insert('assign_priviledge',$data);
		$this->db->where('hotel_id',hotel_id());
		$res = $this->db->get('manage_hotel');
		if($res->num_rows == 1)
		{
			$user_assign = $res->row();
			$assign = $user_assign->assigned_user;
			if($assign)
			{
			  $assign_det = $assign.','.$dean;
		    }
			else
			{
				 $assign_det = $dean;
			}
		$result = array('assigned_user'=>$assign_det);
		$this->db->where('hotel_id',hotel_id());
		$res = $this->db->update('manage_hotel',$result);
	   }
	   else
	   {
		   $res = $this->db->insert('manage_hotel',$result);
	   }
	   return true;
	}
	else
	{
		return false;
	}
	}
	
	// update user...
	function update_user($priviledge_id)
	{
		$user_name = $this->security->xss_clean($this->input->post('user_name'));
		$access = implode(',',$this->security->xss_clean($this->input->post('access')));
		$data = array('user_id'=>$user_name,
					  'access'=>$access,
					  );
		$this->db->where('owner_id',current_user_type());
		$this->db->where('hotel_id',hotel_id());
		$this->db->where('priviledge_id',$priviledge_id);
		$res = $this->db->update('assign_priviledge',$data);
		if($res)
		{
			return true;
		}
		else{
			return false;
		}
	}
	
	// delete users..
	function delete_user($u_id,$us_id)
	{
		$result = $this->del_users();	
		$res = explode(',',$result->assigned_user);
		$del = array_search($us_id,$res);
		unset($res[$del]);
		$this->db->where('priviledge_id',$u_id);
		$this->db->delete('assign_priviledge');
		$data = array('assigned_user'=>implode(',',$res));
		$this->db->where('owner_id',current_user_type());
		$this->db->where('hotel_id',hotel_id());
		$this->db->update('manage_hotel',$data);
		delete_data(TBL_USERS,array('user_id'=>$us_id));
		return true;
	}
	
	// delete users....
	function del_users(){
		$this->db->where('owner_id',current_user_type());
		$this->db->where('hotel_id',hotel_id());
		$res = $this->db->get('manage_hotel');
		if($res->num_rows == 1)
		{
			return $res->row();
		}
			return false;
	}	
	
	// get access..
	function get_access(){
		$this->db->where('owner_id',user_id());
		$this->db->where('hotel_id',hotel_id());
		$res = $this->db->get('assign_priviledge');
		if($res->num_rows > 0){
			return $res->result();
		}
			return false;
	}
	
	//get_user_list...
	function get_user_list(){
		$this->db->order_by('priviledge_id','desc');
		// if(user_type()=='1'){
		$this->db->where('owner_id',current_user_type());
		/* }
		else if(user_type()=='2'){
			$this->db->where('owner_id',owner_id());
		} */
		$this->db->where('hotel_id',hotel_id());
		$res = $this->db->get('assign_priviledge');
		if($res->num_rows > 0)
		{
			return $res->result();
		}
		else
		{
			return false;
		}
	}
	
	//get user name 
	 function get_username($user_id)
	{
		$this->db->where('owner_id',current_user_type());
		$this->db->where('user_id',$user_id);
		$this->db->where('User_Type',2);
		$query = $this->db->get('manage_users');
		if($query->num_rows > 0)
		{
			return $query->row();
		}
		else
		{
			return false;
		}
	} 
	
	function fetch_access($getacc){
		$this->db->where('acc_id',$getacc);
		$query = $this->db->get('user_access');
		if($query->num_rows > 0)
		{
			return $query->row();
		}
			return false;
	}
	
	function get_user($priviledge_id){
		$this->db->where('priviledge_id',$priviledge_id);
		if(user_type()=='1' || admin_id()!='' && admin_type()=='1'){
			$this->db->where('owner_id',user_id());
		}
		else if(user_type()=='2'){
			$this->db->where('owner_id',owner_id());
		}
		$this->db->where('hotel_id',hotel_id());
		$res = $this->db->get('assign_priviledge');
		if($res->num_rows >0){
			return $res->result();
		}
			return false;
	}	
	
	

function update_users($priviledge_id)
{
	$user_access = $this->security->xss_clean($this->input->post('access_options'));
	
	$access = json_encode($user_access);

    $data = array(
					'access'=>$access,
                 );
    $this->db->where('owner_id',current_user_type());
	
	$this->db->where('user_id',insep_decode($this->input->post('user_id')));

    $this->db->where('hotel_id',hotel_id());

    $this->db->where('priviledge_id',insep_decode($priviledge_id));

    $res = $this->db->update('assign_priviledge',$data);
	$t_hasher = new PasswordHash(8, FALSE);
	$hash = $t_hasher->HashPassword($this->security->xss_clean($this->input->post('user_password')));
	$udata['user_name'] = $this->security->xss_clean($this->input->post('user_name'));
	$udata['email_address'] = $this->security->xss_clean($this->input->post('email_address'));
	$udata['spass'] = $this->security->xss_clean($this->input->post('user_password'));
	$udata['password'] = $hash;
	update_data(TBL_USERS,$udata,array('user_id'=>insep_decode($this->input->post('user_id'))));

    if($res)
    {
		return true;
	}
    else
	{
        return false;
    }
}


	
	// count_connected_channels...
	function count_connected_channels(){
		if(user_type()=='1' || admin_id()!='' && admin_type()=='1'){
			$this->db->where('user_id',current_user_type());
		}
		else if(user_type()=='2'){
			$this->db->where('user_id',owner_id());
		}
		$this->db->where('hotel_id',hotel_id());
		$res = $this->db->get('user_connect_channel');
		return $res->num_rows;	
	}
	
	function connected_channelss(){
		if(user_type()=='1' || admin_id()!='' && admin_type()=='1'){
			$this->db->where('user_id',current_user_type());
		}
		else if(user_type()=='2'){
			$this->db->where('user_id',owner_id());
		}
		$this->db->where('hotel_id',hotel_id());
		$res = $this->db->get('user_connect_channel');
		if($res->num_rows>0){
			return $res->result();
		}
			return false;
	}
	
	

    // channel_image...

    function channel_image($channel_id){

       $this->db->where('channel_id',$channel_id);

       $res = $this->db->get('manage_channel');

       if($res->num_rows == 1){

           return $res->row();

       }

           return true;

    }
	
	

    function get_room_name(){

    if(user_type()=='1' || admin_id()!='' && admin_type()=='1'){

       $this->db->where('owner_id',current_user_type());

    }

    else if(user_type()=='2'){

       $this->db->where('owner_id',owner_id());

    }

       $this->db->where('hotel_id',hotel_id());
	   
	   $this->db->where('status','Active');
	   
	   $this->db->order_by('property_id','desc');

       $res = $this->db->get('manage_property');

       if($res->num_rows>0){

           return $res->result();

       }

       else{

           return false;

       }

    }


    // 25/01/2016...

    function add_bill()
	{

		$bill_id = $this->security->xss_clean($this->input->post('bill_id'));
		$this->db->where('user_id',current_user_type());
		$this->db->where('hotel_id',hotel_id());
		$res = $this->db->get('bill_info');
		$qry = $res->row();
		if($res->num_rows()==0)
		{
			// echo 'insert';die;
			$company_name = $this->security->xss_clean($this->input->post('company_name'));
			$town = $this->security->xss_clean($this->input->post('town'));
			$address = $this->security->xss_clean($this->input->post('address'));
			$zip_code = $this->security->xss_clean($this->input->post('zip_code'));
			$mobile = $this->security->xss_clean($this->input->post('mobile'));
			$vat = $this->security->xss_clean($this->input->post('vat'));
			$reg_num = $this->security->xss_clean($this->input->post('reg_num'));
			$email_address = $this->security->xss_clean($this->input->post('email_address'));
			$country = $this->security->xss_clean($this->input->post('country'));
			$data = array('user_id'=>current_user_type(),'hotel_id'=>hotel_id(),'company_name'=>$company_name,'town'=>$town,'address'=>$address,'zip_code'=>$zip_code,'mobile'=>$mobile,'vat'=>$vat,'reg_num'=>$reg_num,'email_address'=>$email_address,'country'=>$country);
			// print_r($data);die;
			$ver = $this->db->insert('bill_info',$data);
			if($ver)
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
			// echo 'update';die;
		    $company_name = $this->security->xss_clean($this->input->post('company_name'));
			$town = $this->security->xss_clean($this->input->post('town'));
			$address = $this->security->xss_clean($this->input->post('address'));
			$zip_code = $this->security->xss_clean($this->input->post('zip_code'));
			$mobile = $this->security->xss_clean($this->input->post('mobile'));
			$vat = $this->security->xss_clean($this->input->post('vat'));
			$reg_num = $this->security->xss_clean($this->input->post('reg_num'));
			$email_address = $this->security->xss_clean($this->input->post('email_address'));
			$country = $this->security->xss_clean($this->input->post('country'));
			$data = array('company_name'=>$company_name,'town'=>$town,'address'=>$address,'zip_code'=>$zip_code,'mobile'=>$mobile,'vat'=>$vat,'reg_num'=>$reg_num,'email_address'=>$email_address,'country'=>$country);
			/*echo '<pre>';
			print_r($data);die;*/
			$this->db->where('bill_id',$bill_id);
			$asp = $this->db->update('bill_info',$data);
			if($asp)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	function get_bill(){
		$this->db->where('user_id',current_user_type());
		$this->db->where('hotel_id',hotel_id());
		$ver = $this->db->get('bill_info');
		/*echo '<pre>';
		print_r($ver->row());die;*/
		if($ver->num_rows()==1){
			return $ver->row();
		}
		else{
			return false;
		}
	}


function add_user_det()
{
	/*$total_success = $this->input->post('total_success');
	if($total_success!='')
	{
		$db_val='';
		for($i=1;$i<=$total_success;$i++)
		{
			if($this->input->post('view_'.$i)!='' || $this->input->post('edit_'.$i)!='')
			{
			if($this->input->post('view_'.$i)!='' && $this->input->post('edit_'.$i)=='')
			{
				 $insert_value = $this->input->post('view_'.$i).'~V';
			}
			else if($this->input->post('edit_'.$i)!='' && $this->input->post('view_'.$i)=='')
			{
				 $insert_value = $this->input->post('edit_'.$i).'^E';
			}
			else if($this->input->post('edit_'.$i)!='' && $this->input->post('view_'.$i)!=''){
				$insert_value = $this->input->post('edit_'.$i).'~V^E';
			}
			$db_val .=  $insert_value.' , ';
			}
		}
	   $value = rtrim($db_val,' , ');
	   // echo rtrim($db_val,' , ');
	} */
	$user_access = $this->security->xss_clean($this->input->post('access_options'));
	$access = json_encode($user_access);
	$user_name = $this->security->xss_clean($this->input->post('user_name'));
	$email_address = $this->security->xss_clean($this->input->post('email_address'));
	$user_password = $this->security->xss_clean($this->input->post('user_password'));
	$confirm_password = $this->security->xss_clean($this->input->post('confirm_password'));
	$t_hasher = new PasswordHash(8, FALSE);
	$hash = $t_hasher->HashPassword($this->security->xss_clean($this->input->post('user_password')));
	$data = array(
						'User_Type'=>'2',
						'owner_id'=>user_id(),
						//'access'=>implode(',',$access),
						'user_name'=>$user_name,
						'email_address'=>$email_address,
						'password'=>$hash,
						'spass'=>$this->security->xss_clean($this->input->post('user_password')),
						'ipaddress' 	=> 	$_SERVER['REMOTE_ADDR'],
						'user_agent'	=>  $_SERVER['HTTP_USER_AGENT'],
						'status'	=>  '0',
						'acc_active'	=>  '0',
				);
	if(insert_data(TBL_USERS,$data))
	{
		$user_id = $this->db->insert_id();
		$data = array(	'user_id'=>$user_id,
						'access'=>$access,
						'owner_id'=>current_user_type(),
						'hotel_id'=>hotel_id());
		if(insert_data('assign_priviledge',$data))
		{
			$priviledge_id = $this->db->insert_id();
			$this->db->where('priviledge_id',$priviledge_id);
			$res = $this->db->get('assign_priviledge');
			if($res->num_rows()==1)
			{
				$ass = $res->row();
				$ass_id = $ass->user_id;
				$this->db->where('hotel_id',hotel_id());
				$ver = $this->db->get('manage_hotel');
				if($ver->num_rows()==1)
				{
					$var = $ver->row();
					$assign = $var->assigned_user;
					if($assign)
					{
						$dean = $assign.','.$ass_id;
					}
					else
					{
						$dean = $ass_id;
					}
					$data = array('assigned_user'=>$dean);
					$this->db->where('hotel_id',hotel_id());
					$res = $this->db->update('manage_hotel',$data);
					if($res)
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
					$varia = $this->db->insert('manage_hotel',$data);
					if($varia)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
			}
		}
	}
	else
	{
		return false;
	}
}
function get_connect_channel($chann=''){
    	if($chann!=""){
	    	$this->db->where('channel_id',$chann);
	    	$this->db->where('user_id',current_user_type());
	    	$this->db->where('hotel_id',hotel_id());
	    	$ver = $this->db->get('user_connect_channel');
        }else{
        	$this->db->where('user_id',current_user_type());
	    	$this->db->where('hotel_id',hotel_id());
	    	$ver = $this->db->get('user_connect_channel');
        }
    	if($ver->num_rows()==1){
    		return $ver->row();
    	}
    	else{
    		return false;
    	}
    }


function get_connect_channels(){
    if(user_type()=='1' || admin_id()!='' && admin_type()=='1')
    {
    		$this->db->where('user_id',user_id());
    }else if(user_type()=='2'){
        $this->db->where('user_id',owner_id());
    }
	    	$this->db->where('hotel_id',hotel_id());
	    	$ver = $this->db->get('user_connect_channel');
        if($ver->num_rows()>0){
    		return $ver->result();
    	}
    	else{
    		return false;
    	}
    }

    function get_connect_channels_with_status($channel_id){
    	$yesterday = date('m/d/Y',strtotime("-1 days"));
    	$yesterday = $yesterday." 11:59:59 am";
    	$sql = "SELECT error_message FROM channel_error WHERE user_id='".current_user_type()."' AND hotel_id='".hotel_id()."' AND channel_id='".$channel_id."' AND error_date_time > '".$yesterday."'  AND error_date_time <= '".date('m/d/Y h:i:s a', time())."' ORDER BY error_date_time DESC LIMIT 1 ";
    	$query = $this->db->query($sql);
    	if($query->num_rows() == 0){
    		return 0;
    	}else{
    		foreach($query->result() as $message)
    			return $message->error_message;
    	} 
    }

	function get_channel_id($view_channel)
	{
		$this->db->where('seo_url',$view_channel);
		$ver = $this->db->get('manage_channel');
		// print_r($ver->row());die;
		if($ver->num_rows()==1)
		{
			return $ver->row();
		}
		else
		{
			return false;
		}
	}

        
    function new_notification()
    {
         $this->db->where('status','unseen');
         $fetch=$this->db->get('notifications');
         if($fetch->num_rows>0)
         {
            return $fetch->num_rows();
         }
         else
         {
            return '0';
         }
    }

    function new_notification_result()
    {
        $this->db->where('status','unseen');
        $this->db->order_by('n_id','desc');
        $fetch=$this->db->get('notifications');
        if($fetch->num_rows>0)
        {
            return $fetch->result();
        }
        else
        {
            return false;
        }
    }

     //12/02/2016...
     
    function total_today_reservation()
	{
		$this->db->where('user_id',current_user_type());
		$this->db->where('hotel_id',hotel_id());
		$this->db->where('status','unseen');
		$this->db->where_not_in('reservation_id',0);
		$ver = $this->db->get('notifications');
		if($ver->num_rows>0)
		{
			return $ver->num_rows();
		}
		else
		{
			return false;
		}
	}
function getMappedRoom_Rate($channel_id)
	{
		$query = $this->db->query("(
		SELECT * FROM `roommapping`
		WHERE `owner_id` = '".current_user_type()."' AND `hotel_id` = '".hotel_id()."' AND `channel_id` = '".$channel_id."' AND `rate_id` ='0'
		GROUP BY `property_id`
		)
		UNION ALL
		(
		SELECT * FROM `roommapping`
		WHERE `owner_id` = '".current_user_type()."' AND `hotel_id` = '".hotel_id()."' AND `channel_id` = '".$channel_id."' AND `rate_id` !='0'
		GROUP BY `rate_id`
		)
		ORDER BY `property_id`");
		
		/* $query = $this->db->query("(
		SELECT * FROM `roommapping`
		WHERE `owner_id` = '".current_user_type()."' AND `hotel_id` = '".hotel_id()."' AND `channel_id` = '".$channel_id."' AND `rate_id` ='0'
		)
		UNION ALL
		(
		SELECT * FROM `roommapping`
		WHERE `owner_id` = '".current_user_type()."' AND `hotel_id` = '".hotel_id()."' AND `channel_id` = '".$channel_id."' AND `rate_id` !='0'
		)
		ORDER BY `property_id`"); */
		
		if($query)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}

	function save_connection_req_mail($data)
	{
		$this->db->insert('save_connection_req_mail',$data);
		$askforconnection = get_data(HOTEL,array('hotel_id'=>hotel_id()))->row();
		$ch = $this->get_connect_channel(5);
		$senderdetails = get_data(CONFIG,array('id'=>1))->row()->req_mail;
		$content = "Dear Xml Department,<br><br>
Kindly could you enable xml connection for Property:".$askforconnection->property_name."<br> Id ".$ch->hotel_channel_id."<br>

Hotel User ".$ch->user_name."<br>

Address: ".$askforconnection->address.",".$askforconnection->town."-".$askforconnection->zip_code."<br>

Email: ".$askforconnection->email_address."<br>

Web: ".$askforconnection->web_site."<br><br>


Please enable reservation push method to <br>
url:".base_url()."reservation/getHotelbedsReservation/".insep_encode(5)."<br><br>


Thanks for cooperation<br>
Best regards from<br>
hoteratus team.";

		$this->mailsettings();   
		if($senderdetails != ""){
			$this->email->from($senderdetails);
		}else{
			$this->email->from('connectivity@hoteratus.com');
		}

		$this->email->to($data['recipients']); 

		$this->email->subject($data['subject']); 

		$this->email->message($content);

		if($this->email->send())
		{
			return true;
		}
		else{
			return false;
		}
	}


/* sharmila starts here 0803  */

	function delete($table,$field,$value)
    {
        $this->db->where($field,$value);
        $delete = $this->db->delete($table,$data);
        if($delete)
        {
           return true;
        }
        return false;   
    }

     function insert($table,$data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

     function get_details_unique($table,$field=null,$value=null,$field1=null,$value1=null)
    {		
		if($field!='')
		{
			$this->db->where($field, $value);
		}
		if($field1!='')
		{
			$this->db->where($field1, $value1);
		}				
		$q = $this->db->get($table);		
        return $q->row();
    }


    function seoUrl($string) {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

    function email_newsletter()
	{
		$email = $this->input->post('email');
		$email_content1 = $this->input->post('descrpation');
		$email_subject1 = $this->input->post('subject');
        foreach ($email as  $email_address) {
	   $aa=array(  				 
         	'###status###'=>'Success', 		 
         ); 
	    $email_content=strtr($email_content1,$aa);
	    
		$admin_mail = get_data('site_config',array('id'=>'1'))->row(); 
		$this->mailsettings();   
		$this->email->from($admin_mail->email_id);
		$this->email->to($email_address); 
		$this->email->subject($email_subject1); 
		$this->email->message($email_content);
		$this->email->send();
         }
            return true;
	}


    /* sharmila ends here 0803  */


}
