


$this->db->where('id', 7)->delete('users');

$this->c->load('service/crud as db', $this->c->load('service/provider/db'));