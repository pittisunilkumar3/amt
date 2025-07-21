<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>

<style>
/* Multi-select dropdown enhancements for Finance Reports */
.SumoSelect {
    width: 100% !important;
}

.SumoSelect > .CaptionCont {
    border: 1px solid #d2d6de;
    border-radius: 3px;
    background-color: #fff;
    min-height: 34px;
    padding: 6px 12px;
}

.SumoSelect > .CaptionCont > span {
    line-height: 1.42857143;
    color: #555;
    padding-right: 20px;
}

.SumoSelect > .CaptionCont > span.placeholder {
    color: #999;
    font-style: italic;
}

.SumoSelect.open > .CaptionCont,
.SumoSelect:focus > .CaptionCont,
.SumoSelect:hover > .CaptionCont {
    border-color: #66afe9;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, .6);
}

.SumoSelect .optWrapper {
    border: 1px solid #d2d6de;
    border-radius: 3px;
    box-shadow: 0 6px 12px rgba(0,0,0,.175);
    background-color: #fff;
    z-index: 9999;
}

.SumoSelect .optWrapper ul.options {
    max-height: 200px;
    overflow-y: auto;
}

.SumoSelect .optWrapper ul.options li {
    padding: 8px 12px;
    border-bottom: 1px solid #f4f4f4;
}

.SumoSelect .optWrapper ul.options li:hover {
    background-color: #f5f5f5;
}

.SumoSelect .optWrapper ul.options li.selected {
    background-color: #337ab7;
    color: #fff;
}

.SumoSelect .search-txt {
    border: 1px solid #d2d6de;
    border-radius: 3px;
    padding: 6px 12px;
    margin: 5px;
    width: calc(100% - 10px);
}

/* Responsive design improvements */
@media (max-width: 768px) {
    .col-sm-2.col-lg-2.col-md-2 {
        margin-bottom: 15px;
    }

    .SumoSelect > .CaptionCont {
        min-height: 40px;
        padding: 8px 12px;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 5px;
    }
}

@media (max-width: 480px) {
    .SumoSelect > .CaptionCont {
        min-height: 44px;
        padding: 10px 12px;
    }
}

/* Form styling improvements */
.form-group label {
    margin-bottom: 5px;
    font-weight: 500;
}

/* Simple Excel-like table styling */
.table-columnwise {
    font-size: 11px;
    border-collapse: collapse;
    width: 100%;
    font-family: Arial, sans-serif;
    background-color: #ffffff;
}

/* Simple table container */
.table-responsive {
    overflow-x: auto;
    overflow-y: auto;
    margin: 0;
    padding: 0;
    background-color: #ffffff;
    border: 1px solid #000000;
}

/* Simple Excel-like headers */
.table-columnwise th {
    background-color: #f0f0f0;
    border: 1px solid #000000;
    padding: 5px;
    text-align: center;
    font-weight: bold;
    font-size: 11px;
}

/* Simple Excel-like cells */
.table-columnwise td {
    border: 1px solid #000000;
    padding: 5px;
    text-align: center;
    font-size: 11px;
    background-color: #ffffff;
}













