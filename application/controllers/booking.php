<?php 
ini_set('memory_limit', '-1');
ini_set('display_errors','1');
defined('BASEPATH') OR exit('No direct script access allowed');
class Booking extends Front_Controller {
    public $hotel_detail;
    public $currency_code;

    function is_login()
    { 
        if(!user_id())
        redirect(base_url());
        return;
    }
    function is_admin()
    {   
        if(!admin_id())\
        redirect(base_url());
        return;
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('booking_engine_model', 'booking');
    }

	function get_reservation(){
        if($this->input->get('hotel_id') == ""){
            $this->load->view('admin/404');
        }

        $hotel_id = insep_decode($this->input->get("hotel_id"));
        $data['booking'] = get_data('booking_engine',array('hotel_id'=>$hotel_id))->row_array();
        $data['hotel'] = get_data(HOTEL,array('hotel_id'=>$hotel_id))->row_array();
		$this->load->view('booking/header', $data);
        $this->load->view('booking/index', $data);
        $this->load->view('booking/footer', $data);
	}

    function set_reservation(){
        if($this->input->get('room') == ""){
            $this->load->view('admin/404');
        }
        $room_id = $this->input->get('room');
        $data['property'] = get_data(TBL_PROPERTY, array("property_id"=>$room_id))->row_array();
        $data['hotel'] =   $data['hotel'] = get_data(HOTEL,array('hotel_id'=>$data['property']['hotel_id']))->row_array();
        $data['booking'] = get_data('booking_engine',array('hotel_id'=>$data['property']['hotel_id']))->row_array();
        $this->load->view('booking/header', $data);
        $this->load->view('booking/reservation', $data);
        $this->load->view('booking/footer', $data);  
    }

    function engine(){
        if(admin_id()=='')
        {
            $this->is_login();
        }
        else
        {
            $this->is_admin();
        }
        $data['page_heading'] = 'Booking Engine';
        $user_details = get_data(TBL_USERS,array('user_id'=>user_id()))->row_array();
        $data= array_merge($user_details,$data);

        $data['booking'] = get_data('booking_engine',array('hotel_id'=>hotel_id()))->row_array();
        $data['widget'] = get_data('booking_widget',array('hotel_id'=>hotel_id()))->row_array();
        $this->views('booking/engine',$data);   
    }

    function update_engine(){
        if(admin_id()=='')
        {
            $this->is_login();
        }
        else
        {
            $this->is_admin();
        }
        extract($this->input->post());
        if($background_type == '1'){
            if($_FILES['background_img']['name'] !== ''){
                $rnumber = mt_rand(0,999999);
                $ext = substr(strrchr($_FILES['background_img']['name'], "."), 1);
                $filename=$rnumber;
                $profile_image=$filename.".".$ext;
                $config['upload_path'] ='uploads';
                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['file_name']=$filename;
                $this->load->library('upload', $config);
                $this->upload->initialize($config); 
                if($this->upload->do_upload('background_img')){
                    $data['background'] = $profile_image;
                }
            }
        }else{
            $data['background'] = $background;
        }

        if($_FILES['logo']['name'] !== ''){
            $rnumber = mt_rand(0,999999);
            $ext = substr(strrchr($_FILES['logo']['name'], "."), 1);
            $filename=$rnumber;
            $profile_image=$filename.".".$ext;
            $config['upload_path'] ='uploads';
            $config['allowed_types'] = 'gif|jpg|jpeg|png';
            $config['file_name']=$filename;
            $this->load->library('upload', $config);
            $this->upload->initialize($config); 
            if($this->upload->do_upload('logo')){
                $data['logo'] = $profile_image;
            }
        }
        $data['background_type'] = $background_type;
        $data['header_color'] = $header_color;
        $data['description'] = $description;
        $rows = $this->db->get_where('booking_engine', array('hotel_id'=>hotel_id()))->num_rows();
        if($rows > 0){
            $this->db->update('booking_engine', $data, array('hotel_id'=>hotel_id()));
        }else{
            $data['hotel_id'] = hotel_id();
            $this->db->insert('booking_engine', $data);
        }
        redirect('booking/engine');
    }

    function update_widget(){
        if(admin_id()=='')
        {
            $this->is_login();
        }
        else
        {
            $this->is_admin();
        }
        extract($this->input->post());
        $data['show_header'] = $header;
        $data['guest_number'] = $guest_number;
        $data['ask_children'] = $children;
        $data['layout'] = $layout;
        $data['floating_position'] = $floating_position;
        $data['open_page'] = $open_page;
        $data['theme'] = $theme;
        $data['font'] = $font;
        $data['custom_css'] = $custom_css;
        $rows = $this->db->get_where('booking_widget', array('hotel_id'=>hotel_id()))->num_rows();
        if($rows > 0){
            $this->db->update('booking_widget', $data, array('hotel_id'=>hotel_id()));
        }else{
            $data['hotel_id'] = hotel_id();
            $this->db->insert('booking_widget', $data);
        }
    }

    function test(){
        $data['widget'] = get_data('booking_widget', array('hotel_id'=>hotel_id()))->row_array();
        $data['page_heading'] = 'Booking Widget';
        $this->load->view('booking/header', $data);
        $this->load->view('booking/test', $data);
    }

    function widget($id){
        if(!$id){
            $this->load->view('admin/404');
            die();
        }

        $data['hotel_id'] = $id;
        $id = insep_decode($id);
        $data['widget'] = get_data('booking_widget', array('hotel_id'=>$id))->row_array();
        $data['page_heading'] = 'Booking Widget';
        $this->load->view('booking/header', $data);
        $this->load->view('booking/widget', $data);
    }
}
?>