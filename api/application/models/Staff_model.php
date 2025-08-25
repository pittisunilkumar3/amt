<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staff_model extends CI_Model
{

    public function getAll($id = null, $is_active = null)
    {
        $this->db->select("staff.*,staff_designation.designation,department.department_name as department, roles.id as role_id, roles.name as role");
        $this->db->from('staff');
        $this->db->join('staff_designation', "staff_designation.id = staff.designation", "left");
        $this->db->join('staff_roles', "staff_roles.staff_id = staff.id", "left");
        $this->db->join('roles', "roles.id = staff_roles.role_id", "left");
        $this->db->join('department', "department.id = staff.department", "left");

        if ($id != null) {
            $this->db->where('staff.id', $id);
        } else {
            if ($is_active != null) {
                $this->db->where('staff.is_active', $is_active);
            }
            $this->db->order_by('staff.id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    /**
     * Get detailed staff profile by ID
     */
    public function getProfile($id)
    {
        $this->db->select('staff.*,staff_designation.designation as designation,staff_roles.role_id, department.department_name as department,roles.name as user_type');
        $this->db->join("staff_designation", "staff_designation.id = staff.designation", "left");
        $this->db->join("department", "department.id = staff.department", "left");
        $this->db->join("staff_roles", "staff_roles.staff_id = staff.id", "left");
        $this->db->join("roles", "staff_roles.role_id = roles.id", "left");
        $this->db->where("staff.id", $id);
        $this->db->from('staff');
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Search staff by name, email, or employee ID
     */
    public function searchStaff($search_term, $role_id = null, $is_active = 1, $limit = 20, $offset = 0)
    {
        $this->db->select("staff.*,staff_designation.designation,department.department_name as department, roles.id as role_id, roles.name as role");
        $this->db->from('staff');
        $this->db->join('staff_designation', "staff_designation.id = staff.designation", "left");
        $this->db->join('staff_roles', "staff_roles.staff_id = staff.id", "left");
        $this->db->join('roles', "roles.id = staff_roles.role_id", "left");
        $this->db->join('department', "department.id = staff.department", "left");

        if (!empty($search_term)) {
            $this->db->group_start();
            $this->db->like('staff.name', $search_term);
            $this->db->or_like('staff.surname', $search_term);
            $this->db->or_like('staff.email', $search_term);
            $this->db->or_like('staff.employee_id', $search_term);
            $this->db->or_like('CONCAT(staff.name, " ", staff.surname)', $search_term);
            $this->db->group_end();
        }

        if ($role_id != null) {
            $this->db->where('roles.id', $role_id);
        }

        if ($is_active != null) {
            $this->db->where('staff.is_active', $is_active);
        }

        $this->db->order_by('staff.name', 'ASC');
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get staff by role ID
     */
    public function getStaffByRole($role_id, $is_active = 1)
    {
        $this->db->select("staff.*,staff_designation.designation,department.department_name as department, roles.id as role_id, roles.name as role");
        $this->db->from('staff');
        $this->db->join('staff_designation', "staff_designation.id = staff.designation", "left");
        $this->db->join('staff_roles', "staff_roles.staff_id = staff.id", "left");
        $this->db->join('roles', "roles.id = staff_roles.role_id", "left");
        $this->db->join('department', "department.id = staff.department", "left");

        $this->db->where('roles.id', $role_id);
        if ($is_active != null) {
            $this->db->where('staff.is_active', $is_active);
        }

        $this->db->order_by('staff.name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get staff by employee ID
     */
    public function getByEmployeeId($employee_id)
    {
        $this->db->select("staff.*,staff_designation.designation,department.department_name as department, roles.id as role_id, roles.name as role");
        $this->db->from('staff');
        $this->db->join('staff_designation', "staff_designation.id = staff.designation", "left");
        $this->db->join('staff_roles', "staff_roles.staff_id = staff.id", "left");
        $this->db->join('roles', "roles.id = staff_roles.role_id", "left");
        $this->db->join('department', "department.id = staff.department", "left");

        $this->db->where('staff.employee_id', $employee_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Get staff count for pagination
     */
    public function getStaffCount($search_term = null, $role_id = null, $is_active = 1)
    {
        $this->db->from('staff');
        $this->db->join('staff_roles', "staff_roles.staff_id = staff.id", "left");
        $this->db->join('roles', "roles.id = staff_roles.role_id", "left");

        if (!empty($search_term)) {
            $this->db->group_start();
            $this->db->like('staff.name', $search_term);
            $this->db->or_like('staff.surname', $search_term);
            $this->db->or_like('staff.email', $search_term);
            $this->db->or_like('staff.employee_id', $search_term);
            $this->db->or_like('CONCAT(staff.name, " ", staff.surname)', $search_term);
            $this->db->group_end();
        }

        if ($role_id != null) {
            $this->db->where('roles.id', $role_id);
        }

        if ($is_active != null) {
            $this->db->where('staff.is_active', $is_active);
        }

        return $this->db->count_all_results();
    }

}
