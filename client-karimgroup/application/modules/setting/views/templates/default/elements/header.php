<style type="text/css">
    ul.dropdown-menu li {
        display: block;
        float: none;
    }

    body {
        font-size: 12px !important;
    }

    .btn-default {
        background-color: #ffffff !important;
        *background-color: #ffffff !important;
        background-image: none !important;
        background-image: none !important;
        background-image: none !important;
        background-image: none !important;
        background-image: none !important;
        background-image: none !important;
    }

    .breadcrumb>li+li::before {
        color: #ccc;
        content: "";
        padding: 0 5px;
    }

    .breadcrumb>li+li {
        width: 180px;
    }
</style>
<div class="flash_message"></div>
<?php
if ($this->session->userdata('logged_in') && $this->page_name != 'home') {
    $companyId = $this->session->userdata('companyId');
?>
    <?php
    $user_id = $this->session->userdata('user_id');
    $userData = $this->m_common->get_row_array('users', array('user_id' => $user_id), '*');
    // $this->role = checkUserPermission(10, '', $userData);
    ?>
    <nav class="navbar navbar-default" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only"><?php echo lang('Toggle_navigation'); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">

                <li><a style="margin:0px; padding:0px;padding:5px 5px 0px 0px;" href="dashboard"><img src="<?php echo site_url("images/logo.png"); ?>" alt="User Image" style="height: 50px; width: 100px;" /></a></li>
                <?php $this->menu = checkMenuPermission(1, $userData);
                if (1 == $this->menu) {
                ?>
                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'dashboard')) echo 'class="active"'; ?> style="margin-top:0.5%"><a href="dashboard">Dashboard</a></li>
                <?php } ?>

                <?php if (!empty($companyId)) { ?>
                    <?php $this->menu = checkMenuPermission(2, $userData);
                    if (2 == $this->menu) {
                    ?>
                        <li style="margin-right: 10px;margin-top: 1.5%;">
                            <?php if (!empty($this->page_name) && !empty($this->menu_name) && ($this->menu_name == "people")) { ?>
                                <button style="background-color:#E7E7E7 !important;" class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <?php echo "Employee"; ?> <span class="caret"></span>
                                </button>
                            <?php } else { ?>
                                <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <?php echo "Employee"; ?> <span class="caret"></span>
                                </button>
                            <?php } ?>
                            <ul class="dropdown-menu">
                                <?php $this->role = checkUserPermission(2, 6, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) { ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'requisition')) echo 'class="active"'; ?>><a href="dashboard/requisition">Manpower Requisition</a></li>
                                <?php } ?>
                                <?php $this->role = checkUserPermission(2, 1, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) { ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'selectedcandidate')) echo 'class="active"'; ?>><a href="dashboard/crud/recruitment">Selected Candidate</a></li>
                                <?php } ?>
                                <?php $this->role = checkUserPermission(2, 2, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) { ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'recruitment')) echo 'class="active"'; ?>><a href="dashboard/recruitment">Recruitment</a></li>
                                <?php } ?>
                                <?php $this->role = checkUserPermission(2, 2, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) { ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'appointment')) echo 'class="active"'; ?>><a href="dashboard/appointment">Appointment</a></li>
                                <?php } ?>
                                <?php $this->role = checkUserPermission(2, 3, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) { ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'employee')) echo 'class="active"'; ?>><a href="dashboard/crud/serviceInfo">Employee</a></li>
                                <?php } ?>
                                <?php $this->role = checkUserPermission(2, 4, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) { ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'resignedEmployee')) echo 'class="active"'; ?>><a href="dashboard/crud/resignedEmployee">Resigned Employee</a></li>
                                <?php } ?>
                                <?php $this->role = checkUserPermission(2, 5, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) { ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'manpowerSetup')) echo 'class="active"'; ?>><a href="dashboard/crud/manpowerSetup">Manpower Setup</a></li>
                                <?php } ?>

                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'workprogram')) echo 'class="active"'; ?>><a href="dashboard/work_program">Work Program</a></li>




                            </ul>
                        </li>
                    <?php } ?>

                    <?php $this->menu = checkMenuPermission(3, $userData);
                    if (3 == $this->menu) {
                    ?>
                        <li style="margin-right: 10px;margin-top: 1.5%;">
                            <?php if (!empty($this->page_name) && !empty($this->menu_name) && ($this->menu_name == "leave")) { ?>
                                <button style="background-color:#E7E7E7 !important;" class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <?php echo "Leave"; ?> <span class="caret"></span>
                                </button>
                            <?php } else { ?>
                                <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <?php echo "Leave"; ?> <span class="caret"></span>
                                </button>
                            <?php } ?>
                            <ul class="dropdown-menu">
                                <?php $this->role = checkUserPermission(3, 8, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'leave')) echo 'class="active"'; ?>><a href="dashboard/leave">Leave Register</a></li>
                                <?php } ?>

                                <!--   <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'short-leave')) echo 'class="active"'; ?>><a href="dashboard/short_leave">Short Leave Register</a></li> -->
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'half-leave')) echo 'class="active"'; ?>><a href="dashboard/half_leave">Half Leave Register</a></li>

                                <?php $this->role = checkUserPermission(3, 9, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'leaveType')) echo 'class="active"'; ?>><a href="dashboard/crud/leaveType">Leave Type</a></li>
                                <?php } ?>

                                <!--                            <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'holiday')) echo 'class="active"'; ?>><a href="dashboard/crud/holiday">Holiday</a></li>-->
                                <?php $this->role = checkUserPermission(3, 10, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'holidayCalendar')) echo 'class="active"'; ?>><a href="dashboard/calendar">Holiday Calendar</a></li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <?php $this->menu = checkMenuPermission(4, $userData);
                    if (4 == $this->menu) {
                    ?>
                        <li style="margin-right: 10px;margin-top: 1.5%;">
                            <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                <?php echo "Attendance"; ?> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <?php $this->role = checkUserPermission(4, 11, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'attendance')) echo 'class="active"'; ?>><a href="employee/employee/attendanceView">Attendance</a></li>
                                <?php } ?>

                                <?php $this->role = checkUserPermission(4, 11, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'nonattendance')) echo 'class="active"'; ?>><a href="employee/employee/nonstaffAttendance">Non-Staff Attendance</a></li>
                                <?php } ?>
                                <?php $this->role = checkUserPermission(4, 11, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <!--    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'absentreduce')) echo 'class="active"'; ?>><a href="employee/employee/absent_reduce">Absent reduce</a></li>-->
                                <?php } ?>

                                <?php $this->role = checkUserPermission(4, 12, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'roaster')) echo 'class="active"'; ?>><a href="employee/roaster">Duty Roaster</a></li>
                                <?php } ?>

                                <?php $this->role = checkUserPermission(4, 12, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'updateMovementAttendance')) echo 'class="active"'; ?>><a href="dashboard/shortMovementAttendance">Update Movement Attendance</a></li>
                                <?php } ?>


                                <?php $this->role = checkUserPermission(4, 13, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'changeShift')) echo 'class="active"'; ?>><a href="employee/changeShift">Shift Change</a></li>
                                <?php } ?>
                                <!--            
                                <?php $this->role = checkUserPermission(4, 14, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'lateInfo')) echo 'class="active"'; ?>><a href="employee/lateInfo">Late Approval Register</a></li>
                        <?php } ?>
                              <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'earlyInfo')) echo 'class="active"'; ?>><a href="employee/earlyInfo">Early Approval Register</a></li>  
                        -->
                                <?php $this->role = checkUserPermission(4, 15, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'shortmovementRegister')) echo 'class="active"'; ?>><a href="employee/shortMovementRegister">Short Movement Register</a></li>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'movementRegister')) echo 'class="active"'; ?>><a href="employee/movementRegister">Full Day Movement Register</a></li>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'updateAttendance')) echo 'class="active"'; ?>><a href="dashboard/updateAttendance">Update Attendance</a></li>
                                <?php } ?>

                                <?php $this->role = checkUserPermission(4, 16, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <!--    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'weekEndWork')) echo 'class="active"'; ?>><a href="employee/weekEndWork">Weekend Work</a></li> -->
                                <?php } ?>
                                <?php $this->role = checkUserPermission(4, 16, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>

                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <?php $this->menu = checkMenuPermission(5, $userData);
                    if (5 == $this->menu) {
                    ?>
                        <li style="margin-right: 10px;margin-top: 1.5%;">

                            <?php if (!empty($this->page_name) && !empty($this->menu_name) && ($this->menu_name == "payroll")) { ?>
                                <button style="background-color:#E7E7E7 !important;" class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <?php echo "Payroll"; ?> <span class="caret"></span>
                                </button>
                            <?php } else { ?>
                                <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <?php echo "Payroll"; ?> <span class="caret"></span>
                                </button>
                            <?php } ?>
                            <ul class="dropdown-menu">

                                <?php $this->role = checkUserPermission(5, 18, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Company Bank')) echo 'class="active"'; ?>><a href="payroll/payroll/companyBank">Company Bank </a></li>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Employee Bank')) echo 'class="active"'; ?>><a href="payroll/payroll/bank">Employee Bank </a></li>

                                <?php } ?>

                                <?php $this->role = checkUserPermission(5, 17, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Pay Slip')) echo 'class="active"'; ?>><a href="payroll/payslip">Pay-Slip </a></li>
                                <?php } ?>

                                <?php $this->role = checkUserPermission(5, 18, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Salary Info')) echo 'class="active"'; ?>><a href="payroll/payroll/employeeSalaryInfo">Salary Info </a></li>
                                <?php } ?>


                                <?php $this->role = checkUserPermission(5, 18, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Arrear and Other Deduction')) echo 'class="active"'; ?>><a href="payroll/payroll/overTimeArrearPenaltyView">Deduction</a></li>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Salary Arrear')) echo 'class="active"'; ?>><a href="payroll/salary_arrear">Salary Arrear</a></li>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Absent Payment')) echo 'class="active"'; ?>><a href="payroll/payroll/absentPaymentView">Absent Arrear</a></li>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Increment')) echo 'class="active"'; ?>><a href="payroll/payroll/increment">Increment </a></li>
                                <?php } ?>

                                <?php $this->role = checkUserPermission(5, 19, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Loan Management')) echo 'class="active"'; ?>><a href="payroll/loan">Loan Register </a></li>
                                <?php } ?>

                                <?php $this->role = checkUserPermission(5, 43, $userData);
                                if (empty($this->role) || !in_array(11, $this->role)) {
                                ?>
                                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Meal Settings')) echo 'class="active"'; ?>><a href="payroll/meal_settings">Meal Settings</a></li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                <?php } ?>


                <?php $this->menu = checkMenuPermission(6, $userData);
                if (6 == $this->menu) {
                ?>
                    <li style="margin-right: 10px;margin-top: 1.5%;">


                        <?php if (!empty($this->page_name) && !empty($this->menu_name) && ($this->menu_name == "report")) { ?>
                            <button style="background-color:#E7E7E7 !important;" class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                <?php echo "Reports"; ?> <span class="caret"></span>
                            </button>
                        <?php } else { ?>
                            <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                <?php echo "Reports"; ?> <span class="caret"></span>
                            </button>
                        <?php } ?>

                        <ul class="dropdown-menu">
                            <?php $this->role = checkUserPermission(6, 20, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Employee Report')) echo 'class="active"'; ?>><a href="report/employeeReport">Employee Report </a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(6, 21, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Leave Report')) echo 'class="active"'; ?>><a href="report/leaveReport">Leave Report </a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(6, 22, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Attendance Report')) echo 'class="active"'; ?>><a href="report/attendanceReport">Attendance Report </a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(6, 23, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Salary Report')) echo 'class="active"'; ?>><a href="report/salaryReport">Salary Report </a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(6, 41, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Others Report')) echo 'class="active"'; ?>><a href="report/othersReport">Others Report </a></li>
                            <?php } ?>

                        </ul>
                    </li>
                <?php } ?>


                <?php $this->menu = checkMenuPermission(7, $userData);
                if (7 == $this->menu) {
                ?>
                    <li style="margin-right: 10px;margin-top: 1.5%;">
                        <?php if (!empty($this->page_name) && !empty($this->menu_name) && ($this->menu_name == "setting")) { ?>
                            <button style="background-color:#E7E7E7 !important;" class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                <?php echo "Settings"; ?> <span class="caret"></span>
                            </button>
                        <?php } else { ?>
                            <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                <?php echo "Settings"; ?> <span class="caret"></span>
                            </button>
                        <?php } ?>
                        <ul class="dropdown-menu">
                            <?php $this->role = checkUserPermission(7, 24, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'company')) echo 'class="active"'; ?>><a href="dashboard/crud/company">Company</a></li>
                            <?php } ?>

                            <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'festival')) echo 'class="active"'; ?>><a href="<?php echo site_url('dashboard/crud/festival'); ?>">Festival Disbursement </a></li>

                            <?php $this->role = checkUserPermission(7, 25, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Main Department')) echo 'class="active"'; ?>><a href="dashboard/crud/mainDepartment">Main Department</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 25, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'department')) echo 'class="active"'; ?>><a href="dashboard/crud/department">Department</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 26, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'section')) echo 'class="active"'; ?>><a href="dashboard/crud/section">Section</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 27, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'subsection')) echo 'class="active"'; ?>><a href="dashboard/crud/subsection">Sub Section</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 28, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'designation')) echo 'class="active"'; ?>><a href="dashboard/crud/designation">Designation</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 29, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'dayOfWeek')) echo 'class="active"'; ?>><a href="dashboard/crud/dayOfWeek">Day Of Week</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 30, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'salaryGrade')) echo 'class="active"'; ?>><a href="dashboard/crud/salaryGrade">Salary Grade</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 31, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'shift')) echo 'class="active"'; ?>><a href="dashboard/crud/shift">Shift</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 32, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'shiftSetting')) echo 'class="active"'; ?>><a href="setting/shiftSetting">Shift Settings</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 33, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'setting')) echo 'class="active"'; ?>><a href="setting/setting">Settings</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 34, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'documents')) echo 'class="active"'; ?>><a href="setting/documents">Legal Documents</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 35, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'asset')) echo 'class="active"'; ?>><a href="crud/assetsManagement">Assets Management</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 36, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'benefitDeductionSetting')) echo 'class="active"'; ?>><a href="setting/benefitDeductionSetting">Benefit & Deduction Field Setup</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 37, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'import')) echo 'class="active"'; ?>><a href="setting/import">Import's Data</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 38, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'materials')) echo 'class="active"'; ?>><a href="dashboard/crud/materialSetting">Materials Settings</a></li>
                            <?php } ?>

                            <?php $this->role = checkUserPermission(7, 38, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'bank')) echo 'class="active"'; ?>><a href="setting/bank">Bank</a></li>
                            <?php } ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'sla')) echo 'class="active"'; ?>><a href="setting/sla">SLA Activation</a></li>
                            <?php $this->role = checkUserPermission(7, 38, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'holidayCalederForAll')) echo 'class="active"'; ?>><a href="dashboard/holidayCalederForAll">Holiday Calender For All Group</a></li>
                            <?php } ?>
                            <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'cclist')) echo 'class="active"'; ?>><a href="<?php echo site_url(); ?>setting/cclist">CC List</a></li>
                            <!-- <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'schedule')) echo 'class="active"'; ?>><a href="<?php echo site_url(); ?>crud/timeSchedule">Duty Time Schedule</a></li> -->
                            <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'schedule')) echo 'class="active"'; ?>><a href="<?php echo site_url(); ?>employee/time_schedule">Time Schedule</a></li>
                        </ul>
                    </li>
                <?php } ?>


                <?php $this->menu = checkMenuPermission(8, $userData);
                if (8 == $this->menu) {
                ?>
                    <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'notice')) echo 'class="active"'; ?> style="margin-top:0.5%"><a href="dashboard/crud/notice">Notice</a></li>
                <?php } ?>

                <?php $this->menu = checkMenuPermission(10, $userData);
                if (10 == $this->menu) {
                ?>
                    <li style="margin-right: 10px;margin-top: 1.5%;">


                        <?php if (!empty($this->page_name) && !empty($this->menu_name) && ($this->menu_name == "materials")) { ?>
                            <button style="background-color:#E7E7E7 !important;" class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                <?php echo "Materials"; ?> <span class="caret"></span>
                            </button>
                        <?php } else { ?>
                            <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                <?php echo "Materials"; ?> <span class="caret"></span>
                            </button>
                        <?php } ?>

                        <ul class="dropdown-menu">
                            <?php $this->role = checkUserPermission(10, 42, $userData);
                            if (empty($this->role) || !in_array(11, $this->role)) {
                            ?>
                                <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'Purchase Requisition')) echo 'class="active"'; ?>><a href="<?php echo site_url('dashboard/materials'); ?>">Materials Register </a></li>
                            <?php } ?>


                        </ul>
                    </li>
                <?php } ?>


                <?php if ($this->session->userdata('logged_in')) { ?>
                    <?php $this->session->set_userdata('current_url', "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>

                    <?php $this->menu = checkMenuPermission(9, $userData);
                    if (9 == $this->menu) {
                    ?>
                        <li style="margin-right: 10px;margin-top: 0.5%;">
                            <div class="btn-group pull-right" style="margin-left: 10px;margin-top: 12%;">

                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                    <?php echo $this->session->userdata('username') ?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php $this->role = checkUserPermission(9, 39, $userData);
                                    if (empty($this->role) || !in_array(11, $this->role)) {
                                    ?>
                                        <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'profile')) echo 'class="active"'; ?>><a href="dashboard/profile"><?php echo 'Profile'; ?></a></li>
                                    <?php } ?>

                                    <?php $this->role = checkUserPermission(9, 40, $userData);
                                    if (empty($this->role) || !in_array(11, $this->role)) {
                                    ?>
                                        <li <?php if ((!empty($this->page_name)) && ($this->page_name == 'acl')) echo 'class="active"'; ?>><a href="dashboard/acl"><?php echo 'ACL/Role'; ?></a></li>
                                    <?php } ?>


                                    <li><a href="dashboard/logout"><?php echo 'Logout'; ?></a></li>

                                </ul>
                            </div>
                        </li>
                    <?php } ?>

                    <!-- Messages: style can be found in dropdown.less-->
                    <li id="notice" style="margin-left:-8px;margin-right: 2px;margin-top:-.4%" class="dropdown messages-menu">

                    </li>
                    <!-- Notifications: style can be found in dropdown.less -->
                    <?php
                    $user_id = $this->session->userdata('user_id');
                    $user = $this->m_common->get_row_array('v_user', array('user_id' => $user_id), '*');
                    $assign_companies = unserialize($user[0]['assign_company']);
                    $companyName = $this->session->userdata('companyName');
                    if (!empty($companyName)) {
                    ?>
                        <li style="margin-right: 10px;margin-top: 0.5%;width:15%;" class="pull-right">
                            <select class="form-control" id="allCompany" onchange="changeCompany()" style="margin-top: 10px;height:35px;">
                                <option class="form-control" value="0">All</option>

                                <?php foreach ($this->companies as $com) { ?>
                                    <?php if (in_array($com['id'], $assign_companies)) { ?>
                                        <option class="form-control" value="<?php echo $com['id']; ?>" <?php if ($com['id'] == $companyId) echo 'selected="selected"'; ?>><?php echo $com['name']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                            <!--        <h3><?php echo $companyName; ?></h3></li>-->
                        <?php } ?>
            </ul>
        <?php } ?>
        </div><!-- /.navbar-collapse -->

    </nav>


<?php } ?>
<script>
    var site_url = '<?php echo site_url(); ?>'
