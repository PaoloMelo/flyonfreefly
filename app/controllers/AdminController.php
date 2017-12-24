<?php

class AdminController extends Controller
{
    private function checkLogin()
    {
        $LoginHelper = new LoginHelper();
        $has_login = $LoginHelper->checkLogin();
        if (!$has_login) return $this->goToAction('login');

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

        printa('admin ok');
        printa('products::view : ' . ($login->acl->isAllowed('products', 'view') ? 1 : 0));
        printa('products::list : ' . ($login->acl->isAllowed('products', 'list') ? 1 : 0));
        printa('master-data::list : ' . ($login->acl->isAllowed('master-data', 'list') ? 1 : 0));
        echo '<a href="' . _PATH_ . 'admin/logout' . '">logout</a>';
	}

	public function loginAction()
	{
        $this->view('admin/login', [
            'path' => _PATH_,
        ]);
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
