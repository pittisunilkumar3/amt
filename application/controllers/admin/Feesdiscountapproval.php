<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
ob_start();
class Feesdiscountapproval extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->model('staff_model');
        $this->load->model('addaccount_model');
        $this->load->model('studentfee_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('generate_certificate', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Certificate');
        $this->session->set_userdata('sub_menu', 'admin/generatecertificate');

        $certificateList         = $this->feediscount_model->get();
        $data['certificateList'] = $certificateList;

        $progresslist            = $this->customlib->getProgress();
        $data['progresslist']    = $progresslist;

        $class                   = $this->class_model->get();
        $data['classlist']       = $class;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/feediscount/feesdiscountapproval', $data);
        $this->load->view('layout/footer', $data);
        
    }


    public function search()
    {
        $this->session->set_userdata('top_menu', 'Certificate');
        $this->session->set_userdata('sub_menu', 'admin/generatecertificate');

        $class                   = $this->class_model->get();
        $data['classlist']       = $class;

        $progresslist            = $this->customlib->getProgress();
        $data['progresslist']    = $progresslist;

        $certificateList         = $this->feediscount_model->get();
        $data['certificateList'] = $certificateList;

        $button                  = $this->input->post('search');
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/feediscount/feesdiscountapproval', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class       = $this->input->post('class_id');
            $section     = $this->input->post('section_id');
            $disstatus   = $this->input->post('progress_id');
            // $search      = $this->input->post('search');
            $certificate = $this->input->post('certificate_id');
            // if (isset($search)) {
                $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
                // $this->form_validation->set_rules('certificate_id', $this->lang->line('certificate'), 'trim|required|xss_clean');
                if ($this->form_validation->run() == false) {

                } else {

            //         $data['searchby']          = "filter";
                    $data['class_id']          = $this->input->post('class_id');
                    $data['section_id']        = $this->input->post('section_id');
                    $certificate               = $this->input->post('certificate_id');

                    $certificateResult         = $this->feediscount_model->get($certificate);
                    $data['certificateResult'] = $certificateResult;

                    $resultlist                = $this->student_model->searchByClassSectionAnddiscountStatus($class,$certificate, $section,$disstatus);
                    $data['resultlist']        = $resultlist;

            //         $data['discountstat']      = $disstatus;
                    $title                     = $this->classsection_model->getDetailbyClassSection($data['class_id'], $data['section_id']);
                    $data['title']             = $this->lang->line('std_dtl_for') . ' ' . $title['class'] . "(" . $title['section'] . ")";
                }
            // }
            $data['sch_setting'] = $this->sch_setting_detail;
            $this->load->view('layout/header', $data);
            $this->load->view('admin/feediscount/feesdiscountapproval', $data);
            $this->load->view('layout/footer', $data);
        }
    }


    public function generate($student, $class, $certificate)
    {
        $certificateResult         = $this->Generatecertificate_model->getcertificatebyid($certificate);
        $data['certificateResult'] = $certificateResult;
        $resultlist                = $this->student_model->searchByClassStudent($class, $student);
        $data['resultlist']        = $resultlist;

        $this->load->view('admin/certificate/transfercertificate', $data);
    }

    public function generatemultiple()
    {

        $studentid           = $this->input->post('data');
        $student_array       = json_decode($studentid);
        $certificate_id      = $this->input->post('certificate_id');
        $class               = $this->input->post('class_id');
        foreach ($student_array as $key => $value) {
            $item['student_session_id']=$value->student_id;
            $item['fees_discount_id']=$certificate_id;
            $temp=$this->feediscount_model->allotdiscount($item);
            $this->feediscount_model->updateapprovalstatus($certificate_id,$value->student_id,1);
        }
        
        redirect('admin/feesdiscountapproval/index');

    }


    public function dismissapprovalgeneratemultiple()
    {

        $studentid           = $this->input->post('data');
        $student_array       = json_decode($studentid);
        $certificate_id      = $this->input->post('certificate_id');
        $class               = $this->input->post('class_id');
        foreach ($student_array as $key => $value) {
            $this->feediscount_model->updateapprovalstatus($certificate_id,$value->student_id,2);
        }
        
        redirect('admin/feesdiscountapproval/index');
        
    }


    public function dismissapprovalsingle()
    {

        $studentid           = $this->input->post('dataa');
        // $certificate_id      = $this->input->post('certificate_id');

        $update_result = $this->feediscount_model->updateapprovalstatus($studentid, 2);

        if ($update_result) {
            $response = array('status' => 'success');
        } else {
            $response = array('status' => 'fail');
        }
        // Send the response
        echo json_encode($response);
        
    }


    public function retrive()
    {

        $studentid           = $this->input->post('dataa');
        $paymentid      = $this->input->post('certificate_id');

        $update_result = $this->feediscount_model->updateapprovalstatus($studentid, 0);

        $dataa = array(
            'id'=>$studentid,
            'payment_id'=> $paymentid,
        );
        $update_resultt = $this->feediscount_model->updatepaymentid($dataa);
        if(!empty($paymentid)){
            $parts = explode('/', $paymentid);
            $this->deleteFee($parts[0],$parts[1]);
        }

        if ($update_result) {
            $response = array('status' => 'success');
        } else {
            $response = array('status' => 'fail');
        }

        // Send the response
        echo json_encode($response);
        
    }

    public function deleteFee($invoice_id,$sub_invoice)
    {
        
        if (!empty($invoice_id)) {
            $this->studentfee_model->remove($invoice_id, $sub_invoice);
            $this->addaccount_model->transcationremove($invoice_id . '/' . $sub_invoice,'fees');
        }
        
    }

  

    
    public function approvalsingle()
    {
        $studentid = $this->input->post('dataa');
        // $certificate_id = $this->input->post('certificate_id');

        // Update the approval status in the database using your model
        $update_result = $this->feediscount_model->updateapprovalstatus($studentid, 1);
        $approval_data = $this->feediscount_model->getapproval($studentid);

        $staff_record = $this->staff_model->get($this->customlib->getStaffID());

        $collected_by             = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";
        $json_array               = array(
            'amount'          => convertCurrencyFormatToBaseAmount(0),
            'amount_discount' => convertCurrencyFormatToBaseAmount($approval_data['amount']),
            'amount_fine'     => convertCurrencyFormatToBaseAmount(0),
            'date'            => $approval_data['date'],
            'description'     => $approval_data['description'],
            'collected_by'    => $collected_by,
            'payment_mode'    => 'Cash',
            'received_by'     => $staff_record['id'],
        );

        $data = array(
            'fee_category'           => 'fees',
            'student_fees_master_id' => $approval_data['student_fees_master_id'],
            'fee_groups_feetype_id'  => $approval_data['fee_groups_feetype_id'],
            'amount_detail'          => $json_array,
        );

        $inserted_id        = $this->studentfeemaster_model->fee_deposit($data,'','');
        $receipt_data1           = json_decode($inserted_id);
        $dataa = array(
            'id'=>$studentid,
            'payment_id'=> $receipt_data1->invoice_id . '/' . $receipt_data1->sub_invoice_id,
        );
        $update_resultt = $this->feediscount_model->updatepaymentid($dataa);
        if ($update_result) {
            $response = array('status' => 'success');
        } else {
            $response = array('status' => 'fail');
        }

        // Send the response
        echo json_encode($response);
    }




    


    public function addstudentfee()
    {

        $studentid=$this->input->post('student_session_id');
        $fee_groups_feetype_id  = $this->input->post('fee_groups_feetype_id');

        $temp =$this->feediscount_model->getfeetypeid($studentid,$fee_groups_feetype_id);


        $staff_record = $this->staff_model->get($this->customlib->getStaffID());
        $collected_by             = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";
        $json_array               = array(
            'amount'          => convertCurrencyFormatToBaseAmount($this->input->post('amount')),
            'amount_discount' => convertCurrencyFormatToBaseAmount($this->input->post('amount_discount')),
            'amount_fine'     => convertCurrencyFormatToBaseAmount($this->input->post('amount_fine')),
            'date'            => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
            'description'     => $this->input->post('description'),
            'collected_by'    => $collected_by,
            'payment_mode'    => $this->input->post('payment_mode'),
            'received_by'     => $staff_record['id'],
        );


        
        
        
        $student_fees_master_id = $temp['id'];
        $transport_fees_id      = $this->input->post('transport_fees_id');


        $data = array(
            
            'student_fees_master_id' => $student_fees_master_id,
            'fee_groups_feetype_id'  => $fee_groups_feetype_id,
            'amount_detail'          => $json_array,
        );

    
        
        $send_to            = $this->input->post('guardian_phone');
        // $email              = $this->input->post('guardian_email');
        // $parent_app_key     = $this->input->post('parent_app_key');
        // $student_session_id = $this->input->post('student_session_id');
        $inserted_id        = $this->studentfeemaster_model->discount_fee_deposit($data, $send_to, $student_fees_discount_id);

        
        echo json_encode(['status' => 'success', 'message' => $inserted_id]);
        exit();
    }

}


?>






