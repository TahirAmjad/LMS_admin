<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');



class m_user extends CI_Model
{
    
    private $table = "users";
    
    private $tableId = "userId";
    
    public function insert_record($recordData){
        
        $query = $this->db->insert($this->table, $recordData);
        if ($query) {
            $whereConditionArray = array(
                $this->tableId => $this->db->insert_id()
            );
            return true;
        } else
            return false;
    }
    public function get_record($whereConditionArray = null)
    {
        if ($whereConditionArray)
        $this->db->where($whereConditionArray);
        $query = $this->db->get($this->table);
        return $query->row();
        
    }
    public function get_records($whereConditionArray = null)
    {
        if ($whereConditionArray)
            $this->db->where($whereConditionArray);
        if ($this->user->roleId != 1)
        $this->db->where('userCreatedUserId', $this->user->userId);
        $query = $this->db->get($this->table);
        return $query->result();
    }
    public function get_records_with_login_join($whereConditionArray = null)
    {
        if ($whereConditionArray)
        $this->db->where($whereConditionArray);
        $this->db->join('login', 'login.login_userId = ' . $this->table . '.' . $this->tableId, 'left');
        $this->db->order_by('loginId', 'DESC');
        $query = $this->db->get($this->table);
        return $query->result();
        
    }
    public function update_record($whereConditionArray, $updateData)
    {
        $this->db->where($whereConditionArray);
        $query = $this->db->update($this->table, $updateData);
        if ($query) {
            return true;
        } else
            return false;
    }
    public function update_role($uid, $rid)
    {
        $slug = url_title($this->input->post('title'));
        $data = array(
            'typeId' => $rid
        );
        $this->db->where('userId', $uid);
        return $this->db->update($this->table, $data);
        
    }
    
    public function get_user_name($whereConditionArray = null)
    {
        
        if ($whereConditionArray)
            $this->db->select('first_name');
        
        $this->db->select('last_name');
        
        $this->db->where($whereConditionArray);
        
        $query = $this->db->get($this->table);
        
        return $query->row();
        
    }
    
    
    
    public function get_user_email($userId)
    {
        
        
        
        $this->db->select('email');
        
        $this->db->where('userId', $userId);
        
        $query = $this->db->get($this->table);
        
        return $query->row();
        
    }
    public function get_deal_owner_info($whereConditionArray = null)
    {
        
        if ($whereConditionArray)
            $this->db->select('firstName');
        
        $this->db->select('lastName');
        
        $this->db->select('phone');
        
        $this->db->select('email');
        
        $this->db->where($whereConditionArray);
        
        $query = $this->db->get($this->table);
        
        return $query->row();
        
    }
    
    
    function del_user($whereConditionArray)
    {
        $this->db->where($whereConditionArray);
        $query = $this->db->delete($this->table);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function upload_user_image($user_id) {
       
        if (isset($_FILES['user_image']) && $_FILES['user_image']['name'] != "") {
            move_uploaded_file($_FILES['user_image']['tmp_name'], 'uploads/user_image/'.$user_id.'.jpg');
            return true;
        }else{
            return false;
        }
    }
    
    
}