</script>
<?php
$msg = $this->session->flashdata("msg");
if (!empty($msg)) {
?>
    <script type="text/javascript">
        msg = '<?php echo $msg; ?>';
        $('.flash_message').html(msg);
        $('.flash_message').show();
        setTimeout(function() {
            $('.flash_message').hide();
        }, 5000);
    </script>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function() {

        notification();

    });

    $(function() {

        $('.time').datetimepicker();
        $('.datepicker').datetimepicker({
            format: 'DD-MM-YYYY'
        });
        $('.monthpicker').datetimepicker({
            format: 'YYYY-MM'
        });
        $('.timeByDate').datetimepicker({
            format: 'DD-MM-YYYY hh:mm a'
        });
        $('.datetimepicker3').datetimepicker({
            format: 'LT'
        });
    });

    function notification() {

        $.ajax({
            type: "POST",
            url: "<?php echo site_url(); ?>dashboard/crud/notification",
            dataType: "json",
            success: function(data) {
                // alert(data.notices.length);

                var tag = "<a href='#' class='dropdown-toggle' data-toggle='dropdown' style='padding:15px 2px !important;float:left;display:block'>" + "<img src='images/images.png' alt='alert' />";

                if (data.notices.length) {

                    tag += "<span class='label label-danger'>" + "<lable class='alert-image'>" + data.notices.length + "</lable>" + "</span>";
                }

                tag += "</a>";
                if (data.notices.length) {

                    var count = data.notices.length;
                    tag += "<ul class='dropdown-menu'>" + "<li class='header'>" + "You have" + " " + count + " " + "notification" + "</li>" + "<li>" + "<ul class='menu'>";

                    $.each(data.notices, function(i, v) {
                        var str = data.notices[i]['title'];
                        var url = v.url;
                        var title = str.substring(0, 16);
                        var rex = /(<([^>]+)>)/ig;
                        var str1 = v.notice;
                        var str2 = str1.replace(rex, " ");
                        var description = str2.substring(0, 30);
                        tag += "<li>" + "<a href=dashboard/notice_update/" + data.notices[i]['id'] + " >" + "<div class='pull-left'>" + "<img class='img-circle' src=images/idcard.jpg" + ">" + "</div>";
                        //tag += "<li>" + "<a href="+ url + ">" + "<div class='pull-left'>" + "<img class='img-circle' src=images/idcard.jpg" + ">" + "</div>";
                        tag += "<h4>" + title + "<small><i class='glyphicon glyphicon-time'></i> " + data.notices[i]['time'] + "</small>" + "</h4>" + "<p>" + description + "</p>" + "</a>" + "</li>";
                        //alert(description);                  
                    });
                    tag += "</ul>" + "</li>" + "<li class='footer'>" + "<a href=dashboard/crud/notice>" + "See all notifications" + "</a>" + "</li>" + "</ul>";
                }

                tag += "<a title='Special Notice' href='#' id='special_notice' onclick='javascript:special();'  style='padding:15px 0px !important;float:left;display:block;'>" + "<img src='images/images.png' alt='alert' />";

                if (data.special_notice.length) {

                    tag += "<span class='label label-danger'>" + "<lable class='alert-image'>" + data.special_notice.length + "</lable>" + "</span>";
                }

                tag += "</a>";

                if (data.special_notice.length) {

                    var count = data.special_notice.length;
                    tag += "<ul class='dropdown-menu' id='special_notice_details' style='display:none'> " + "<li class='header'>" + "You have" + " " + count + " " + "special notification" + "</li>" + "<li>" + "<ul class='menu'>";

                    $.each(data.special_notice, function(j, w) {

                        var str = data.special_notice[j]['title'];
                        var url = w.url;
                        var title = str.substring(0, 16);
                        var rex = /(<([^>]+)>)/ig;
                        var str1 = w.notice;
                        var str2 = str1.replace(rex, " ");
                        var description = str2.substring(0, 30);
                        tag += "<li>" + "<a href=dashboard/notice_update/" + data.special_notice[j]['id'] + " >" + "<div class='pull-left'>" + "<img class='img-circle' src=images/idcard.jpg" + ">" + "</div>";
                        // tag += "<li>" + "<a href="+ url + ">" + "<div class='pull-left'>" + "<img class='img-circle' src=images/idcard.jpg" + ">" + "</div>";
                        tag += "<h4>" + title + "<small><i class='glyphicon glyphicon-time'></i> " + data.special_notice[j]['time'] + "</small>" + "</h4>" + "<p>" + description + "</p>" + "</a>" + "</li>";
                        //alert(description);                  
                    });
                    tag += "</ul>" + "</li>" + "<li class='footer'>" + "<a href=dashboard/crud/notice>" + "See all notifications" + "</a>" + "</li>" + "</ul>";
                }
                //alert(tag);
                $('#notice').html(tag);
            }
        });
        setTimeout(notification, 12000);
    }

    //$('#special_notice').click(function(){
    //   $('#special_notice_details').show();
    //});
    function special() {
        $('#special_notice_details').toggle();
    }
</script>