</style>
<div class="content-wrapper">
    <section class="content-header"></section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('financereports/_finance');?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header ">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form role="form" action="<?php echo site_url('financereports/fee_collection_report_columnwise') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('search_duration'); ?><small class="req"> *</small></label>
                                    <select class="form-control" name="search_type" onchange="showdate(this.value)">

                                        <?php foreach ($searchlist as $key => $search) {
    ?>
                                            <option value="<?php echo $key ?>" <?php
if ((isset($search_type)) && ($search_type == $key)) {
        echo "selected";
    }
    ?>><?php echo $search ?></option>
                                                <?php }?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                </div>
                            </div>

                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('session'); ?></label>

                                    <select id="sch_session_id" name="sch_session_id[]" class="form-control multiselect-dropdown" multiple>
                                        <?php foreach ($sessionlist as $session) {
                                            ?>
                                            <option value="<?php echo $session['id'] ?>"

                                            <?php if (set_value('sch_session_id') == $session['id']) {echo "selected=selected";}?>><?php echo $session['session'] ?></option>
                                            <?php } ?>
                                    </select>

                                    <span class="text-danger" id="error_sch_session_id"></span>
                                </div>
                            </div>

                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('class'); ?></label>
                                    <select id="class_id" name="class_id[]" class="form-control multiselect-dropdown" multiple>
                                        <?php
                                            $count = 0;
                                            foreach ($classlist as $class) {
                                                ?>
                                            <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id') == $class['id']) {
                                                echo "selected=selected";
                                            }
                                            ?>><?php echo $class['class'] ?></option>
                                            <?php
                                        $count++;
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger" id="error_class_id"></span>
                                </div>
                            </div>

                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('section'); ?></label>
                                    <select id="section_id" name="section_id[]" class="form-control multiselect-dropdown" multiple>
                                    </select>
                                    <span class="text-danger" id="error_section_id"></span>
                                </div>
                            </div>

                            <div class="col-sm-2 col-lg-2 col-md-2">
                               <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('fees_type'); ?></label>

                                            <select id="feetype_id" name="feetype_id[]" class="form-control multiselect-dropdown" multiple>
                                                <?php
                                                    $count = 0;
                                                    foreach ($feetypeList as $feetype) {
                                                        ?>
                                                    <option value="<?php echo $feetype['id'] ?>"<?php
                                                    if (set_value('feetype_id') == $feetype['id']) {
                                                            echo "selected =selected";
                                                        }
                                                        ?>><?php echo $feetype['type'] ?></option>

                                                    <?php
                                                $count++;
                                                }
                                                ?>
                                            </select>
                                            <span class="text-danger" id="error_feetype_id"></span>
                                        </div>
                            </div>

                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('collect_by'); ?></label>

                                    <select id="collect_by" name="collect_by[]" class="form-control multiselect-dropdown" multiple>
                                        <?php
                                            $count = 0;
                                            foreach ($collect_by as $key => $value) {
                                                ?>
                                            <option value="<?php echo $key ?>"<?php
                                            if (set_value('collect_by') == $key) {
                                                    echo "selected =selected";
                                                }
                                                ?>><?php echo $value ?></option>

                                            <?php
                                        $count++;
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger" id="error_collect_by"></span>
                                </div>
                            </div>

                            <div id='date_result'>
                            </div>

                            <div class="col-sm-2 col-lg-2 col-md-2">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('group_by'); ?></label>

                                    <select class="form-control" name="group">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                            $count = 0;
                                            foreach ($group_by as $key => $value) {
                                                ?>
                                            <option value="<?php echo $key ?>"<?php
                                            if ((isset($group_byid)) && ($group_byid == $key)) {
                                                    echo "selected";
                                                }
                                                ?>><?php echo $value ?></option>

                                            <?php
                                        $count++;
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('group'); ?></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" id="search_btn" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
 <?php
if (empty($results)) {
    ?>
<div class="box-header ptbnull">
    <div class="alert alert-info">
       <?php echo $this->lang->line('no_record_found'); ?>
    </div>
</div>
                                        <?php
} else {
    ?>
                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i> <?php echo $this->lang->line('fee_collection_report_column_wise'); ?></h3>
                        </div>




                        <div class="box-body table-responsive" id="transfee">
                        <?php if (isset($error_message)) { ?>
                            <div class="alert alert-danger">
                                <strong>Error:</strong> <?php echo $error_message; ?>
                            </div>
                        <?php } ?>
                        <div id="printhead"><center><b><h4><?php echo $this->lang->line('fee_collection_report_column_wise') . "<br>";
    $this->customlib->get_postmessage();
    ?></h4></b></center></div>
                            <div class="download_label"><?php echo $this->lang->line('fee_collection_report_column_wise') . "<br>";
    $this->customlib->get_postmessage();
    ?></div>





                            <!-- Table Container with Enhanced Styling -->
                            <div class="table-container">
                                <table class="table table-striped table-bordered table-hover table-columnwise" id="headerTable">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" style="vertical-align: middle;"><?php echo $this->lang->line('admission_no'); ?></th>
                                            <th rowspan="2" style="vertical-align: middle;"><?php echo $this->lang->line('student_name'); ?></th>
                                            <th rowspan="2" style="vertical-align: middle;"><?php echo $this->lang->line('class'); ?></th>
                                            <th rowspan="2" style="vertical-align: middle;"><?php echo $this->lang->line('section'); ?></th>
                                            <?php
                                            $total_by_type = array();
                                            foreach ($fee_types as $fee_type) {
                                                $total_by_type[$fee_type['type']] = array(
                                                    'total_amount' => 0,
                                                    'paid_amount' => 0,
                                                    'remaining_amount' => 0
                                                );
                                            ?>
                                                <th colspan="3" style="text-align: center; background-color: #f5f5f5;"><?php echo $fee_type['type']; ?></th>
                                            <?php } ?>
                                            <th rowspan="2" style="vertical-align: middle; background-color: #e8f4fd;"><?php echo $this->lang->line('grand_total'); ?></th>
                                        </tr>
                                        <tr>
                                            <?php foreach ($fee_types as $fee_type) { ?>
                                                <th style="text-align: center; font-size: 11px; background-color: #f9f9f9;">Total Amount</th>
                                                <th style="text-align: center; font-size: 11px; background-color: #e8f5e8;">Paid Amount</th>
                                                <th style="text-align: center; font-size: 11px; background-color: #ffe8e8;">Remaining</th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $grand_total = 0;
                                        // Initialize totals array if not already done
                                        if (!isset($total_by_type) || empty($total_by_type)) {
                                            $total_by_type = array();
                                            if (isset($fee_types) && is_array($fee_types)) {
                                                foreach ($fee_types as $fee_type) {
                                                    $total_by_type[$fee_type['type']] = array(
                                                        'total_amount' => 0,
                                                        'paid_amount' => 0,
                                                        'remaining_amount' => 0
                                                    );
                                                }
                                            }
                                        }

                                        if (isset($results) && is_array($results)) {
                                            foreach ($results as $student) {
                                                $student_total = 0;
                                        ?>
                                            <tr>
                                                <td class="student-info"><?php echo $student['admission_no']; ?></td>
                                                <td class="student-info"><?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></td>
                                                <td><?php echo $student['class']; ?></td>
                                                <td><?php echo $student['section']; ?></td>
                                                <?php foreach ($fee_types as $fee_type) {
                                                    $fee_data = isset($student['fee_types'][$fee_type['type']]) ? $student['fee_types'][$fee_type['type']] : array(
                                                        'total_amount' => 0,
                                                        'paid_amount' => 0,
                                                        'remaining_amount' => 0,
                                                        'payments' => array()
                                                    );

                                                    // Handle old format (just amount) vs new format (detailed data)
                                                    if (is_numeric($fee_data)) {
                                                        $paid_amount = $fee_data;
                                                        $total_amount = $fee_data;
                                                        $remaining_amount = 0;
                                                        $payments = array();
                                                    } else {
                                                        $paid_amount = $fee_data['paid_amount'];
                                                        $total_amount = $fee_data['total_amount'];
                                                        $remaining_amount = $fee_data['remaining_amount'];
                                                        $payments = $fee_data['payments'];
                                                    }

                                                    $student_total += $paid_amount;
                                                    $total_by_type[$fee_type['type']]['total_amount'] += $total_amount;
                                                    $total_by_type[$fee_type['type']]['paid_amount'] += $paid_amount;
                                                    $total_by_type[$fee_type['type']]['remaining_amount'] += $remaining_amount;

                                                    // Create tooltip with payment details
                                                    $tooltip = '';
                                                    if (!empty($payments)) {
                                                        $tooltip = 'title="Payment Details:\n';
                                                        foreach ($payments as $payment) {
                                                            $tooltip .= 'Date: ' . $payment['date'] . ', Amount: ' . $currency_symbol . number_format($payment['amount'], 2) . ', By: ' . $payment['collected_by'] . '\n';
                                                        }
                                                        $tooltip .= '"';
                                                    }
                                                ?>
                                                    <!-- Total Amount -->
                                                    <td class="amount-cell">
                                                        <?php echo ($total_amount > 0) ? $currency_symbol . number_format($total_amount, 0) : '-'; ?>
                                                    </td>
                                                    <!-- Paid Amount -->
                                                    <td class="amount-cell" <?php echo $tooltip; ?>>
                                                        <?php echo ($paid_amount > 0) ? $currency_symbol . number_format($paid_amount, 0) : '-'; ?>
                                                    </td>
                                                    <!-- Remaining Amount -->
                                                    <td class="amount-cell">
                                                        <?php echo ($remaining_amount > 0) ? $currency_symbol . number_format($remaining_amount, 0) : '-'; ?>
                                                    </td>
                                                <?php } ?>
                                                <td class="total-cell">
                                                    <strong><?php echo $currency_symbol . number_format($student_total, 0); ?></strong>
                                                </td>
                                            </tr>
                                        <?php
                                                $grand_total += $student_total;
                                            } // end foreach
                                        } // end if isset($results) ?>
                                    </tbody>
                                    <tfoot>
                                        <?php
                                        $grand_total_amount = 0;
                                        $grand_paid_amount = 0;
                                        $grand_remaining_amount = 0;

                                        foreach ($fee_types as $fee_type) {
                                            $type_totals = $total_by_type[$fee_type['type']];
                                            $grand_total_amount += $type_totals['total_amount'];
                                            $grand_paid_amount += $type_totals['paid_amount'];
                                            $grand_remaining_amount += $type_totals['remaining_amount'];
                                        }
                                        ?>

                                        <!-- Grand Total (Total Assigned) Row -->
                                        <tr style="font-weight: bold;">
                                            <td colspan="4"><strong>Grand Total</strong></td>
                                            <?php foreach ($fee_types as $fee_type) {
                                                $type_totals = $total_by_type[$fee_type['type']];
                                            ?>
                                                <td><strong><?php echo $currency_symbol . number_format($type_totals['total_amount'], 0); ?></strong></td>
                                                <td>-</td>
                                                <td>-</td>
                                            <?php } ?>
                                            <td><strong><?php echo $currency_symbol . number_format($grand_total_amount, 0); ?></strong></td>
                                        </tr>

                                        <!-- Grand Paid (Total Collected) Row -->
                                        <tr style="font-weight: bold;">
                                            <td colspan="4"><strong>Grand Paid</strong></td>
                                            <?php foreach ($fee_types as $fee_type) {
                                                $type_totals = $total_by_type[$fee_type['type']];
                                            ?>
                                                <td>-</td>
                                                <td><strong><?php echo $currency_symbol . number_format($type_totals['paid_amount'], 0); ?></strong></td>
                                                <td>-</td>
                                            <?php } ?>
                                            <td><strong><?php echo $currency_symbol . number_format($grand_paid_amount, 0); ?></strong></td>
                                        </tr>

                                        <!-- Grand Pending (Total Remaining) Row -->
                                        <tr style="font-weight: bold;">
                                            <td colspan="4"><strong>Grand Pending</strong></td>
                                            <?php foreach ($fee_types as $fee_type) {
                                                $type_totals = $total_by_type[$fee_type['type']];
                                            ?>
                                                <td>-</td>
                                                <td>-</td>
                                                <td><strong><?php echo $currency_symbol . number_format($type_totals['remaining_amount'], 0); ?></strong></td>
                                            <?php } ?>
                                            <td><strong><?php echo $currency_symbol . number_format($grand_remaining_amount, 0); ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div> <!-- End table-container -->
                        </div>
                    </div>
                                        <?php
}
?>
                </div>
            </div>
        </div>
    </section>
</div>
<iframe id="txtArea1" style="display:none"></iframe>

<script>
    $(document).ready(function () {
        console.log('Document ready, jQuery version:', $.fn.jquery);
        console.log('Found multiselect dropdowns:', $('.multiselect-dropdown').length);

        // Check if SumoSelect is available
        if (typeof $.fn.SumoSelect === 'undefined') {
            console.error('SumoSelect plugin not loaded!');
            return;
        }

        // Add a small delay to ensure DOM is fully rendered
        setTimeout(function() {
            console.log('Initializing SumoSelect...');

            // Initialize SumoSelect for all multi-select dropdowns
            $('.multiselect-dropdown').each(function() {
                console.log('Initializing dropdown:', $(this).attr('id'));
                $(this).SumoSelect({
                    placeholder: 'Select Options',
                    csvDispCount: 3,
                    captionFormat: '{0} Selected',
                    captionFormatAllSelected: 'All Selected ({0})',
                    selectAll: true,
                    search: true,
                    searchText: 'Search...',
                    noMatch: 'No matches found "{0}"',
                    okCancelInMulti: true,
                    isClickAwayOk: true,
                    locale: ['OK', 'Cancel', 'Select All'],
                    up: false,
                    showTitle: true
                });
            });

            console.log('SumoSelect initialization complete');
        }, 100);

    // Initialize section dropdown on page load if class is pre-selected
    var preSelectedClass = $('#class_id').val();
    if (preSelectedClass && preSelectedClass.length > 0) {
        $('#class_id').trigger('change');
    }

    // Handle class dropdown changes for section population
    $(document).on('change', '#class_id', function (e) {
        var sectionDropdown = $('#section_id')[0];
        if (sectionDropdown && sectionDropdown.sumo) {
            sectionDropdown.sumo.removeAll();
        }

        var class_ids = $(this).val();
        var base_url = '<?php echo base_url() ?>';

        if (class_ids && class_ids.length > 0) {
            var requests = [];
            var allSections = [];
            var addedSections = {};

            // Get sections for all selected classes
            $.each(class_ids, function(index, class_id) {
                requests.push(
                    $.ajax({
                        type: "GET",
                        url: base_url + "sections/getByClass",
                        data: {'class_id': class_id},
                        dataType: "json",
                        success: function(data) {
                            if (data && Array.isArray(data)) {
                                $.each(data, function(i, obj) {
                                    // Avoid duplicate sections
                                    if (!addedSections[obj.section_id]) {
                                        allSections.push({
                                            value: obj.section_id,
                                            text: obj.section
                                        });
                                        addedSections[obj.section_id] = true;
                                    }
                                });
                            }
                        }
                    })
                );
            });

            // Wait for all requests to complete
            $.when.apply($, requests).done(function() {
                // Add sections to dropdown
                if (sectionDropdown && sectionDropdown.sumo && allSections.length > 0) {
                    $.each(allSections, function(index, section) {
                        sectionDropdown.sumo.add(section.value, section.text);
                    });
                    // Refresh the dropdown to ensure proper display
                    sectionDropdown.sumo.reload();
                }
            });
        }
    });

    // Helper functions for user feedback
    function showSuccessMessage(message) {
        $('.alert').remove(); // Remove any existing alerts
        var alertHtml = '<div class="alert alert-success alert-dismissible" role="alert">' +
                       '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                       '<span aria-hidden="true">&times;</span></button>' +
                       '<i class="fa fa-check-circle"></i> ' + message +
                       '</div>';
        $('.box-body.row').prepend(alertHtml);

        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert-success').fadeOut();
        }, 5000);
    }

    function showErrorMessage(message) {
        $('.alert').remove(); // Remove any existing alerts
        var alertHtml = '<div class="alert alert-danger alert-dismissible" role="alert">' +
                       '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                       '<span aria-hidden="true">&times;</span></button>' +
                       '<i class="fa fa-exclamation-triangle"></i> ' + message +
                       '</div>';
        $('.box-body.row').prepend(alertHtml);

        // Auto-hide after 8 seconds
        setTimeout(function() {
            $('.alert-danger').fadeOut();
        }, 8000);
    }

    // Enhanced loading state for SumoSelect dropdowns
    function showDropdownLoading(selector) {
        $(selector).prop('disabled', true);
        $(selector).next('.SumoSelect').addClass('loading');
    }

    function hideDropdownLoading(selector) {
        $(selector).prop('disabled', false);
        $(selector).next('.SumoSelect').removeClass('loading');
    }

    function showdate(value) {
        if (value == 'period') {
            $('.search_date').show();
        } else {
            $('.search_date').hide();
        }
    }



    // Form validation
    $('form').on('submit', function(e) {
        var search_type = $('select[name="search_type"]').val();
        if (!search_type) {
            e.preventDefault();
            alert('<?php echo $this->lang->line("please_select_search_duration"); ?>');
            return false;
        }
        return true;
    });

    // Clear error messages when user makes selections
    $('.multiselect-dropdown').on('sumo:closed', function() {
        var fieldName = $(this).attr('name');
        if (fieldName) {
            var errorElement = $('#error_' + fieldName.replace('[]', ''));
            if (errorElement.length) {
                errorElement.text('');
            }
        }
    });

    // Show/hide date fields based on search type
    showdate($('select[name="search_type"]').val());
});

<?php
if ($search_type == 'period') {
    ?>

        $(document).ready(function () {
            showdate('period');
        });

    <?php
}
?>


</script>


