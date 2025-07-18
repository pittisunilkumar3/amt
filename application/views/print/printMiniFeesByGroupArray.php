<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<!DOCTYPE html>
<html>
<head>
<style type="text/css">
    .receipt {
        width: 350px;
        border: 1px solid #000;
        padding: 12px;
        background-color: white;
    }
    
    .header {
        position: relative;
        text-align: center;
        margin-bottom: 12px;
        padding-top: 12px;
    }
    
    .logo {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 38px;
        height: 38px;
    }
    
    .college-info {
        margin: 0 auto;
        padding: 0 40px;
    }
    
    .college-name {
        font-weight: bold;
        font-size: 12px;
        margin-bottom: 4px;
        text-transform: uppercase;
    }
    
    .college-address {
        font-size: 9px;
        margin: 0 auto;
        max-width: 90%;
        line-height: 1.3;
    }
    
    .receipt-title {
        text-align: center;
        font-weight: bold;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        padding: 5px 0;
        margin: 12px 0;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 11px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        margin: 6px 0;
        font-size: 10px;
        flex-wrap: wrap;
        padding: 0;
    }
    
    .info-row span {
        max-width: 50%;
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
        line-height: 1.3;
        padding-right: 0;
    }

    /* Modified student name styling */
    .info-row .student-name {
        max-width: 48%;
        text-align: right;
        margin-left: auto;
        word-wrap: break-word;
        height: auto;
        min-height: fit-content;
    }

    .info-row + .info-row {
        margin-top: 8px;
    }
    
    .fee-table {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 9px;
    }
    
    .fee-table th, .fee-table td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .fee-table th {
        font-size: 9px;
        background-color: #f8f9fa;
    }
    
    .fee-table th:nth-child(1) {
        width: 40%;
    }
    
    .fee-table th:nth-child(2),
    .fee-table th:nth-child(3),
    .fee-table th:nth-child(4) {
        width: 20%;
    }
    
    .fee-table td {
        padding: 4px;
    }
    
    .payment-info-row {
        display: flex;
        justify-content: space-between;
        margin: 6px 0;
        font-size: 10px;
    }
    
    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        font-size: 9px;
    }
    
    .signature-container {
        margin-top: 25px;
        text-align: center;
        font-size: 9px;
        width: 45%;
    }
    
    .footer {
        font-style: italic;
        font-size: 9px;
        margin-top: 15px;
        text-align: center;
        width: 100%;
    }
    
    hr {
        border: none;
        border-top: 1px solid #000;
        margin: 10px 0;
    }

    .fee-table td:nth-child(2),
    .fee-table td:nth-child(3),
    .fee-table td:nth-child(4) {
        text-align: right;
        padding-right: 6px;
    }
</style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <img src="<?php echo $this->media_storage->getImageURL('uploads/school_content/logo/'.$sch_setting->image); ?>" alt="College Logo" class="logo">
            <div class="college-info">
                <div class="college-name"><?php echo $sch_setting->name;?></div>
                <div class="college-address"><?php echo $sch_setting->address;?></div>
            </div>
        </div>

        <div class="receipt-title">
            FEE RECEIPT (Office Copy)
        </div>

        <div class="info-row">
            <span><b>Date: </b><?php
                $date = date('d-m-Y');
                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
            ?></span>
            <span><b>Academic: </b><?php echo $sch_setting->session;?></span>
        </div>
        
        <hr>
        
        <div class="info-row">
            <span><b>Application Id: </b><?php echo $feearray[0]->admission_no; ?></span>
            <span><b>Student Name:</b> <?php echo $this->customlib->getFullName($feearray[0]->firstname, $feearray[0]->middlename,$feearray[0]->lastname,$sch_setting->middlename,$sch_setting->lastname); ?></span>
        </div>
        
        <div class="info-row">
            <span><b>Section: </b><?php echo $feearray[0]->section; ?></span>
            <span><b>Class: </b><?php echo $feearray[0]->class; ?></span>
        </div>

        <table class="fee-table">
            <?php
            $total_amount = 0;
            $total_deposite_amount = 0;
            $total_fine_amount = 0;
            $total_discount_amount = 0;
            $total_balance_amount = 0;
            $alot_fee_discount = 0;
            ?>
            <tr>
                <th>Fee Head</th>
                <th>Total fee</th>
                <th>Paid fee</th>
                <th>Balance fee</th>
            </tr>
            <?php 
            foreach ($feearray as $fee_key => $feeList) {
                $fee_discount = 0;
                $fee_paid = 0;
                $fee_fine = 0;
                if (!empty($feeList->amount_detail)) {
                    $fee_deposits = json_decode(($feeList->amount_detail));
                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                        $fee_paid = $fee_paid + $fee_deposits_value->amount;
                        $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                        $fee_fine = $fee_fine + $fee_deposits_value->amount_fine;
                    }
                }
                $feetype_balance = $feeList->amount - ($fee_paid + $fee_discount);
                $total_amount = $total_amount + $feeList->amount;
                $total_discount_amount = $total_discount_amount + $fee_discount;
                $total_fine_amount = $total_fine_amount + $fee_fine;
                $total_deposite_amount = $total_deposite_amount + $fee_paid;
                $total_balance_amount = $total_balance_amount + $feetype_balance;
            ?>
            <tr>
                <td>
                    <?php
                        if ($feeList->is_system) {
                            echo $this->lang->line($feeList->name) . " (" . $this->lang->line($feeList->type) . ")";
                        } else {
                            echo $feeList->name . " (" . $feeList->type . ")";
                        }
                    ?>
                </td>
                <td><?php echo $feeList->amount; ?></td>
                <td><?php echo $fee_paid; ?></td>
                <td><?php echo $feetype_balance; ?></td>
            </tr>
            <?php } ?>
            
            <tr style="font-weight: bold;">
                <td></td>
                <td><?php echo $total_amount; ?></td>
                <td><?php echo $total_deposite_amount; ?></td>
                <td><?php echo $total_balance_amount; ?></td>
            </tr>
        </table>

        <div class="payment-info-row">
            <span>Balance Amount: <?php echo $total_balance_amount; ?></span>
            <span>Paid By: Cash</span>
        </div>

        <hr>
        
        <div class="signature-section">
            <div class="signature-container">Student Signature</div>
            <div class="signature-container">Authorised Signatory / Cashier</div>
        </div>

        <div class="footer">
            <?php echo $this->setting_model->get_receiptfooter(); ?>
        </div>
    </div>
</body>
</html>