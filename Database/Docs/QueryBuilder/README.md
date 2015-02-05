


$this->db->where('id', 7)->delete('users');

$this->c['service/crud as db', $this->c->load('service/provider/db')];