<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class m_section extends CI_Model
{
    private $table = "section";
    private $tableId = "sectionId";
    
    public function insert_record($recordData)
    {
        $query = $this->db->insert($this->table, $recordData);
        if ($query) {
            $insertId = $this->db->insert_id();
            return $insertId;
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

    public function get_records($whereConditionArray = null, $orderBy = null, $limit = 0, $groupByArray = null)
    {
        if ($whereConditionArray)
            $this->db->where($whereConditionArray);
        if($orderBy)
            $this->db->order_by($this->tableId, $orderBy);
        if($limit) {
            $this->db->limit($limit);
        }
        if($groupByArray){
            $this->db->group_by($groupByArray);
        }
        $query = $this->db->get($this->table);
//        print_result($this->db->last_query());
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
}