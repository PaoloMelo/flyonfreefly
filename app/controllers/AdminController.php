<?php

class AdminController extends Controller
{
    public function __construct()
    {
        $this->addVariable('path', _PATH_);
        $login = $this->checkLogin(false);
        if ($login) {
            $this->addVariable('login_user_id', $login->data['user_id']);
            $this->addVariable('login_name', $login->data['name']);
            $this->addVariable('login_email', $login->data['email']);
        }
    }

    private function checkLogin($redirect = true)
    {
        $LoginHelper = new LoginHelper();
        $has_login = $LoginHelper->checkLogin();
        if (!$has_login) {
            if ($redirect) return $this->goToAction('login');
            else return false;
        }

        $login_data = $LoginHelper->getData();

        $acl = new Acl();

        $acl->allow('master');
        $acl->allow('admin');
        $acl->deny('admin', 'master-data');

        $acl->addUserRole($login_data['roles']);

        $login = new StdClass();
        $login->data = $login_data;
        $login->acl = $acl;

        return $login;
    }

	public function indexAction()
	{
        $login = $this->checkLogin();

        $this->view([
            'admin/template/top',
            'admin/template/header',
            'admin/template/menu',
            'admin/pages/index',
            'admin/template/footer',
            // 'admin/template/sidebar',
            'admin/template/bottom',
        ]);
	}

	public function loginAction()
	{
        $this->view('admin/login');
	}

	public function loginPostAction()
	{
        $ret = [
            'error' => false,
            'messages' => [],
        ];

        $login = trim($this->request->post('login'));
        $password = trim($this->request->post('password'));

        if ($login == '') {
            $ret['error'] = true;
            $ret['messages'][] = [
                'type' => 'field_login',
                'message' => 'Campo "login" é obrigatório.',
            ];
        }

        if ($password == '') {
            $ret['error'] = true;
            $ret['messages'][] = [
                'type' => 'field_password',
                'message' => 'Campo "senha" é obrigatório.',
            ];
        }

        if (!$ret['error'] ) {
            $login_data = [
                'user_id' => 1,
                'name' => 'Leandro Macedo',
                'email' => 'fmlimao@gmail.com',
                'roles' => ['admin'],
            ];

            $LoginHelper = new LoginHelper();
            $LoginHelper->login($login_data);

            $ret['messages'][] = [
                'type' => 'success',
                'message' => 'Login efetuado com sucesso.',
            ];
        }

        echo json_encode($ret);
	}

    public function logoutAction()
    {
        $LoginHelper = new LoginHelper();
        $LoginHelper->logout();
        $this->goToAction('');
    }
}
