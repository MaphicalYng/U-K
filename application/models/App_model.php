<?php
/**
 * Created by PhpStorm.
 * User: Shuolin Yang
 * Date: 17/4/30
 * Time: 下午 10:14
 */
class App_model extends CI_Model
{




    /*
     * 初始化模型并连接数据库。
     * */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
	    $this->load->helper('array');
    }




    /*
     * 查询用户是否存在的函数。
     * */
    private function _if_user_exist_password($id = NULL)
    {
        if ($id === NULL)
        {
            return 'no-data';
        }


        $query = $this->db->query('SELECT * FROM user WHERE user.user="'.$id.'"')->result();
        if ($query[0]->user === $id)
        {
            $result['exist'] = TRUE;
            $result['password'] = $query[0]->password;
            return $result;
        }
        else
        {
            $result['exist'] = FALSE;
            $result['password'] = NULL;
            return $result;
        }
    }




    /*
     * 创建用户，为新用户创建一个条目。
     * */
    public function new_id($id_info)
    {


        /*
         * 检查是否已有相同用户名存在。
         * */
        $same = $this->db->query('SELECT user FROM user')->result();
        foreach ($same as $item)
        {
            if ($item->user === $id_info['id'])
            {
                return 'already';
            }
        }


        /*
         * 将密码（加密后）和邮箱存储到数据库中。
         * */
        $this->db->insert('user', array(
            'user' => $id_info['id'],
            'password' => password_hash($id_info['password'], PASSWORD_DEFAULT),
            'email' => $id_info['email']
        ));
    }




    /*
     * 登录功能，遍历数据库寻找用户名并返回存在情况，貌似效率太低？
     * */
    public function log_in($id_info)
    {


        /*
         * 检查是否有相同用户名存在。
         * */
        $same = $this->db->query('SELECT user FROM user')->result();
        foreach ($same as $item)
        {
            if ($item->user === $id_info['id'])  // 用户名存在。
            {
                $result['exist'] = TRUE;
                $pa = $this->db->query('SELECT password FROM user WHERE user.user="'.$id_info['id'].'"')->result();
                $result['password'] = $pa['0']->password;
                return $result;  // 返回存在情况和密码。
            }
        }
        $result['exist'] = FALSE;
        $result['password'] = NULL;
        return $result;
    }




    /*
     * 更改密码功能。
     * */
    public function change_password($id_info = array())
    {


        /*
         * 检查用户是否存在。
         * */
        $query = $this->_if_user_exist_password($id_info['id']);
        if ($query['exist'])
        {


            /*
             * 存在，验证密码。
             * */
            if (password_verify($id_info['old_password'], $query['password']))
            {


                /*
                 * 正确，修改密码。
                 * */
                $new_password_encrypted = password_hash($id_info['new_password'], PASSWORD_DEFAULT);
                $this->db->query('UPDATE user SET password="'.$new_password_encrypted.'" WHERE user="'.$id_info['id'].'"');
                $result['result'] = TRUE;
                $result['error'] = NULL;
                return $result;
            }
            else
            {


                /*
                 * 密码错误。
                 * */
                $result['result'] = FALSE;
                $result['error'] = 'wrong-password';
                return $result;
            }
        }
        else
        {


            /*
             * 用户不存在。
             * */
            $result['result'] = FALSE;
            $result['error'] = 'no-id';
            return $result;
        }
    }




    /*
     * 登录后功能的通用模型（插入、提取和删除数据）。
     * */
    public function universal($data_info = FALSE)  // 参数包括：type, id, item, content 。
    {                                               // 按需返回。


        /*
         * 需要数组辅助函数。
         * */



        /*
         * 检查参数防止不完整参数对数据库的影响。
         * */
        if ($data_info === FALSE)
        {
            log_message('error', 'No data get. (FROM App_model/universal);');
            show_error('缺少数据。（App_model/universal）。', 500);
        }
        if ((!element('type', $data_info, FALSE))
            OR (!element('id', $data_info, FALSE)))
        {
            log_message('error', 'Data format error. (FROM App_model/universal);');
            show_error('数据格式错误（App_model/universal）。', 500);
        }



        /*
         * 插入操作。
         * */
        if ($data_info['type'] === 'insert')
        {
            $data = array(
                'user' => $data_info['id'],  // 插入数据时将用户名插入。
                'item' => $data_info['item'],
                'content' => $data_info['content']
            );
            $this->db->insert('content', $data);
        }


        /*
         * 提取操作。
         * */
        if ($data_info['type'] === 'query')
        {
            $query = $this->db->query('SELECT item, content FROM content WHERE content.user="'.$data_info['id'].'"')->result();
            return $query;
        }


        /*
         * 删除操作。
         * */
        if ($data_info['type'] === 'delete')
        {


            /*
             * 查询是否有此记录。
             * */
            $flag = FALSE;
            $same = $this->db->query('SELECT item FROM content WHERE content.user="'.$data_info['id'].'"')->result();
            foreach ($same as $item)
            {
                if ($item->item === $data_info['item'])  // 条目存在。
                {
                    $flag = TRUE;
                }
            }
            if (!$flag)
            {
                return 'not-found';
            }


            /*
             * 删除操作。
             * */
            $this->db->query('DELETE FROM content WHERE content.user="'.$data_info['id'].'" AND content.item="'.$data_info['item'].'"');
        }
    }
}
