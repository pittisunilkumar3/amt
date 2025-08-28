<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class AdvancePayment_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    /**
     * Get advance payment records
     * @param int $id
     * @return mixed
     */
    public function get($id = null)
    {
        $this->db->select('sap.*, ss.student_id, s.firstname, s.middlename, s.lastname, s.admission_no, c.class, sec.section')
                 ->from('advance_payments sap')
                 ->join('student_session ss', 'sap.student_session_id = ss.id')
                 ->join('students s', 'ss.student_id = s.id')
                 ->join('classes c', 'ss.class_id = c.id')
                 ->join('sections sec', 'ss.section_id = sec.id')
                 ->where('sap.is_active', 'yes');
        
        if ($id != null) {
            $this->db->where('sap.id', $id);
            return $this->db->get()->row();
        } else {
            $this->db->order_by('sap.id', 'DESC');
            return $this->db->get()->result();
        }
    }

    /**
     * Get advance balance for a student
     * @param int $student_session_id
     * @return float
     */
    public function getAdvanceBalance($student_session_id)
    {
        $this->db->select('SUM(balance) as total_balance')
                 ->from('advance_payments')
                 ->where('student_session_id', $student_session_id)
                 ->where('is_active', 'yes')
                 ->where('balance >', 0);
        
        $result = $this->db->get()->row();
        return $result ? (float)$result->total_balance : 0.00;
    }

    /**
     * Get advance payments for a student
     * @param int $student_session_id
     * @return array
     */
    public function getStudentAdvancePayments($student_session_id)
    {
        $this->db->select('*')
                 ->from('advance_payments')
                 ->where('student_session_id', $student_session_id)
                 ->where('is_active', 'yes')
                 ->order_by('payment_date', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Add or update advance payment
     * @param array $data
     * @return int
     */
    public function add($data)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);

        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('advance_payments', $data);
            $message = UPDATE_RECORD_CONSTANT . " On advance payment id " . $data['id'];
            $action = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
        } else {
            // Set balance equal to amount for new payments
            if (!isset($data['balance'])) {
                $data['balance'] = $data['amount'];
            }
            
            $this->db->insert('advance_payments', $data);
            $id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On advance payment id " . $id;
            $action = "Insert";
            $record_id = $id;
            $this->log($message, $record_id, $action);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return isset($data['id']) ? $data['id'] : $id;
        }
    }

    /**
     * Apply advance payment to fee
     * @param int $advance_payment_id
     * @param float $amount_to_use
     * @param int $student_fees_deposite_id
     * @param string $fee_category
     * @param string $description
     * @return bool
     */
    public function applyAdvanceToFee($advance_payment_id, $amount_to_use, $student_fees_deposite_id = null, $student_fees_depositeadding_id = null, $fee_category = 'fees', $description = '')
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);

        // Get current advance payment
        $advance_payment = $this->db->get_where('advance_payments', array('id' => $advance_payment_id))->row();
        
        if (!$advance_payment || $advance_payment->balance < $amount_to_use) {
            return false;
        }

        // Update advance payment balance
        $new_balance = $advance_payment->balance - $amount_to_use;
        $this->db->where('id', $advance_payment_id);
        $this->db->update('advance_payments', array('balance' => $new_balance));

        // Record usage
        $usage_data = array(
            'advance_payment_id' => $advance_payment_id,
            'student_fees_deposite_id' => $student_fees_deposite_id,
            'student_fees_depositeadding_id' => $student_fees_depositeadding_id,
            'amount_used' => $amount_to_use,
            'usage_date' => date('Y-m-d'),
            'fee_category' => $fee_category,
            'description' => $description
        );
        
        $this->db->insert('advance_payment_usage', $usage_data);
        $usage_id = $this->db->insert_id();

        $message = INSERT_RECORD_CONSTANT . " On advance payment usage id " . $usage_id;
        $action = "Insert";
        $this->log($message, $usage_id, $action);

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * Get available advance payments for a student (with balance > 0)
     * @param int $student_session_id
     * @return array
     */
    public function getAvailableAdvancePayments($student_session_id)
    {
        $this->db->select('*')
                 ->from('advance_payments')
                 ->where('student_session_id', $student_session_id)
                 ->where('is_active', 'yes')
                 ->where('balance >', 0)
                 ->order_by('payment_date', 'ASC'); // Use oldest payments first
        
        return $this->db->get()->result();
    }

    /**
     * Get advance payment usage history
     * @param int $advance_payment_id
     * @return array
     */
    public function getAdvanceUsageHistory($advance_payment_id = null, $student_session_id = null)
    {
        $this->db->select('apu.*, sap.invoice_id as advance_invoice_id, sap.payment_date as advance_payment_date')
                 ->from('advance_payment_usage apu')
                 ->join('advance_payments sap', 'apu.advance_payment_id = sap.id');
        
        if ($advance_payment_id) {
            $this->db->where('apu.advance_payment_id', $advance_payment_id);
        }
        
        if ($student_session_id) {
            $this->db->where('sap.student_session_id', $student_session_id);
        }
        
        $this->db->order_by('apu.usage_date', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Generate advance payment invoice ID
     * @return string
     */
    public function generateAdvanceInvoiceId()
    {
        $prefix = 'ADV';
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $this->db->select('invoice_id')
                 ->from('advance_payments')
                 ->like('invoice_id', $prefix . $year . $month, 'after')
                 ->order_by('id', 'DESC')
                 ->limit(1);
        
        $result = $this->db->get()->row();
        
        if ($result) {
            $last_number = (int)substr($result->invoice_id, -4);
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }
        
        return $prefix . $year . $month . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Delete advance payment (soft delete)
     * @param int $id
     * @return bool
     */
    public function remove($id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);

        $this->db->where('id', $id);
        $this->db->update('advance_payments', array('is_active' => 'no'));
        
        $message = DELETE_RECORD_CONSTANT . " On advance payment id " . $id;
        $action = "Delete";
        $this->log($message, $id, $action);

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * Get advance payment summary for reports
     * @param string $start_date
     * @param string $end_date
     * @param int $class_id
     * @param int $section_id
     * @return array
     */
    public function getAdvancePaymentReport($start_date = null, $end_date = null, $class_id = null, $section_id = null)
    {
        $this->db->select('sap.*, ss.student_id, s.firstname, s.middlename, s.lastname, s.admission_no, c.class, sec.section')
                 ->from('advance_payments sap')
                 ->join('student_session ss', 'sap.student_session_id = ss.id')
                 ->join('students s', 'ss.student_id = s.id')
                 ->join('classes c', 'ss.class_id = c.id')
                 ->join('sections sec', 'ss.section_id = sec.id')
                 ->where('sap.is_active', 'yes');
        
        if ($start_date && $end_date) {
            $this->db->where('sap.payment_date >=', $start_date);
            $this->db->where('sap.payment_date <=', $end_date);
        }
        
        if ($class_id) {
            $this->db->where('ss.class_id', $class_id);
        }
        
        if ($section_id) {
            $this->db->where('ss.section_id', $section_id);
        }
        
        $this->db->order_by('sap.payment_date', 'DESC');
        return $this->db->get()->result();
    }
}
